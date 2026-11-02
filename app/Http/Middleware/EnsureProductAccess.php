<?php

namespace App\Http\Middleware;

use App\Services\AccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProductAccess
{
    public function __construct(private AccessService $access) {}

    public function handle(Request $request, Closure $next, string $productKey): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! $this->access->hasAccess($user, $productKey)) {
            return redirect()->route('paywall', ['product' => $productKey]);
        }

        return $next($request);
    }
}
