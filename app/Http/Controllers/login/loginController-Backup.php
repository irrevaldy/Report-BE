<?php

namespace App\Http\Controllers\login;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Session;
use Exception;

class loginController extends Controller
{
   	public function __construct(Request $request){

    }

    public function login(Request $request) {

      $username = $request->username;
  		$password = $request->password;

		try{
      date_default_timezone_set('Asia/Jakarta');
      $now = date("YmdHis");

			$data = DB::select("[spVDWH_login] '$username', '$password'");

			$data = json_encode($data);
			$data = json_decode($data, true);

			if( count($data) == 1 ){

				$api_token = sha1(date('YmdHis').$username.explode('.',microtime(true))[1]);

				//session(['username' => $username], ['token' => $api_token]);
				$set_token = DB::statement("[spVDWH_setAPIToken] '$username', '$api_token' ");

        $dateRange = '7';
        $merchant = $data[0]['merch_id'];
        $branch = $data[0]['branch_code'];

        $summary = DB::select("[spVDWH_MonitoringSum] '$dateRange', '$merchant', '$branch' ");

        $summary = json_encode($summary);
        $summary = json_decode($summary, true);

        $user_data['user_id'] = $data[0]['user_id'];
				$user_data['username'] = $data[0]['username'];
				$user_data['name'] = $data[0]['name'];
        $user_data['FID'] = $data[0]['FID'];
				$user_data['group_id'] = $data[0]['GROUPID'];
        $user_data['groupName'] = $data[0]['groupName'];
        $user_data['branch_code'] = $data[0]['branch_code'];
				$user_data['token'] =  $api_token;
        $user_data['merch_id'] = $data[0]['merch_id'];
        $merch_id = $data[0]['merch_id'];
        $FID = $data[0]['FID'];

        switch ($FID) {
          case '2':
            $imgName = "bank mandiri.png";
            break;
          case '3':
            $imgName = "bni.png";
            break;
          case '6':
            $imgName = "navbar_logo.png";
            break;
          case '7':
            $imgName = "bri-logo.png";
            break;
          case '99':
            $row_logo =  DB::select("[spVDWH_GetMerchantLogo] '$merch_id'");
            $row_logo = json_encode($row_logo);
            $row_logo = json_decode($row_logo, true);
            $imgName = $row_logo[0]['FMERCHLOGO'];

            if($imgName == 'Default Logo') {
              $imgName = 'logo.png';
            }
            break;
          default:
            $imgName = "logo.png";
            break;
        }
        $user_data['logo'] = $imgName;

        $merchant_call = DB::select("[spVDWH_GetMerchant] '$merch_id'");

        $merchant_call = json_encode($merchant_call);
        $merchant_call = json_decode($merchant_call, true);

        $FID_call = DB::select("[spVDWH_GetFID] '$FID'");

        $FID_call = json_encode($FID_call);
        $FID_call = json_decode($FID_call, true);

        if($user_data['FID'] == '1909')
        {
      		$user_data['FCODE'] = '1909';
      		$user_data['FNAME'] = "Wirecard";
      	}
        else if($user_data['FID'] == '99')
        {
      		$user_data['FCODE'] = 'merchant';
      		$user_data['FNAME'] = $merchant_call[0]['FMERCHNAME'];
      	}
        else if(isset($user_data['FID'])) {
      		$user_data['FCODE'] = $FID_call[0]['FCODE'];
      		$user_data['FNAME'] = $FID_call[0]['FNAME'];
      	}
        else
        {
      		$user_data['FCODE'] = 'pvs1909';
      		$user_data['FNAME'] = 'Wirecard';
      	}

        $user_data['total_edc'] = $summary[0]['Total EDC'];
        $user_data['total_active'] = $summary[0]['Total Active'];
        $user_data['total_not_active'] = $summary[0]['Total Not Active'];
        $user_data['total_active_transaction'] = $summary[0]['Total Active Transaction'];

        $audit_trail = DB::statement("[spVDWH_InsertAuditTrail] '2', '$user_data[user_id]', '$user_data[username]', '$user_data[name]', $now, 'Login idm laravel'");

				$res['success'] = true;
				$res['result'] = 'Login Success !';
				$res['data'] = $user_data;

			} else {
				$res['success'] = false;
				$res['result'] = 'Wrong username or password !';
			}

			return response($res);
		} catch(QueryException $ex){
			$res['success'] = 'error';
			$res['result'] = 'Query Exception.. Please Check Database!';

			return response($res);
		}

	}

	public function logout(Request $request, $username) {
    date_default_timezone_set('Asia/Jakarta');
    $now = date("YmdHis");

    $user_id = $request->user_id;
    $username = $request->username;
    $name = $request->name;

    $audit_trail = DB::statement("[spVDWH_InsertAuditTrail] '3', '$user_id' , '$username ', '$name', $now, 'Logout idm laravel'");

    session()->flush();
    $set_token = DB::statement(" [spVDWH_setAPIToken] '$username', '' ");

		$res['success'] = true;
		$res['result'] = 'Logout success';

		return $res;
	}

}
