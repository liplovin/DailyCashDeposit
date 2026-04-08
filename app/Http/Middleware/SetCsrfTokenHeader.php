<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCsrfTokenHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Send CSRF token in response headers to keep client in sync
        if ($token = csrf_token()) {
            $response->headers->set('X-CSRF-TOKEN', $token);
        }
        
        return $response;
    }
}
