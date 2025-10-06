<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            // Dacă utilizatorul are un tenant, setează-l în sesiune
            if (auth()->user()->tenant_id) {
                session(['current_tenant_id' => auth()->user()->tenant_id]);
            }
            
            // Dacă utilizatorul este admin și nu are tenant setat,
            // permite accesul la toate rolurile (fără filtrare)
            if (!auth()->user()->tenant_id && auth()->user()->hasRole('admin')) {
                session()->forget('current_tenant_id');
            }
        }

        return $next($request);
    }
}
