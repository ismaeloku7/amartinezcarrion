<?php

namespace App\Services\PayPal;

use Illuminate\Http\Client\Response;
use RuntimeException;

class PayPalException extends RuntimeException
{
    public function __construct(public readonly Response $response)
    {
        $body = $response->json() ?? [];
        $issue = $body['details'][0]['issue'] ?? $body['name'] ?? $body['error'] ?? 'error';

        parent::__construct('PayPal '.$response->status().': '.$issue);
    }

    public function issue(): ?string
    {
        return $this->response->json('details.0.issue') ?? $this->response->json('name');
    }
}
