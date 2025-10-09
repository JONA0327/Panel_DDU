<?php

namespace App\Http\Middleware;

use App\Models\UserPanelMiembro;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDduMember
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Verificar si el usuario es miembro de DDU
        if (!UserPanelMiembro::isDduMember($user->id)) {
            Auth::logout();

            return redirect()->route('login')
                ->withErrors(['ddu_access' => 'Lo sentimos, no perteneces a DDU y tu acceso está restringido a este panel.'])
                ->withInput($request->except('password'));
        }

        return $next($request);
    }
}
