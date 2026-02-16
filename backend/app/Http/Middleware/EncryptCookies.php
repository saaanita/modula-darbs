<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Http\Request;

class EncryptCookies
{
    protected $except = [];

    public function __construct(Encrypter $encrypter)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}
