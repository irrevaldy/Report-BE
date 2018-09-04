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

class privilegeController extends Controller
{
   	public function __construct(Request $request){
        
    }
	
    public function getPrivilegeData(Request $request, $id_privilege) {
		
		try{
			$data = DB::select("[spVDWH_GetPrivilegeData] '$id_privilege'");
			$res['success'] = true;
			$res['result'] = $data;
	
			return response($res);
		} catch(QueryException $ex){ 
			$res['success'] = false;
			$res['result'] = 'Query Exception.. Please Check Database!';
	
			return response($res);
		}
		
		return json_encode($result);
    }
	
	public function insertPrivilegeData(Request $request) {
		
		try{
			
			$privilegeCode 	= $request->input('privilegeCode_');
			$privilegeName 	= $request->input('privilegeName_');
			$description 	= $request->input('description_');
			
			$data = DB::statement("[spApiTMS_insertPrivilegeData] '$privilegeCode', '$privilegeName', '$description'");
			$res['success'] = true;
			$res['message'] = $data;
	
			return response($res);
		} catch(Exception $ex){ 
			$res['success'] = false;
			$res['message'] = 'Query Exception.. Please Check Database!';
	
			return response($res);
		}
		
		return json_encode($result);
    }
	
	public function updatePrivilegeData(Request $request) {
		
		try{
			
			$privilege_id 	= $request->input('privilege_id_');
			$privilegeCode 	= $request->input('privilegeCode_');
			$privilegeName 	= $request->input('privilegeName_');
			$description 	= $request->input('description_');
			
			$data = DB::statement("[spApiTMS_updatePrivilegeData] '$privilege_id', '$privilegeCode', '$privilegeName', '$description'");
			$res['success'] = true;
			$res['message'] = $data;
	
			return response($res);
		} catch(Exception $ex){ 
			$res['success'] = false;
			$res['message'] = 'Query Exception.. Please Check Database!';
	
			return response($res);
		}
		
		return json_encode($result);
    }
	
	public function deletePrivilegeData(Request $request) {
		
		try{
			
			$privilege_id 	= $request->input('privilege_id_');
			
			$data = DB::statement("[spApiTMS_deletePrivilegeData] '$privilege_id'");
			$res['success'] = true;
			$res['message'] = $data;
	
			return response($res);
		} catch(Exception $ex){ 
			$res['success'] = false;
			$res['message'] = 'Query Exception.. Please Check Database!';
	
			return response($res);
		}
		
		return json_encode($result);
    }
	
}
