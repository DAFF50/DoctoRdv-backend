<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {

        // 1️⃣ Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        // 2️⃣ Récupérer l'utilisateur connecté
        $user = auth()->user();

        // 3️⃣ Vérifier si son rôle correspond
        if ($user->role !== $role) {
            return response()->json([
                'error' => 'Accès refusé. Rôle requis : ' . $role
            ], 403);
        }

        return $next($request);
    }
}
