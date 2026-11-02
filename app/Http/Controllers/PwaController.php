<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PwaController extends Controller
{
    /** One manifest per installable app, so each one installs as its own icon. */
    public function manifest(string $app): JsonResponse
    {
        $apps = [
            'portal' => ['name' => config('app.name'), 'short' => config('app.name'), 'start' => '/', 'scope' => '/', 'color' => '#1f3a5f'],
            'mdp' => ['name' => __('Método Devocional Proyectivo'), 'short' => __('Devocional'), 'start' => '/mdp', 'scope' => '/mdp/', 'color' => '#7a1f1f'],
            'cards' => ['name' => __('El arte de dar forma a tu cerebro'), 'short' => __('Fichas'), 'start' => '/fichas', 'scope' => '/fichas/', 'color' => '#2b5d8c'],
        ];
        $a = $apps[$app];

        return response()->json([
            'id' => $a['scope'],
            'name' => $a['name'],
            'short_name' => $a['short'],
            'start_url' => $a['start'],
            'scope' => $a['scope'],
            'display' => 'standalone',
            'background_color' => '#fafaf9',
            'theme_color' => $a['color'],
            'lang' => app()->getLocale(),
            'icons' => [
                ['src' => asset("images/icons/$app-192.png"), 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => asset("images/icons/$app-512.png"), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }
}
