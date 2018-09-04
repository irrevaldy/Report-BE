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

class groupController extends Controller
{
   	public function __construct(Request $request){
        
    }
	
    public function getGroupData(Request $request, $id_group) {
		
		try{
			$data = DB::select("[spVDWH_getGroupData] '$id_group'");
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
	
    public function getGroupPrivilegeData(Request $request, $id_group) {
		
		try{
			$data = DB::select("[spVDWH_GetGroupPrivilegeData] '$id_group'");
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
	
    public function getGroupFilterTypeData(Request $request, $id_group) {
		
		try{
			$data = DB::select("[spVDWH_GetGroupFilterTypeData] '$id_group'");
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
	
	public function insertGroupData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			
			$username			= $request->input('username');
			
			$groupName 			= $request->input('groupName_');
			$packageId 			= $request->input('packageId_');
			$description 		= $request->input('description_');
			$filter_type_id		= $request->input('filter_type_id_');
			$filter_type_value	= $request->input('filter_type_value_');
			
			$register = DB::select("[spVDWH_InsertGroupData] '$groupName', '$packageId', '$description'");
			
			if( $register ) {
				
				$register = json_encode($register);
				$register = json_decode($register, true); 
				
				$group_id = $register[0]['id'];
				
				foreach($filter_type_id as $key => $value) {
					
					$filter_id = $value;
					$filter_value = $filter_type_value[$key];
					
					$tran = DB::statement("[spVDWH_InsertUserGroupConfigData] '$group_id', '$filter_id', '$filter_value'");
					
					if( $tran ) {
						$res['success'] = true;
						$res['message'] = 'Insert data success !';
					} else {
						$res['success'] = false;
						$res['message'] = "Insert data failed ! ( ".$group_id." - ".$filter_id." - ".$filter_value." )";
						DB::rollback();
						return response($res);
					}
					
				}
				
				if( $res['success'] == true ) {
					
					$data_package = DB::select("[spVDWH_GetPackageData] 'all'");
					foreach( $data_package as $key => $value ) {
						$id 				= $value->id;
						$package_name 		= $value->package_name;
						
						$list_package[$id] 	= $package_name;
					}
					
					$data_filter = DB::select("[spVDWH_GetFilterTypeData] 'all'");
					foreach( $data_filter as $key => $value ) {
						$id 				= $value->id;
						$name 				= $value->name;
						
						$list_filter[$id] 	= $name;
					}
					
					$get_user_detail = DB::select("[spVDWH_GetUserDetail] '$username'");
					if( $get_user_detail ) {
						
						$user_id 	= $get_user_detail[0]->user_id;
						$name_audit = $get_user_detail[0]->name;
						$datetime	= date("jS F Y H:i:s");
						
					} else {
						$res['success'] = false;
						$res['message'] = 'Failed on get user detail !';
						DB::rollback();
						return response($res);
					}
					
					/* audit trail */
					// $activity[] = "[User_Management][Group] Add Group : ".$groupName;
					// $activity[] = "[User_Management][Group] Name : ".$groupName;
					// $activity[] = "[User_Management][Group] Subgroup : ".$list_package[$packageId];
					// $activity[] = "[User_Management][Group] Description : ".$description;
					
					// if( $filter_type_id != '' ) {
					// 	foreach($filter_type_id as $key => $value) {
					// 		$filter_id = $value;
					// 		$filter_value = $filter_type_value[$key];
							
					// 		$activity[] = "[User_Management][Group] Filter type : ".$list_filter[$filter_id].", value : ".$filter_value;
					// 	}
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
					$res['message'] = "Insert data success ! ";
					
					DB::commit();
					return response($res);
				} else {
					$res['success'] = false;
					$res['message'] = "Insert data failed ! ";
					DB::rollback();
					return response($res);
				}
			} else {
				$res['success'] = false;
				$res['message'] = "Insert data failed on register group ! ";
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
	
	public function updateGroupData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			
			$username			= $request->input('username');
			
			$group_id 			= $request->input('group_id_');
			$groupName 			= $request->input('groupName_');
			$packageId 			= $request->input('packageId_');
			$description 		= $request->input('description_');
			$groupActive 		= $request->input('groupActive_');
			$filter_type_id		= $request->input('filter_type_id_');
			$filter_type_value	= $request->input('filter_type_value_');
			
			$old_data = $data = DB::select("[spVDWH_GetGroupData] '$group_id'");
								
			$old_package_id			= $old_data[0]->package_id;
			$old_group_fullname		= $old_data[0]->group_fullname;
			$old_description		= $old_data[0]->description;
			$old_group_active		= $old_data[0]->group_active;
			
			$update = DB::statement("[spVDWH_UpdateGroupData] '$group_id', '$groupName', '$packageId', '$description', '$groupActive'");
			
			if( $update ) {
				
				$data_package = DB::select("[spVDWH_GetPackageData] 'all'");
				foreach( $data_package as $key => $value ) {
					$id 				= $value->id;
					$package_name 		= $value->package_name;
					
					$list_package[$id] 	= $package_name;
				}
				
				$data_filter = DB::select("[spVDWH_GetFilterTypeData] 'all'");
				foreach( $data_filter as $key => $value ) {
					$id 				= $value->id;
					$name 				= $value->name;
					
					$list_filter[$id] 	= $name;
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
				// $activity[] = "[User_Management][Group] Edit Group : ".$old_group_fullname;	
				// if( $old_package_id != $packageId ) { 
				// 	$audit_trail_check = '1';
				// 				$activity[] = "[User_Management][Group] Package, old value : ".$list_package[$old_package_id]." -> new value : ".$list_package[$packageId]; 
				// }
				
				// if( $old_group_fullname != $groupName ) { 
				// 	$audit_trail_check = '1';
				// 				$activity[] = "[User_Management][Group] Group name, old value : ".$old_group_fullname." -> new value : ".$groupName; 
				// }
				
				// if( $old_description != $description ) { 
				// 	$audit_trail_check = '1';
				// 				$activity[] = "[User_Management][Group] Description, old value : ".$old_description." -> new value : ".$description; 
				// }
				
				// if( $old_group_active != $groupActive ) { 
				// 	$audit_trail_check = '1';
				// 				$activity[] = "[User_Management][Group] Status, old value : ".$old_group_active." -> new value : ".$groupActive; 
				// }
				
				$old_filter = DB::select("[spVDWH_GetGroupFilterTypeData] '$group_id'"); 
				$old_filter_list = [];
				foreach( $old_filter as $key => $value ) {
					
					$filter_id 		= $value->data_filter_type_id;
					$filter_value 	= $value->value;
					
					$old_filter_list[ $filter_id ] = $filter_value;
				}
				
				$delete = DB::statement("[spVDWH_DeleteGroupFilterTypeData] '$group_id'");
				if( $delete ) {
					$res['success'] = true;
				} else {
					$res['success'] = false;
					$res['message'] = "Delete group filter type failed !";
					
					DB::rollback();
	
					return response($res);
				}
				
				if( $res['success'] == true ) {
					foreach($filter_type_id as $key => $value) {
					
						$filter_id = $value;
						$filter_value = $filter_type_value[$key];
						
						$new_filter_list[ $filter_id ] = $filter_value;
						
						$tran = DB::statement("[spVDWH_InsertUserGroupConfigData] '$group_id', '$filter_id', '$filter_value'");
						
						if( $tran ) {
							$res['success'] = true;
							$res['message'] = 'Insert data success !';
						} else {
							$res['success'] = false;
							$res['message'] = "Insert data failed ! ( ".$group_id." - ".$filter_id." - ".$filter_value." )";
							DB::rollback();
							return response($res);
						}
						
					}
					
					foreach( $old_filter_list as $key => $value ) {
						
						if( !array_key_exists( $key, $new_filter_list ) ) {
							$audit_trail_check = '1';
								$activity[] = "[User_Management][Group] Delete filter type, filter : ".$list_filter[$key].", value : ".$value; 
						}
						
					}
					
					foreach( $new_filter_list as $key => $value ) {
						if( !array_key_exists( $key, $old_filter_list ) ) {
							$audit_trail_check = '1';
								$activity[] = "[User_Management][Group] Add filter type, filter : ".$list_filter[$key].", value : ".$value; 
						} else {
							if( $value != $old_filter_list[$key] ) {
								$audit_trail_check = '1';
								$activity[] = "[User_Management][Group] Edit filter type, filter : ".$list_filter[$key].", old value : ".$value;
							}
						}
					}
				}
				
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
					$res['message'] = "Update data success ! ";
					
					DB::commit();
					return response($res);
				} else {
					$res['success'] = false;
					$res['message'] = "Update data failed on update user group data ! ";
					DB::rollback();
					return response($res);
				}
			} else {
				$res['success'] = false;
				$res['message'] = "Update data failed ! ";
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
	
	public function deleteGroupData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			$username 	= $request->input('username');
			$group_id 	= $request->input('group_id_');
			
			$old_data = $data = DB::select("[spVDWH_GetGroupData] '$group_id'");
								
			$old_group_fullname		= $old_data[0]->group_fullname;
			
			$delete = DB::statement("[spVDWH_DeleteGroupFilterTypeData] '$group_id'");
			if( $delete ) {
				
				$data = DB::statement("[spVDWH_DeleteGroupData] '$group_id'");
				
				if( $data ) {
					
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
					
					// $activity[] = "[User_Management][Group] Delete Group, group name : ".$old_group_fullname; 
					
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
					$res['result'] = $data;
					DB::commit();
					return response($res);
				} else {
					$res['success'] = false;
					$res['message'] = "Delete group data type failed !";
					
					DB::rollback();
		
					return response($res);	
				}
				
			} else {
				$res['success'] = false;
				$res['message'] = "Delete group filter type failed !";
				
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
}
