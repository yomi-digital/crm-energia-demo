<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectLegacyDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Dominio legacy assegnato da DigitalOcean App Platform
        $legacyHost = 'easywork-crm-8vtob.ondigitalocean.app';

        if ($request->getHost() === $legacyHost) {
            $targetBase = 'https://cloud.gruppoalfacom.it';

            // Mantieni path e query string
            $uri = $request->getRequestUri(); // include path + query
            $targetUrl = $targetBase.$uri;

            return redirect()->away($targetUrl, 301);
        }

        return $next($request);
    }
}

