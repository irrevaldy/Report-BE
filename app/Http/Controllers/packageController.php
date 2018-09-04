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

class packageController extends Controller
{
   	public function __construct(Request $request){
        
    }
	
    public function getPackageData(Request $request, $id_package) {
		
		try{
			$data = DB::select("[spVDWH_GetPackageData] '$id_package'");
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
	
    public function getTranPackagePrivilegeData(Request $request, $id_package) {
		
		try{
			$data = DB::select("[spVDWH_GetTranPackagePrivilegeData] '$id_package'");
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
	
	public function insertPackageData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			
			$username		= $request->input('username');
			
			$packageName 	= $request->input('packageName_');
			$description 	= $request->input('description_');
			$privilege 		= $request->input('privilege_');
			
			$register = DB::select("[spVDWH_InsertPackageData] '$packageName', '$description'");
			
			$res['success'] = true;
			
			if($register){
				$register = json_encode($register);
				$register = json_decode($register, true); 
				
				$package_id = $register[0]['id'];
				
				$privilege = json_decode($privilege, true); 
				
				foreach($privilege as $key => $value) {
					
					$tran = DB::statement("[spVDWH_InsertTranPackagePrivilegeData] '$package_id', '$value'");
					
					if( $tran ) {
						$res['success'] = true;
						$res['message'] = 'Insert data success !';
					} else {
						$res['success'] = false;
						$res['message'] = "Insert data failed ! ( ".$package_id." - ".$value." )";
						
						return response($res);
					}
					
				}
				
				$data_privileges = DB::select("[spVDWH_GetPrivilegeData] 'all'");
				foreach( $data_privileges as $key => $value ) {
					$id 					= $value->id;
					$privilege_name 		= $value->privilege_name;
					
					$list_privilege[$id] 	= $privilege_name;
				}
				
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
				
				/* audit trail */
				// $activity[] = "[User_Management][Package] Add Package : ".$packageName;
				// $activity[] = "[User_Management][Package] Package name : ".$packageName;
				// $activity[] = "[User_Management][Package] Description : ".$description;
				
				// foreach($privilege as $key => $value) {
				// 	$activity[] = "[User_Management][Package] Privilege : ".$list_privilege[$value];
				// }
				
				// foreach( $activity as $key => $value ) {
				// 	$activity_text = $value;
					
				// 	$insert_audit_trail = DB::statement("[spApiTMS_insertAuditTrail] '$user_id', '$username', '$name', '$datetime', '5', '$activity_text'");
					
				// 	if(!$insert_audit_trail) {
				// 		$res['success'] = false;
				// 		$res['message'] = 'Failed on insert audit trail !';
				// 		DB::rollback();
				// 		return response($res);
				// 	}
				// }
			}
			
			if( $res['success'] == true ) {
				DB::commit();
			}
	
			return response($res);
		} catch(QueryException $ex){ 
			$res['success'] = false;
			$res['message'] = 'Query Exception.. Please Check Database!';
			DB::rollback();
	
			return response($res);
		}
		
		return json_encode($result);
    }
	
	public function updatePackageData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			
			$username		= $request->input('username');
			
			$package_id 	= $request->input('package_id_');
			$packageName 	= $request->input('packageName_');
			$description 	= $request->input('description_');
			$privilege 		= $request->input('privilege_');
			
			$old_data = DB::select("[spVDWH_GetPackageData] '$package_id'");
								
			$old_package_name		= $old_data[0]->package_name;
			$old_description		= $old_data[0]->description;
			
			$data = DB::statement("[spVDWH_UpdatePackageData] '$package_id', '$packageName', '$description'");
			
			if( $data ) {
				
				$old_privileges = DB::select("[spVDWH_GetTranPackagePrivilegeData] '$package_id'");
				foreach( $old_privileges as $key => $value ) {
					
					$privilege_id 		= $value->privilege_id;
					
					$old_privilege_list[] 	= $privilege_id;
				}
				
				$delete = DB::statement("[spVDWH_DeleteTranPackagePrivilegeData] '$package_id'");
				
				if( $delete ) {
					$res['success'] = true;
				} else {
					$res['success'] = false;
					$res['message'] = "Delete tran package privilege failed !";
					
					DB::rollback();
	
					return response($res);
				}
				
				if( $res['success'] == true ) {
					$privilege = json_decode($privilege, true); 
				
					foreach($privilege as $key => $value) {
						
						$tran = DB::statement("[spVDWH_InsertTranPackagePrivilegeData] '$package_id', '$value'");
						
						if( $tran ) {
							$res['success'] = true;
							$res['message'] = 'Insert data success !';
						} else {
							$res['success'] = false;
							$res['message'] = "Insert data failed ! ( ".$package_id." - ".$value." )";
							DB::rollback();
							return response($res);
						}
						
					}
				}
				
			}
			
			$data_privileges = DB::select("[spVDWH_GetPrivilegeData] 'all'");
			foreach( $data_privileges as $key => $value ) {
				$id 					= $value->id;
				$privilege_name 		= $value->privilege_name;
				
				$list_privilege[$id] 	= $privilege_name;
			}
			
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
			
			/* audit trail */
			// $activity = array();
			// $audit_trail_check = '0';
			// $activity[] = "[User_Management][Group] Edit Group : ".$old_package_name;	
			// if( $old_package_name != $packageName ) { 
			// 	$audit_trail_check = '1';
			// 		$activity[] = "[User_Management][Group] Group name, old value : ".$old_package_name." -> new value : ".$packageName; 
			// }
			// if( $old_description != $description ) { 
			// 	$audit_trail_check = '1';
			// 		$activity[] = "[User_Management][Group] Description, old value : ".$old_description." -> new value : ".$description; 
			// }
			
			// foreach ( $old_privilege_list as $key => $value ) {
			// 	if( !in_array($value, $privilege) ) {
			// 		$audit_trail_check = '1';
			// 		$activity[] = "[User_Management][Group] Delete privilege : ".$list_privilege[$value];
			// 	}
			// }
			
			// foreach ( $privilege as $key => $value ) {
			// 	if( !in_array($value, $old_privilege_list) ) {
			// 		$audit_trail_check = '1';
			// 		$activity[] = "[User_Management][Group] Add privilege : ".$list_privilege[$value];
			// 	}
			// }
			
			// if( $audit_trail_check != '1' ) {
			// 	$activity = array();
			// }
			
			// foreach( $activity as $key => $value ) {
			// 	$activity_text = $value;
				
			// 	$insert_audit_trail = DB::statement("[spApiTMS_insertAuditTrail] '$user_id', '$username', '$name', '$datetime', '6', '$activity_text'");
				
			// 	if(!$insert_audit_trail) {
			// 		$res['success'] = false;
			// 		$res['message'] = 'Failed on insert audit trail !';
					
			// 		DB::rollback();
			// 		return response($res);
			// 	}
			// }
			
			if( $res['success'] == true ) {
			
				$res['success'] = true;
				$res['message'] = "Update data success !";
				DB::commit();
		
				return response($res);
				
			}
		} catch(QueryException $ex){ 
			$res['success'] = false;
			$res['result'] = 'Query Exception.. Please Check Database!';
			DB::rollback();
	
			return response($res);
		}
		
		return json_encode($result);
    }
	
	public function deletePackageData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			$username 		= $request->input('username');
			$package_id 	= $request->input('package_id_');
			
			$old_data = DB::select("[spVDWH_GetPackageData] '$package_id'");
								
			$old_package_name		= $old_data[0]->package_name;
			
			$delete = DB::statement("[spVDWH_DeleteTranPackagePrivilegeData] '$package_id'");
				
			if( $delete ) {
				$res['success'] = true;
			} else {
				$res['success'] = false;
				$res['message'] = "Delete tran package privilege failed !";
				
				DB::rollback();
	
				return response($res);
			}
			
			if( $res['success'] == true ) {
				$data = DB::statement("[spVDWH_DeletePackageData] '$package_id'");
				
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
				
				// $activity[] = "[User_Management][Group] Delete Group, group name : ".$old_package_name; 
				
				// foreach( $activity as $key => $value ) {
				// 	$activity_text = $value;
					
				// 	$insert_audit_trail = DB::statement("[spApiTMS_insertAuditTrail] '$user_id', '$username', '$name', '$datetime', '7', '$activity_text'");
					
				// 	if(!$insert_audit_trail) {
				// 		$res['success'] = false;
				// 		$res['message'] = 'Failed on insert audit trail !';
				// 		DB::rollback();
				// 		return response($res);
				// 	}
				// }
				
				$res['success'] = true;
				$res['message'] = "Delete data success !";
				DB::commit();
		
				return response($res);
			}
			
			
		} catch(QueryException $ex){ 
			$res['success'] = false;
			$res['message'] = 'Query Exception.. Please Check Database!';
			DB::rollback();
	
			return response($res);
		}
		
		return json_encode($result);
    }
	
}
