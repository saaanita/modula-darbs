<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\HandleCors;

class CorsMiddleware extends HandleCors
{
    protected $except = [];
}
