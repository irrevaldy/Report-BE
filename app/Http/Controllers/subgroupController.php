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

class subgroupController extends Controller
{
   	public function __construct(Request $request){
        
    }
	
    public function getSubgroupData(Request $request, $id_subgroup) {
		
		try{
			$data = DB::select("[spVDWH_GetSubgroupData] '$id_subgroup'");
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
	
    public function getTranSubgroupPrivilegeData(Request $request, $id_subgroup) {
		
		try{
			$data = DB::select("[spVDWH_GetTranSubgroupPrivilegeData] '$id_subgroup'");
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
	
	public function getSubgroupPerGroupData(Request $request, $id_group) {
		
		try{
			$data = DB::select("[spVDWH_GetSubgroupPerGroupData] '$id_group'");
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
	
	public function insertSubgroupData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			
			$username		= $request->input('username');
			
			$subgroupName 	= $request->input('subgroupName_');
			$groupId 		= $request->input('groupId_');
			$description 	= $request->input('description_');
			$privilege 		= $request->input('privilege_');
			
			$register = DB::select("[spVDWH_InsertSubgroupData] '$subgroupName', '$groupId', '$description'");
			
			if($register){
				$register = json_encode($register);
				$register = json_decode($register, true); 
				
				$subgroup_id = $register[0]['id'];
				
				$privilege = json_decode($privilege, true); 
				
				foreach($privilege as $key => $value) {
					
					$tran = DB::statement("[spVDWH_InsertTranSubgroupPrivilegeData] '$subgroup_id', '$value'");
					
					if( $tran ) {
						$res['success'] = true;
						$res['message'] = 'Insert data success !';
					} else {
						$res['success'] = false;
						$res['message'] = "Insert data failed ! ( ".$subgroup_id." - ".$value." )";
						DB::rollback();
						return response($res);
					}
					
				}
				
				$data_privileges = DB::select("[spVDWH_GetPrivilegeData] 'all'");
				foreach( $data_privileges as $key => $value ) {
					$id 					= $value->id;
					$privilege_name 		= $value->privilege_name;
					
					$list_privilege[$id] 	= $privilege_name;
				}
				
				$data_group = DB::select("[spVDWH_GetGroupData] 'all'");
				foreach( $data_group as $key => $value ) {
					$id 				= $value->id;
					$group_fullname 	= $value->group_fullname;
					
					$list_group[$id] = $group_fullname;
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
				// $activity[] = "[User_Management][Subgroup] Add Subgroup : ".$subgroupName;
				// $activity[] = "[User_Management][Subgroup] Subgroup name : ".$subgroupName;
				// $activity[] = "[User_Management][Subgroup] Group : ".$list_group[$groupId];
				// $activity[] = "[User_Management][Subgroup] Description : ".$description;
				
				// foreach($privilege as $key => $value) {
				// 	$activity[] = "[User_Management][Subgroup] Privilege : ".$list_privilege[$value];
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
				
				$res['success'] = true;
				$res['message'] = 'Insert data success !';
				DB::commit();
				
				return response($res);
				
			} else {
				$res['success'] = false;
				$res['message'] = "Insert data failed !";
				DB::rollback();
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
	
	public function updateSubgroupData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			
			$username		= $request->input('username');
			
			$subgroup_id 	= $request->input('subgroup_id_');
			$subgroupName 	= $request->input('subgroupName_');
			$groupId 		= $request->input('groupId_');
			$description 	= $request->input('description_');
			$privilege 		= $request->input('privilege_');
			
			$old_data = DB::select("[spVDWH_GetSubgroupData] '$subgroup_id'");
								
			$old_user_group_id		= $old_data[0]->user_group_id;
			$old_subgroup_name		= $old_data[0]->subgroup_name;
			$old_description		= $old_data[0]->description;
			
			$data = DB::statement("[spVDWH_UpdateSubgroupData] '$subgroup_id', '$subgroupName', '$groupId', '$description'");
			
			if( $data ) {
				
				$old_privileges = DB::select("[spVDWH_GetTranSubgroupPrivilegeData] '$subgroup_id'");
				foreach( $old_privileges as $key => $value ) {
					
					$privilege_id 		= $value->privilege_id;
					
					$old_privilege_list[] 	= $privilege_id;
				}
				
				$delete = DB::statement("[spVDWH_DeleteTranSubgroupPrivilegeData] '$subgroup_id'");
				
				if( $delete ) {
					$res['success'] = true;
				} else {
					$res['success'] = false;
					$res['message'] = "Delete tran subgroup privilege failed !";
					
					DB::rollback();
	
					return response($res);
				}
				
				if( $res['success'] == true ) {
					$privilege = json_decode($privilege, true); 
				
					foreach($privilege as $key => $value) {
					
						$tran = DB::statement("[spVDWH_InsertTranSubgroupPrivilegeData] '$subgroup_id', '$value'");
						
						if( $tran ) {
							$res['success'] = true;
							$res['message'] = 'Insert data success !';
						} else {
							$res['success'] = false;
							$res['message'] = "Insert data failed ! ( ".$subgroup_id." - ".$value." )";
							DB::rollback();
							return response($res);
						}
						
					}
				}
				
				$data_privileges = DB::select("[spVDWH_GetPrivilegeData] 'all'");
				foreach( $data_privileges as $key => $value ) {
					$id 					= $value->id;
					$privilege_name 		= $value->privilege_name;
					
					$list_privilege[$id] 	= $privilege_name;
				}
				
				$data_group = DB::select("[spVDWH_GetGroupData] 'all'");
				foreach( $data_group as $key => $value ) {
					$id 				= $value->id;
					$group_fullname 	= $value->group_fullname;
					
					$list_group[$id] = $group_fullname;
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
				// $activity[] = "[User_Management][Subgroup] Edit Subgroup : ".$old_subgroup_name;
				// if( $old_user_group_id != $groupId ) { 
				// 	$audit_trail_check = '1';
				// 	$activity[] = "[User_Management][Subgroup] Group, old value : ".$list_group[$old_user_group_id]." -> new value : ".$list_group[$groupId]; 
				// }
				// if( $old_subgroup_name != $subgroupName ) { 
				// 	$audit_trail_check = '1';
				// 	$activity[] = "[User_Management][Subgroup] Subgroup name, old value : ".$old_subgroup_name." -> new value : ".$subgroupName; 
				// }
				// if( $old_description != $description ) { 
				// 	$audit_trail_check = '1';
				// 	$activity[] = "[User_Management][Subgroup] Description, old value : ".$old_description." -> new value : ".$description; 
				// }
				
				// foreach ( $old_privilege_list as $key => $value ) {
				// 	if( !in_array($value, $privilege) ) {
				// 		$audit_trail_check = '1';
				// 		$activity[] = "[User_Management][Subgroup] Delete privilege : ".$list_privilege[$value];
				// 	}
				// }
				
				// foreach ( $privilege as $key => $value ) {
				// 	if( !in_array($value, $old_privilege_list) ) {
				// 		$audit_trail_check = '1';
				// 		$activity[] = "[User_Management][Subgroup] Add privilege : ".$list_privilege[$value];
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
			
				$res['success'] = true;
				$res['result'] = $data;
				DB::commit();
				return response($res);
				
			} else {
				$res['success'] = false;
				$res['message'] = "Insert data failed ! ";
				DB::rollback();
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
	
	public function deleteSubgroupData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			$username 		= $request->input('username');
			$subgroup_id 	= $request->input('subgroup_id_');
			
			$old_data = DB::select("[spVDWH_GetSubgroupData] '$subgroup_id'");
			$old_subgroup_name		= $old_data[0]->subgroup_name;
			
			$delete = DB::statement("[spVDWH_DeleteTranSubgroupPrivilegeData] '$subgroup_id'");
			
			if( $delete ) {
				$data = DB::statement("[spVDWH_DeleteSubgroupData] '$subgroup_id'");
				
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
				
				// $activity[] = "[User_Management][Subgroup] Delete Subgroup, subgroup name : ".$old_subgroup_name; 
				
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
				$res['message'] = 'Delete data success ! ';
				DB::commit();
				return response($res);
			} else {
				$res['success'] = true;
				$res['message'] = 'Delete data failed ! ';
				DB::rollback();
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
