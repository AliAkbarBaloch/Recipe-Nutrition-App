<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CORS (Cross-Origin Resource Sharing) Middleware
 * 
 * This middleware handles Cross-Origin Resource Sharing (CORS) for the API.
 * It allows the Angular frontend (running on different ports/domains) to make
 * HTTP requests to the Laravel backend by setting appropriate CORS headers.
 * 
 * CORS is necessary because modern browsers enforce the Same-Origin Policy,
 * which blocks requests from one domain to another unless explicitly allowed.
 * 
 * This middleware:
 * - Allows specific origins (Angular dev servers)
 * - Permits all standard HTTP methods
 * - Allows common headers used by modern web applications
 * - Handles preflight OPTIONS requests
 */
class Cors
{
    /**
     * Handle an incoming request and add CORS headers.
     * 
     * This method processes each HTTP request and adds the necessary CORS headers
     * to allow cross-origin requests from the Angular frontend. It also handles
     * preflight OPTIONS requests that browsers send before actual requests.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request
     * @param \Closure $next The next middleware in the pipeline
     * @return \Symfony\Component\HttpFoundation\Response The response with CORS headers
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Process the request through the next middleware/controller
        $response = $next($request);

        // Define allowed origins (Angular development servers)
        // These are the URLs where the Angular frontend is typically served during development
        $allowedOrigins = [
            'http://localhost:4200',  // Default Angular CLI development server
            'http://localhost:4201'   // Alternative Angular development server port
        ];
        
        // Get the Origin header from the request
        $origin = $request->headers->get('Origin');
        
        // Only allow requests from approved origins
        if (in_array($origin, $allowedOrigins)) {
            // Set the Access-Control-Allow-Origin header to the requesting origin
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        // Set allowed HTTP methods for cross-origin requests
        // This covers all standard CRUD operations plus OPTIONS for preflight
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        
        // Set allowed headers that the frontend can send
        // These are common headers used by modern web applications
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        
        // Allow credentials to be included in cross-origin requests
        // This enables cookies and authentication headers to be sent
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        // Handle preflight OPTIONS requests
        // Browsers send OPTIONS requests before actual requests to check permissions
        if ($request->getMethod() === 'OPTIONS') {
            // Return a successful response for preflight requests
            $response->setStatusCode(200);
        }

        // Return the response with CORS headers
        return $response;
    }
}
