<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Session;
use Exception;

class passwordController extends Controller
{
   	public function __construct(Request $request){
        
    }
	
	public function updatePasswordData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			$username     = $request->input('username');
            $old_password = $request->input('old_password');
            $new_password = $request->input('new_password');
			
			$check_old_password = DB::select("[spVDWH_UpdateUserPassword] '$username', '$old_password', '$new_password'");
            $msg_SQL = $check_old_password[0]->result;
            		
            if($msg_SQL == "SUCCESS") {
                
                $result['result'] = $msg_SQL;
                DB::commit();
                return response($result);
                
            } else {
                
                $result['result'] = $msg_SQL;
                DB::rollback();
                return response($result);
                
            }
			
			
		} catch(Exception $ex){ 
			$result[] = "ERROR";
			$result['result'] = 'Query Exception.. Please Check Database!';
			DB::rollback();
			return response($result);
		}
		
		return json_encode($result);
    }
}
