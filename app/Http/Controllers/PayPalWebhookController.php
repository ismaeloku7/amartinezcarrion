<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\PayPal\PayPalBilling;
use App\Services\PayPal\PayPalClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

/**
 * PayPal webhooks are the source of truth for access: subscriptions grant the plan's
 * products while ACTIVE and close them when they end; approved orders are captured
 * and recorded as purchases even if the user never came back to the site.
 */
class PayPalWebhookController extends Controller
{
    public function __construct(private PayPalClient $client, private PayPalBilling $billing) {}

    public function __invoke(Request $request): Response
    {
        abort_unless(PayPalClient::enabled() && filled(config('paypal.webhook_id')), 404);

        if (! $this->verify($request)) {
            Log::warning('PayPal webhook con firma no válida', ['event' => $request->input('event_type')]);

            return response('Invalid signature', 400);
        }

        $event = $request->all();
        $type = $event['event_type'] ?? '';
        $resource = $event['resource'] ?? [];

        try {
            match (true) {
                str_starts_with($type, 'BILLING.SUBSCRIPTION.') => $this->billing->syncSubscription($resource),
                $type === 'PAYMENT.SALE.COMPLETED' => $this->salePaid($resource),
                $type === 'CHECKOUT.ORDER.APPROVED' => $this->billing->captureOrder($resource['id']),
                $type === 'PAYMENT.CAPTURE.COMPLETED' => $this->capturePaid($resource),
                default => null,
            };
        } catch (\Throwable $e) {
            report($e);

            return response('Error', 500); // PayPal retries on non-2xx
        }

        return response('OK');
    }

    /** A renewal was charged: re-read the subscription so paid_through_at advances. */
    private function salePaid(array $sale): void
    {
        if ($id = $sale['billing_agreement_id'] ?? null) {
            $this->billing->refreshSubscription($id);
        }
    }

    /** A one-off capture completed (captured by us or from the PayPal side): make sure it is recorded. */
    private function capturePaid(array $capture): void
    {
        $orderId = Arr::get($capture, 'supplementary_data.related_ids.order_id');
        if (! $orderId || Purchase::query()->where('paypal_order_id', $orderId)->exists()) {
            return;
        }

        $this->billing->recordOrder($this->client->get("/v2/checkout/orders/{$orderId}"));
    }

    /** Ask PayPal to validate the transmission headers against our webhook id. */
    private function verify(Request $request): bool
    {
        $headers = [
            'auth_algo' => $request->header('PAYPAL-AUTH-ALGO'),
            'cert_url' => $request->header('PAYPAL-CERT-URL'),
            'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID'),
            'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG'),
            'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
        ];

        if (in_array(null, $headers, true) || in_array('', $headers, true)) {
            return false;
        }

        // The event must reach PayPal byte-for-byte as it was received, so the raw
        // body is spliced into a hand-built JSON document instead of being re-encoded.
        $fields = array_map(fn ($value) => json_encode($value), $headers + ['webhook_id' => config('paypal.webhook_id')]);
        $json = '{'.implode(',', array_map(fn ($k, $v) => "\"{$k}\":{$v}", array_keys($fields), $fields))
            .',"webhook_event":'.($request->getContent() ?: '{}').'}';

        try {
            $result = $this->client->postRaw('/v1/notifications/verify-webhook-signature', $json);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }

        return ($result['verification_status'] ?? null) === 'SUCCESS';
    }
}
