<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCardsEnrolled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->cardsEnrollment) {
            return redirect()->route('cards.onboarding');
        }

        return $next($request);
    }
}
