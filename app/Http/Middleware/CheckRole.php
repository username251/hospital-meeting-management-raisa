<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  // Parameter untuk role yang diharapkan
     */
    public function handle(Request $request, Closure $next, string $role, ): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect('/login');
        }

        $user = Auth::user();

        if ($user->role === $role) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses untuk role ini.');
    }
}