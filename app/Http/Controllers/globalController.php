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

class globalController extends Controller
{
   	public function __construct(Request $request){

    }

    public function getBranchData(Request $request)
    {
      $merch_id = $request->merch_id;

  		try
      {
  			$data = DB::select("[spVDWH_GetBranchData] '$merch_id' ");
  			$res['success'] = true;
  			$res['result'] = $data;

  			return response($res);
  		}
      catch(QueryException $ex)
      {
  			$res['success'] = false;
  			$res['result'] = 'Query Exception.. Please Check Database!';

  			return response($res);
  		}
	}

  public function getBranchData1(Request $request)
  {
    $username = $request->username;

    $data = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

  	$data = json_encode($data);
  	$data = json_decode($data, true);

    $merch_id = $data[0]['value'];

    try
    {
      $data = DB::select("[spVDWH_GetBranchData] '$merch_id' ");
      $res['success'] = true;
      $res['result'] = $data;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getMerchantData1(Request $request)
  {
    try
    {
      $data = DB::select("[spVDWH_GetMerchantWoParam]");
      $res['success'] = true;
      $res['result'] = $data;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getBankData(Request $request)
  {
    try
    {
      $data = DB::select("[spVDWH_GetBankData]");
      $res['success'] = true;
      $res['result'] = $data;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
}

  public function getHostData(Request $request)
    {
      $merch_id = $request->merch_id;

  		try
      {
  			$data = DB::select("[spVDWH_getHostData] '$merch_id'");
  			$res['success'] = true;
  			$res['result'] = $data;

  			return response($res);
  		}
      catch(QueryException $ex)
      {
  			$res['success'] = false;
  			$res['result'] = 'Query Exception.. Please Check Database!';

  			return response($res);
  		}
	}

  public function getHostData1(Request $request)
    {
      $username = $request->username;

      $data = DB::select("[spVMonitoringReport_GetUserInfo] '$username'");

    	$data = json_encode($data);
    	$data = json_decode($data, true);

      $merch_id = $data[0]['value'];

  		try
      {
  			$data = DB::select("[spVDWH_getHostData] '$merch_id'");
  			$res['success'] = true;
  			$res['result'] = $data;

  			return response($res);
  		}
      catch(QueryException $ex)
      {
  			$res['success'] = false;
  			$res['result'] = 'Query Exception.. Please Check Database!';

  			return response($res);
  		}
	}

	public function getCorporateData(Request $request) {

		try{
			$data = DB::select("[spVDWH_getCorporateData]");
			$res['success'] = true;
			$res['result'] = $data;

			return response($res);
		} catch(QueryException $ex){
			$res['success'] = false;
			$res['result'] = 'Query Exception.. Please Check Database!';

			return response($res);
		}

	}

  public function getMerchantData(Request $request) {

		try{
			$data = DB::select("[spVDWH_getMerchantData]");
			$res['success'] = true;
			$res['result'] = $data;

			return response($res);
		} catch(QueryException $ex){
			$res['success'] = false;
			$res['result'] = 'Query Exception.. Please Check Database!';

			return response($res);
		}

	}

  public function GetMerchantDataByCorpId(Request $request)
  {
    $id_corp = $request->corporate;
    try{
      $data = DB::select("[spVDWH_getMerchant] '$id_corp' ");
      $res['success'] = true;
      $res['result'] = $data;

      return response($res);
    } catch(QueryException $ex){
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }

  }



	public function getCardData(Request $request) {

		try{
			$data = DB::select("[spVDWH_GetCardData]");
			$res['success'] = true;
			$res['result'] = $data;

			return response($res);
		} catch(QueryException $ex){
			$res['success'] = false;
			$res['result'] = 'Query Exception.. Please Check Database!';

			return response($res);
		}

	}

  public function getUsersData(Request $request) {

    $FCODE = $request->FCODE;
    $user_id = $request->user_id;

		try{
			$data = DB::select("[spVDWH_ViewUser] '$FCODE','$user_id'");

      $data = json_encode($data);
      $data = json_decode($data, true);

      $total = count($data);

      for($i = 0; $i < $total; $i++)
      {
        $user_data['FID'] = $data[$i]['FID'];
        $user_data['merch_id'] = $data[$i]['merch_id'];
        $merchid = $user_data['merch_id'];

        if($user_data['FID'] == '99')
        {
          $merchant_call = DB::select("[spVDWH_GetMerchantbyFID] '$merchid'");

          $merchant_call = json_encode($merchant_call);
          $merchant_call = json_decode($merchant_call, true);

          $data[$i]['FNAME'] = $merchant_call[0]['FMERCHNAME'];
        }
        else if($user_data['FID'] == '1909')
        {
          $data[$i]['FNAME'] = 'Wirecard';
        }

      }

			$res['success'] = true;
			$res['result'] = $data;

			return response($res);
      //return Response::json($res);
		} catch(QueryException $ex){
			$res['success'] = false;
			$res['result'] = 'Query Exception.. Please Check Database!';

			return response($res);
		}

	}

  public function getGroupsData(Request $request)
  {

    $FCODE = $request->FCODE;
    $group_id = $request->group_id;

    try{
      $data = DB::select("[spVDWH_ViewGroup] '$FCODE','$group_id'");
      $data = json_encode($data);
      $data = json_decode($data, true);

      $total = count($data);

      for($i = 0; $i < $total; $i++)
      {
        $user_data['FID'] = $data[$i]['FID'];
        $user_data['merch_id'] = $data[$i]['merch_id'];
        $merchid = $user_data['merch_id'];
        $fid = $user_data['FID'];
        $group_id_each = $data[$i]['group_id'];

        if($user_data['FID'] != '99' && $user_data['FID'] != '1909')
        {
          $data1 = DB::select("[spVDWH_SelectBackEnd] '$fid'");
          $data1 = json_encode($data1);
          $data1 = json_decode($data1, true);

          $data[$i]['FMERCHNAME'] = $data1[0]['FNAME'];
        }
        else if($user_data['FID'] == '1909')
        {
          $data[$i]['FMERCHNAME'] = 'Wirecard';
        }
        else {
          $merchant_call = DB::select("[spVDWH_GetMerchantbyFID] '$merchid'");

          $merchant_call = json_encode($merchant_call);
          $merchant_call = json_decode($merchant_call, true);

          $data[$i]['FMERCHNAME'] = $merchant_call[0]['FMERCHNAME'];
        }

        $group_policy =  DB::select("[spVDWH_SelectGroupPolicy] '$group_id_each'");

        $group_policy = json_encode($group_policy);
        $group_policy = json_decode($group_policy, true);
        $totals = count($group_policy);
        // $garray = array();
        // for($j = 0; $j < $totals; $j++)
        // {
        //   $garray[$j] = $group_policy[$j]['policy_id'];
        // }
        // $data[$i]['GROUP_POLICY'] = $garray;

        $gar = '';
          for($j = 0; $j < $totals; $j++)
          {
            $gar .= $group_policy[$j]['policy_id'];
            if($j+1 != $totals)
            {
              $gar .= ',';
            }

          }
          $data[$i]['GROUP_POLICY'] = $gar;
      }

      $res['success'] = true;
      $res['result'] = $data;

      return response($res);
    } catch(QueryException $ex){
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }

  }

  public function getGroupData(Request $request)
    {
      $fcode = $request->fcode;
      $fid = $request->fid;

  		try
      {
  			$data = DB::select("[spVDWH_GetGroupData] '$fcode' , '$fid' ");
  			$res['success'] = true;
  			$res['result'] = $data;

  			return response($res);
  		}
      catch(QueryException $ex)
      {
  			$res['success'] = false;
  			$res['result'] = 'Query Exception.. Please Check Database!';

  			return response($res);
  		}
	}

  public function updatePassword(Request $request)
  {
    date_default_timezone_set('Asia/Jakarta');
    $now = date("Ymdhis");

    $user_id = $request->user_id;
    $name = $request->name;
    $username = $request->username;
    $oldPassword = $request->oldPassword;
    $newPassword = $request->newPassword;
    $note = $request->note;
    $desc = '';

    try
    {
      if($oldPassword != '')
      {
        $oldPassword = hash('sha256', $oldPassword);
      	$newPassword = hash('sha256',$newPassword);

        $check = DB::select("[spVDWH_ViewDetailUser] '$user_id'");

        $check = json_encode($check);
        $check = json_decode($check, true);

        $oldName = $check[0]['name'];
        $oldPassword2 = $check[0]['password'];
        if($name != $oldName)
        {
          $desc = "Change name, change password";
        }
        else
        {
          $desc = "Change password";
        }
        if ($oldPassword != $oldPassword2)
        {
          $res['status'] = '#ERROR';
          $res['message'] = 'Update Profile Failed';
        }
        else
        {
          $data = DB::statement("[spVDWH_UpdateProfile] '$user_id', '$username', '$newPassword', '$name', '$note'");
          if ($data)
          {
            Session::put('name', $name);

            $audit_trail = DB::statement("[spVDWH_InsertAuditTrail] '5', '$user_id', '$username', '$name', $now, '$desc'");

            $res['status'] = '#SUCCESS';
            $res['message'] = 'Update Profile Success';
          }
          else
          {
            $res['status'] = '#ERROR';
            $res['message'] = 'Update Profile Failed';
          }
        }
      }
      else
      {
      	$check = DB::select("[spVDWH_ViewDetailUser] '$user_id'");

        $check = json_encode($check);
        $check = json_decode($check, true);

        $oldName = $check[0]['name'];
        $oldPassword2 = $check[0]['password'];
        if($name != $oldName) {
      		$desc = "Change name";
      	}

        $data = DB::statement("[spVDWH_UpdateProfile] '$user_id', '$username', '$oldPassword2', '$name', '$note'");

      	if($data)
        {
          if($name != $oldName) {
        		$desc = "Change name";
        	}
      		$user_data['name'] = $name;

          $audit_trail = DB::statement("[spVDWH_InsertAuditTrail] '5', '$user_id', '$username', '$name', $now, '$desc'");

          $res['status'] = '#SUCCESS';
          $res['message'] = 'Update Profile Success';
          $res['data'] = $user_data;
      	}
        else
        {
          $res['status'] = '#ERROR';
          $res['message'] = 'Update Profile Failed';
      	}
      }
      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = '#ERROR';
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getInstituteData(Request $request)
    {
      $fcode = $request->fcode;

      try
      {
        if($fcode == 'pvs1909')
        {
            $data = DB::select("[spVDWH_ViewInstitute]");
        }
        else
        {
            $data = DB::select("[spVDWH_selectFID] '$fcode'");
        }

        $res['success'] = true;
        $res['result'] = $data;

        return response($res);
      }
      catch(QueryException $ex)
      {
        $res['success'] = false;
        $res['result'] = 'Query Exception.. Please Check Database!';

        return response($res);
      }
  }

  public function getPolicyData(Request $request)
    {
      $fcode = $request->fcode;

      try
      {
        $data = DB::select("[spVDWH_GetPolicyData] '$fcode'");

        $res['success'] = true;
        $res['result'] = $data;

        return response($res);
      }
      catch(QueryException $ex)
      {
        $res['success'] = false;
        $res['result'] = 'Query Exception.. Please Check Database!';

        return response($res);
      }
  }

  public function getBranchDataFiltered(Request $request)
  {
    $username = $request->username;

    try
    {
      $data = DB::select("[spVMonitoringReport_GetUserInfoBranch] '$username' ");
      $res['success'] = true;
      $res['result'] = $data;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getMerchantDataFiltered(Request $request)
  {
    $username = $request->username;

    try
    {
      $data = DB::select("[spVMonitoringReport_GetUserInfo] '$username' ");
      $res['success'] = true;
      $res['result'] = $data;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getHostDataFiltered(Request $request)
  {
    $username = $request->username;

    try
    {
      $data = DB::select("[spVMonitoringReport_GetUserInfoAcquirer] '$username' ");
      $res['success'] = true;
      $res['result'] = $data;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getLogo(Request $request)
  {
    $username = $request->username;

    try
    {
      $data = DB::select("[spVDWH_GetLogoName] '$username' ");
      $merch_logo 	= $data[0]->FMERCHLOGO;
      $res['success'] = true;
      $res['merchlogo'] = $merch_logo;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }

  public function getTerminalLocationData(Request $request)
  {
    try
    {
      $data = DB::select("[spVDWH_GetTerminalLocation]");
      $data = json_encode($data);
      $data = json_decode($data, true);

      foreach($data as $key => $value){
        $data[$key]['lat'] = (float) $data[$key]['lat'];
        $data[$key]['lng'] = (float) $data[$key]['lng'];
      }

      $res['success'] = true;
      $res['result'] = $data;

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Query Exception.. Please Check Database!';

      return response($res);
    }
  }
}
