<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    /**
     * Sanitizar todas las entradas de la petición entrante para mitigar XSS e inyecciones de scripts (OWASP A03:2021).
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                // 1. Eliminar etiquetas HTML completamente (Stored XSS mitigation)
                $cleaned = strip_tags($value);
                
                // 2. Codificar caracteres especiales en entidades HTML
                $value = htmlspecialchars($cleaned, ENT_QUOTES, 'UTF-8');
            }
        });
        
        $request->replace($input);
        
        return $next($request);
    }
}
