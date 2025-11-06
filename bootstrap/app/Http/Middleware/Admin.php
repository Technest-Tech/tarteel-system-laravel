<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{

    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {

            if (auth()->user()->user_type == 'admin') {
                return $next($request);
            }else{
                return redirect()->back();
            }
        }else{
            return redirect()->route('login');
        }
    }
}
