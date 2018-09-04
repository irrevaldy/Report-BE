<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Session;
use App\User;

class tokenController extends Controller
{
   	public function __construct(){
        
    }

    public function tokenCheck(Request $request) {
    
    	try {
			
			$posted_api_token = $request->api_token;
			$posted_name = $request->name;
			$posted_username = $request->username;
            
            $q_get_token = DB::select("spVDWH_CheckUserToken '$posted_username', '$posted_name', '$posted_api_token'");
            $data = $q_get_token[0]->counter;
			// $data = User::where('user_name', $posted_username)
			// 				->where('name', $posted_name)
			// 				->where('token_login', $posted_api_token)
			// 				->first();
							
			if(count($data) == 0) {
				$res['success'] = false;
				$res['message'] = 'Please re-login !';
			} else {
				$res['success'] = true;
				$res['message'] = 'Token valid !';
			}
			
		} catch(Exception $e) {
			
			$res['success'] = 'error';
			$res['message'] = 'Please contact administrator !';
			
		}
		
		return $res;
    
    }

    
}
