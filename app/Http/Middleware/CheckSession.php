<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use GuzzleHttp\Client;

class CheckSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //letakkan kode kamu disini...

        if( empty(Session::get('name')) || empty(Session::get('username')) || empty(Session::get('api_token')) ) {
            $request->session()->flush();

            return redirect('/');
        } else {
            $client = new \GuzzleHttp\Client();
            $form_post = $client->request('POST', config('constants.api_server').'token_check?api_token='.session()->get('api_token'), [
                'json' => [
                    'name' => Session::get('name'),
                    'username' => Session::get('username')
                ]
            ]);
            
            $var = json_decode($form_post->getBody()->getContents(), true);
            
            if($var['success'] == true) {
                return $next($request);
            } else {
                return redirect('/');
            }

            //return $next($request);
        }

        //return $next($request);
    }
}
