<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Routes the user is allowed to visit before changing their password.
     */
    protected array $allowedRoutes = [
        'platform.profile',
        'platform.main',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (
            $user &&
            $user->must_change_password &&
            !in_array($request->route()?->getName(), $this->allowedRoutes)
        ) {
            return redirect()
                ->route('platform.profile')
                ->with('warning', 'Debes cambiar tu contraseña antes de continuar.');
        }

        return $next($request);
    }
}
