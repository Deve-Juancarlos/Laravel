<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            $user = Auth::user();
            if ($user->role === 'admin') {
                return redirect()->route('dashboard.admin');
            } elseif ($user->role === 'vendedor') {
                return redirect()->route('dashboard.vendedor');
            }
        }

        return $next($request);
    }
}