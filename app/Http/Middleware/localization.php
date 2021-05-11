<?php
namespace App\Http\Middleware;
use Closure;
class localization
{
  /**
  * Handle an incoming request.
  *
  * @param \Illuminate\Http\Request $request
  * @param \Closure $next
  * @return mixed
  */
  public function handle($request, Closure $next)
  {
      if(!$request->hasHeader('Language')){
          $response = [
            'success' => false,
            'code' => 403,
            'message' => 'Please input Language on header',
          ];
        return response()->json($response, 403);
      }
     // Check header request and determine localizaton
      $local = ($request->hasHeader('Language')) ? $request->header('Language') : 'en';
     // set laravel localization
      app()->setLocale($local);
    // continue request
    return $next($request);
  }
}