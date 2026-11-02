<?php

namespace App\Services\PayPal;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Thin wrapper over the PayPal REST API: OAuth token, base URL and JSON requests.
 */
class PayPalClient
{
    public static function enabled(): bool
    {
        return filled(config('paypal.client_id')) && filled(config('paypal.secret'));
    }

    public function baseUrl(): string
    {
        return config('paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function get(string $path): array
    {
        return $this->decode($this->request()->get($this->baseUrl().$path));
    }

    public function post(string $path, array $data = [], ?string $requestId = null): array
    {
        $request = $this->request();
        if ($requestId) {
            $request = $request->withHeaders(['PayPal-Request-Id' => $requestId]);
        }

        return $this->decode($request->post($this->baseUrl().$path, $data));
    }

    public function patch(string $path, array $data): array
    {
        return $this->decode($this->request()->patch($this->baseUrl().$path, $data));
    }

    /** POST with a hand-built body, for calls where the raw JSON must not be re-encoded. */
    public function postRaw(string $path, string $json): array
    {
        return $this->decode($this->request()->withBody($json, 'application/json')->post($this->baseUrl().$path));
    }

    public function accessToken(): string
    {
        $key = 'paypal.token.'.md5(config('paypal.mode').config('paypal.client_id'));

        return Cache::remember($key, 8 * 60 * 60, function () {
            $response = Http::asForm()
                ->withBasicAuth(config('paypal.client_id'), config('paypal.secret'))
                ->post($this->baseUrl().'/v1/oauth2/token', ['grant_type' => 'client_credentials']);

            if ($response->failed() || ! $response->json('access_token')) {
                throw new RuntimeException('PayPal: no se pudo obtener el token de acceso ('.$response->status().').');
            }

            // Cache::remember has no per-item TTL from inside the closure; the token
            // lives ~9 h, so the fixed 8 h above stays below it.
            return $response->json('access_token');
        });
    }

    private function request(): PendingRequest
    {
        return Http::withToken($this->accessToken())
            ->acceptJson()
            ->asJson()
            ->withHeaders(['Prefer' => 'return=representation']);
    }

    private function decode(Response $response): array
    {
        if ($response->failed()) {
            throw new PayPalException($response);
        }

        return $response->status() === 204 || $response->body() === '' ? [] : ($response->json() ?? []);
    }

    public static function requestId(string $prefix): string
    {
        return $prefix.'-'.Str::uuid();
    }
}
