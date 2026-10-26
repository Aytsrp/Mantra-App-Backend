<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWajibPajak
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->tokenCan('access') || ! $user->bolehMasukMobile()) {
            throw ApiException::unauthenticated();
        }

        $wp = $user->wajibPajak;
        if (! $wp || ! $wp->is_active) {
            throw ApiException::unauthenticated();
        }

        $request->attributes->set('wajib_pajak', $wp);

        return $next($request);
    }
}