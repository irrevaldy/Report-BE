<?php

namespace App\Http\Controllers;

/*use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;*/

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Session;
// use App\User;
use DB;

class LoginController extends Controller
{
   	public function __construct(){

    }

    public function login(Request $request)
    {
    	try
      {
        $username = $request->input('username');
  			$old_password = $request->input('old_password');
  			$new_password = $request->input('new_password');

        // $exist = User::where('user_name', $username)
  			// 				->where('password', $password)
  			// 				->where('user_active', '1')
  			// 				->first();

  			$check_exist_user = DB::select("[spVDWH_CheckUserLogin] '$username', '$old_password', '$new_password'");

  			if(count($check_exist_user) == 0)
        {
  				$result['success'] = false;
  				$result['message'] = 'Username or password is incorrect !';
  			}
        else
        {
  				$user_id 				= $check_exist_user[0]->user_id;
  				$name 				= $check_exist_user[0]->name;
  				$user_subgroup_id 	= $check_exist_user[0]->user_subgroup_id;
  				$flag_old_password 	= $check_exist_user[0]->flag_old_password;
          $group_fullname 	= $check_exist_user[0]->group_fullname;

  				//$api_token = sha1(time());
  				$api_token = Hash::make( $username.date('YmdHis').explode('.',microtime(true))[1] );
  				$result['success'] = true;
  				$result['message'] = 'Login success, '.$name.' !';
  				$result['data'] = array(
  									"user_id" => $user_id,
  									"name" => $name,
  									"api_token" => $api_token,
  									"user_subgroup_id" => $user_subgroup_id,
  									"flag_old_password" => $flag_old_password,
                    "group_fullname" => $group_fullname
  									);

  				// $update_token = User::where('user_name', $username)
  				// 					->where('password', $password)
  				// 					->update(['token_login' => $api_token]);
  				$update_token = DB::statement("[spVDWH_UpdateUserToken] '$username', '$api_token'");

  				Session::put('user_id', $user_id);
  				Session::put('username', $username);
  				Session::put('name', $name);
  				Session::put('api_token', $api_token);
  				Session::put('user_subgroup_id', $user_subgroup_id);

  				$activity[] = "[Login] ".$username;

  				$get_user_detail = DB::select("[spVDWH_GetUserDetail] '$username'");
  				if( $get_user_detail )
          {
  					$user_id 	= $get_user_detail[0]->user_id;
  					$name 		= $get_user_detail[0]->name;
  					$datetime	= date("jS F Y H:i:s");

            $dashboard = array();
            $count = 0;

  					foreach ($get_user_detail as $key => $value)
            {
  						if("DASH_ACQ_V" == $value->privilege_code)
              {
  							$dashboard[$count] = "dashacquirer";
                $count++;
  						}
  						else if("DASH_PROV_V" == $value->privilege_code)
              {
  							$dashboard[$count] = "dashprovider";
                $count++;
  						}
  						else if("DASH_CORP_V" == $value->privilege_code)
              {
  							$dashboard[$count] = "dashcorporate";
                $count++;
  						}
  						else if("DASH_MER_V" == $value->privilege_code)
              {
  							$dashboard[$count] = "dashmerchant";
                $count++;
  						}
  						else if("DASH_BRA_V" == $value->privilege_code)
              {
  							$dashboard[$count] = "dashbranch";
                $count++;
  						}
  						else if("DASH_STO_V" == $value->privilege_code)
              {
  							$dashboard[$count] = "dashstore";
                $count++;
  						}
  					}

  					$result['dashboard'] = $dashboard;


  				} else {
  					$res['success'] = false;
  					$res['message'] = 'Failed on get user detail !';
  					DB::rollback();
  					return response($res);
  				}

  				// foreach( $activity as $key => $value ) {
  				// 	$activity_text = $value;

  				// 	$insert_audit_trail = DB::statement("[spApiTMS_insertAuditTrail] '$user_id', '$username', '$name', '$datetime', '11', '$activity_text'");

  				// 	if(!$insert_audit_trail) {
  				// 		$res['success'] = false;
  				// 		$res['message'] = 'Failed on insert audit trail !';
  				// 		DB::rollback();
  				// 		return response($res);
  				// 	}
  				// }
  			}
  			return response($result);
  		}
      catch(Exception $e)
      {
  			$result['success'] = 'error';
  			$result['message'] = 'Login failed, please contact administrator !';
  		}
    }

	public function logout(Request $request) {

		$username = $request->input('username');

		$update_token = User::where('user_name', $username)
									->update(['token_login' => '']);

		if( $update_token ) {

			$activity[] = "[Logout] ".$username;

			$get_user_detail = DB::select("[spVDWH_GetUserDetail] '$username'");
			if( $get_user_detail ) {

				$user_id 	= $get_user_detail[0]->user_id;
				$name 		= $get_user_detail[0]->name;
				$datetime	= date("jS F Y H:i:s");

			} else {
				$res['success'] = false;
				$res['message'] = 'Failed on get user detail !';
				DB::rollback();
				return response($res);
			}

			// foreach( $activity as $key => $value ) {
			// 	$activity_text = $value;

			// 	$insert_audit_trail = DB::statement("[spApiTMS_insertAuditTrail] '$user_id', '$username', '$name', '$datetime', '12', '$activity_text'");

			// 	if(!$insert_audit_trail) {
			// 		$res['success'] = false;
			// 		$res['message'] = 'Failed on insert audit trail !';
			// 		DB::rollback();
			// 		return response($res);
			// 	}
			// }

			$res['success'] = true;

			return $res;
		}

	}

}
