<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
|--------------------------------------------------------------------------
| Laravel Application Bootstrap
|--------------------------------------------------------------------------
|
| Just the basic Laravel app setup. I'm configuring this as an API for my
| recipe app, so I need CORS handling for the Angular frontend and some
| basic routing. Nothing too fancy here.
|
*/

return Application::configure(basePath: dirname(__DIR__))
    
    /*
    |--------------------------------------------------------------------------
    | Application Routing Configuration
    |--------------------------------------------------------------------------
    |
    | Setting up the routes I need:
    | - Web routes: barely using these since it's mainly an API
    | - API routes: all my recipe and nutrition endpoints
    | - Console routes: for artisan commands
    | - Health check: just a simple '/up' endpoint
    |
    */
    ->withRouting(
        web: __DIR__.'/../routes/web.php',        // Barely using these
        api: __DIR__.'/../routes/api.php',        // Where all the action happens
        commands: __DIR__.'/../routes/console.php', // Artisan stuff
        health: '/up',                            // Simple health check
    )
    
    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | I'm adding CORS middleware at the beginning because my Angular frontend
    | runs on a different port and I need to handle those cross-origin requests
    | properly. Had some headaches with this before!
    |
    */
    ->withMiddleware(function (Middleware $middleware): void {
        // Putting CORS first so it handles preflight requests properly
        // Had to debug this for a while when the frontend couldn't talk to the API
        $middleware->api(prepend: [
            \App\Http\Middleware\Cors::class,
        ]);
    })
    
    /*
    |--------------------------------------------------------------------------
    | Exception Handling Configuration
    |--------------------------------------------------------------------------
    |
    | Just using Laravel's default error handling for now. It works fine for
    | what I need. Maybe I'll customize this later if I need special API
    | error responses.
    |
    */
    ->withExceptions(function (Exceptions $exceptions): void {
        // Default handling is good enough for now
        // TODO: might add custom JSON error responses later
    })->create();
