<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMdpEnrolled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->mdpEnrollment) {
            return redirect()->route('mdp.onboarding');
        }

        return $next($request);
    }
}
