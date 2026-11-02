<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $active = Language::activeCodes();

        $locale = $request->query('lang')
            ?? $request->user()?->locale
            ?? $request->session()->get('locale')
            ?? $request->getPreferredLanguage($active)
            ?? config('app.locale');

        if (! in_array($locale, $active, true)) {
            $locale = $active[0] ?? config('app.locale');
        }

        if ($request->query('lang') && $request->hasSession()) {
            $request->session()->put('locale', $locale);
            if ($request->user() && $request->user()->locale !== $locale) {
                $request->user()->forceFill(['locale' => $locale])->save();
            }
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
