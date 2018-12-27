<?php



namespace App\Http\Controllers;

ini_set('max_input_vars', 1000000);

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Session;
use Exception;
use QueryException;

class userController extends Controller
{
   	public function __construct(Request $request){

    }

    public function getUserData(Request $request, $id_user) {

		try{
			$data = DB::select("[spVDWH_GetUserData] '$id_user'");
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

    public function getUserFilterTypeData(Request $request, $id_user) {

		try{
			$data = DB::select("[spVDWH_GetUserFilterTypeData] '$id_user'");
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

	public function getFilterValueOption(Request $request, $filter_type) {

		try{
			$data = DB::select("[spVDWH_GetFilterValueOption] '$filter_type'");
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

    public function getFilterValueOptionAugmented(Request $request)
    {
  		try
      {
        $chosen_acquirer 				= $request->input('chosen_acquirer');
        $chosen_corporate 				= $request->input('chosen_corporate');
        $chosen_merchant 				= $request->input('chosen_merchant');
        $chosen_branch 				= $request->input('chosen_branch');
        $chosen_store 				= $request->input('chosen_store');
        $filter_type 				= $request->input('filter_type');

  			$data = DB::select("[spVDWH_GetFilterValueOptionAugmented] '$chosen_acquirer','$chosen_corporate','$chosen_merchant','$chosen_branch','$chosen_store','$filter_type'");
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
  		  return json_encode($result);
      }

    public function getFilterValueOptionSelected(Request $request, $filter_type, $user_id) {

  		try{

  			$data = DB::select("[spVDWH_GetFilterValueOptionSelected] '$filter_type','$user_id'");
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

    public function getFilterValueOptionSelectedAugmented(Request $request)
    {
  		try
      {
        $chosen_acquirer 				= $request->input('chosen_acquirer');
        $chosen_corporate 				= $request->input('chosen_corporate');
        $chosen_merchant 				= $request->input('chosen_merchant');
        $chosen_branch 				= $request->input('chosen_branch');
        $chosen_store 				= $request->input('chosen_store');
        $filter_type 				= $request->input('filter_type');
        $user_id = $request->input('user_id');

  			$data = DB::select("[spVDWH_GetFilterValueOptionSelectedAugmented] '$chosen_acquirer','$chosen_corporate','$chosen_merchant','$chosen_branch','$chosen_store','$filter_type','$user_id'");
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
  		  return json_encode($result);
      }

    public function getUserPrivilegeData(Request $request, $username) {

		try{
			$data = DB::select("[spVDWH_getUserPrivilegeData] '$username'");
			$res['success'] = true;
			$res['result'] = $data;

			return response($res);
		} catch(Exception $ex){
			$res['success'] = false;
			$res['result'] = 'Query Exception.. Please Check Database!';

			return response($res);
		}

		return json_encode($result);
    }

	public function insertUserData(Request $request) {

		DB::beginTransaction();

		try{

			$username_audit		= $request->input('username');

			$name 				= $request->input('name_');
			$username 			= $request->input('username_');
			$password 			= $request->input('password_');
			$subgroupId 		= $request->input('subgroupId_');
			$description 		= $request->input('description_');
			// $filter_type_id		= $request->input('filter_type_id_');
			// $filter_type_value	= $request->input('filter_type_value_');

      $filter_acquirer_list = $request->input('filter_acquirer_list_');
      $filter_corporate_list = $request->input('filter_corporate_list_');
      $filter_merchant_list = $request->input('filter_merchant_list_');
      $filter_branch_list = $request->input('filter_branch_list_');
      $filter_store_list = $request->input('filter_store_list_');

			$register = DB::select("[spVDWH_InsertUserData] '$name', '$username', '$password', '$subgroupId', '$description'");

			if( $register ) {

				$register = json_encode($register);
				$register = json_decode($register, true);

				$res['success'] = true;

				$user_id = $register[0]['user_id'];

				/*if( $filter_type_id != '' ) {
					foreach($filter_type_id as $key => $value) {

						$filter_id = $value;
						$filter_value = $filter_type_value[$key];

						$tran = DB::statement("[spVDWH_InsertUserFilterTypeData] '$user_id', '$filter_id', '$filter_value'");

						if( $tran ) {
							$res['success'] = true;
							$res['message'] = 'Insert data success !';
						} else {
							$res['success'] = false;
							$res['message'] = "Insert data failed ! ( ".$user_id." - ".$filter_id." - ".$filter_value." )";
							DB::rollback();
							return response($res);
						}

					}
				}*/

        if ($filter_acquirer_list != null) {

                $filter_acquirer_list = explode(',', $filter_acquirer_list);

                for($i = 0; $i < count($filter_acquirer_list); $i++){
                    // $q_insert_tran_store = DB::statement("INSERT INTO tran_user_cif_store (user_cif_id, store_id) VALUES ('$user_id', '$filter_store_list[$i]')");

                    $q_insert_filter_acquirer = DB::statement("[spVDWH_InsertUserFilterTypeData] '$user_id', '1', '$filter_acquirer_list[$i]'");
                }

            }

            if ($filter_corporate_list != null) {

                $filter_corporate_list = explode(',', $filter_corporate_list);

                for($i = 0; $i < count($filter_corporate_list); $i++){

                    $q_insert_filter_corporate = DB::statement("[spVDWH_InsertUserFilterTypeData] '$user_id', '2', '$filter_corporate_list[$i]'");
                }

            }

            if ($filter_merchant_list != null) {

                $filter_merchant_list = explode(',', $filter_merchant_list);

                for($i = 0; $i < count($filter_merchant_list); $i++){

                    $q_insert_filter_merchant = DB::statement("[spVDWH_InsertUserFilterTypeData] '$user_id', '3', '$filter_merchant_list[$i]'");
                }

            }

            if ($filter_branch_list != null) {

                $filter_branch_list = explode(',', $filter_branch_list);

                for($i = 0; $i < count($filter_branch_list); $i++){

                    $q_insert_filter_branch = DB::statement("[spVDWH_InsertUserFilterTypeData] '$user_id', '4', '$filter_branch_list[$i]'");
                }

            }

            if ($filter_store_list != null) {

                $filter_store_list = explode(',', $filter_store_list);

                for($i = 0; $i < count($filter_store_list); $i++){

                    $q_insert_filter_store = DB::statement("[spVDWH_InsertUserFilterTypeData] '$user_id', '5', '$filter_store_list[$i]'");
                }

            }

      if(  $q_insert_filter_acquirer || $q_insert_filter_corporate || $q_insert_filter_merchant || $q_insert_filter_branch ) {
              $res['success'] = true;
              $res['message'] = 'Insert data success !';
            } else {
              $res['success'] = false;
              $res['message'] = "Insert data failed ! ";
              DB::rollback();
              return response($res);
            }


				if( $res['success'] == true ){

					$data_subgroup = DB::select("[spVDWH_GetSubgroupData] 'all'");
					foreach( $data_subgroup as $key => $value ) {
						$id 				= $value->id;
						$subgroup_name 		= $value->subgroup_name;

						$list_subgroup[$id] = $subgroup_name;
					}

					$data_filter = DB::select("[spVDWH_GetFilterTypeData] 'all'");
					foreach( $data_filter as $key => $value ) {
						$id 				= $value->id;
						$name 				= $value->name;

						$list_filter[$id] 	= $name;
					}

					$get_user_detail = DB::select("[spVDWH_GetUserDetail] '$username_audit'");
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
					// $activity[] = "[User_Management][User] Add User : ".$name;
					// $activity[] = "[User_Management][User] Name : ".$name;
					// $activity[] = "[User_Management][User] Username : ".$username;
					// $activity[] = "[User_Management][User] Subgroup : ".$list_subgroup[$subgroupId];
					// $activity[] = "[User_Management][User] Description : ".$description;

					// if( $filter_type_id != '' ) {
					// 	foreach($filter_type_id as $key => $value) {
					// 		$filter_id = $value;
					// 		$filter_value = $filter_type_value[$key];

					// 		$activity[] = "[User_Management][User] Filter type : ".$list_filter[$filter_id].", value : ".$filter_value;
					// 	}
					// }

					// foreach( $activity as $key => $value ) {
					// 	$activity_text = $value;

					// 	$insert_audit_trail = DB::statement("[spApiTMS_insertAuditTrail] '$user_id', '$username_audit', '$name_audit', '$datetime', '5', '$activity_text'");

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
					$res['message'] = 'Insert data failed !';
					DB::rollback();
					return response($res);

				}

			} else {
				$res['success'] = false;
				$res['message'] = 'Insert data failed on insert tms user data !';
				DB::rollback();
				return response($res);
			}
		} catch(Exception $ex){
			$res['success'] = false;
			$res['message'] = 'Insert Failed. Please Check All Your Data and Filter Type!';
			DB::rollback();
			return response($res);
		}

		return json_encode($result);
    }

	public function updateUserData(Request $request)
  {
		DB::beginTransaction();

		try
    {
			$username_audit		= $request->input('username');

			$user_id 			= $request->input('user_id_');
			$name 				= $request->input('name_');
			$username 			= $request->input('username_');
			$password 			= $request->input('password_');
			$subgroupId 		= $request->input('subgroupId_');
			$description 		= $request->input('description_');
			$userActive 		= $request->input('userActive_');
      if($userActive == 'on')
      {
        $userActive = '1';
      }
      $filter_acquirer_list = $request->input('filter_acquirer_list_');
      $filter_corporate_list = $request->input('filter_corporate_list_');
      $filter_merchant_list = $request->input('filter_merchant_list_');
      $filter_branch_list = $request->input('filter_branch_list_');
      $filter_store_list = $request->input('filter_store_list_');

			$old_data = $data 	= DB::select("[spVDWH_GetUserData] '$user_id'");

			$old_user_subgroup_id	= $old_data[0]->user_subgroup_id;
			$old_user_name			= $old_data[0]->user_name;
			$old_name				= $old_data[0]->name;
			$old_description		= $old_data[0]->description;
			$old_user_active		= $old_data[0]->user_active;

			$update = DB::statement("[spVDWH_UpdateUserData] '$user_id', '$name', '$password', '$subgroupId', '$description', '$userActive'");

			if( $update )
      {
				$data_subgroup = DB::select("[spVDWH_GetSubgroupData] 'all'");
				foreach( $data_subgroup as $key => $value )
        {
					$id 				= $value->id;
					$subgroup_name 		= $value->subgroup_name;

					$list_subgroup[$id] = $subgroup_name;
				}

				$data_filter = DB::select("[spVDWH_GetFilterTypeData] 'all'");
				foreach( $data_filter as $key => $value )
        {
					$id 				= $value->id;
					$name 				= $value->name;

					$list_filter[$id] 	= $name;
				}

				$get_user_detail = DB::select("[spVDWH_GetUserDetail] '$username_audit'");
				if( $get_user_detail )
        {
					$user_id_audit 	= $get_user_detail[0]->user_id;
					$name_audit 	= $get_user_detail[0]->name;
					$datetime		= date("jS F Y H:i:s");

				}
        else
        {
					$res['success'] = false;
					$res['message'] = 'Failed on get user detail !';
					DB::rollback();
					return response($res);
				}

				/* audit trail */
				// $activity = array();
				// $audit_trail_check = '0';
				// $activity[] = "[User_Management][User] Edit User : ".$old_name;
				// if( $old_user_subgroup_id != $subgroupId ) {
				// 	$audit_trail_check = '1';
				// 	$activity[] = "[User_Management][User] Subgroup, old value : ".$list_subgroup[$old_user_subgroup_id]." -> new value : ".$list_subgroup[$subgroupId];
				// }

				// if( $old_name != $name ) {
				// 	$audit_trail_check = '1';
				// 	$activity[] = "[User_Management][User] Name, old value : ".$old_name." -> new value : ".$name;
				// }

				// if( $old_description != $description ) {
				// 	$audit_trail_check = '1';
				// 	$activity[] = "[User_Management][User] Description, old value : ".$old_description." -> new value : ".$description;
				// }

				// if( $old_user_active != $userActive ) {
				// 	$audit_trail_check = '1';
				// 	$activity[] = "[User_Management][User] Status, old value : ".$old_user_active." -> new value : ".$userActive;
				// }

				$old_filter_list = array();

				$old_filter = DB::select("[spVDWH_GetUserFilterTypeData] '$user_id'");
				foreach( $old_filter as $key => $value )
        {

					$filter_id 		= $value->data_filter_type_id;
					$filter_value 	= $value->value;

					$old_filter_list[ $filter_id ] = $filter_value;
				}

				$delete = DB::statement("[spVDWH_DeleteUserFilterTypeData] '$user_id'");
				if( $delete )
        {
					$res['success'] = true;
				}
        else
        {
					$res['success'] = false;
					$res['message'] = "Delete user filter type failed !";

					DB::rollback();

					return response($res);
				}

				if( $res['success'] == true )
        {
					$new_filter_list = array();

					// if( $filter_type_id != '' ) {
					// 	foreach($filter_type_id as $key => $value) {
          //
					// 		$filter_id = $value;
					// 		$filter_value = $filter_type_value[$key];
          //
					// 		$new_filter_list[ $filter_id ] = $filter_value;
          //
					// 		$tran = DB::statement("[spVDWH_InsertUserFilterTypeData] '$user_id', '$filter_id', '$filter_value'");
          //
					// 		if( $tran ) {
          //
					// 			$res['success'] = true;
					// 			$res['message'] = 'Insert data success !';
          //
					// 		} else {
          //
					// 			$res['success'] = false;
					// 			$res['message'] = "Insert data failed ! ( ".$user_id." - ".$filter_id." - ".$filter_value." )";
          //
					// 			DB::rollback();
					// 			return response($res);
          //
					// 		}
          //
					// 	}
					// }

          if ($filter_acquirer_list != null)
          {
                  $filter_acquirer_list = explode(',', $filter_acquirer_list);

                  for($i = 0; $i < count($filter_acquirer_list); $i++)
                  {
                      // $q_insert_tran_store = DB::statement("INSERT INTO tran_user_cif_store (user_cif_id, store_id) VALUES ('$user_id', '$filter_store_list[$i]')");

                      $q_insert_filter_acquirer = DB::statement("[spVDWH_InsertUserFilterTypeData] '$user_id', 1, '$filter_acquirer_list[$i]'");
                  }

              }

              if ($filter_corporate_list != null) {

                  $filter_corporate_list = explode(',', $filter_corporate_list);

                  for($i = 0; $i < count($filter_corporate_list); $i++){

                      $q_insert_filter_corporate = DB::statement("[spVDWH_InsertUserFilterTypeData] '$user_id', '2', '$filter_corporate_list[$i]'");
                  }

              }

              if ($filter_merchant_list != null) {

                  $filter_merchant_list = explode(',', $filter_merchant_list);

                  for($i = 0; $i < count($filter_merchant_list); $i++){

                      $q_insert_filter_merchant = DB::statement("[spVDWH_InsertUserFilterTypeData] '$user_id', '3', '$filter_merchant_list[$i]'");
                  }

              }

              if ($filter_branch_list != null) {

                  $filter_branch_list = explode(',', $filter_branch_list);

                  for($i = 0; $i < count($filter_branch_list); $i++){

                      $q_insert_filter_branch = DB::statement("[spVDWH_InsertUserFilterTypeData] '$user_id', '4', '$filter_branch_list[$i]'");
                  }

              }

              if ($filter_store_list != null) {

                  $filter_store_list = explode(',', $filter_store_list);

                  for($i = 0; $i < count($filter_store_list); $i++){

                      $q_insert_filter_store = DB::statement("[spVDWH_InsertUserFilterTypeData] '$user_id', '5', '$filter_store_list[$i]'");
                  }

              }

        if(  $q_insert_filter_acquirer || $q_insert_filter_corporate || $q_insert_filter_merchant || $q_insert_filter_branch ) {
                $res['success'] = true;
                $res['message'] = 'Insert data success !';
              } else {
                $res['success'] = false;
                $res['message'] = "Insert data failed ! ";
                DB::rollback();
                return response($res);
              }




					// foreach( $old_filter_list as $key => $value ) {
          //
					// 	if( !array_key_exists( $key, $new_filter_list ) ) {
					// 		$audit_trail_check = '1';
					// 		$activity[] = "[User_Management][User] Delete filter type, filter : ".$key.", value : ".$value;
					// 	}
          //
					// }
          //
					// foreach( $new_filter_list as $key => $value ) {
					// 	if( !array_key_exists( $key, $old_filter_list ) ) {
					// 		$audit_trail_check = '1';
					// 			$activity[] = "[User_Management][User] Add filter type, filter : ".$key.", value : ".$value;
					// 	} else {
					// 		if( $value != $old_filter_list[$key] ) {
					// 			$audit_trail_check = '1';
					// 			$activity[] = "[User_Management][User] Edit filter type, filter : ".$key.", old value : ".$value;
					// 		}
					// 	}
					// }
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
					$res['message'] = "Update data failed on update user data ! ";
					DB::rollback();
					return response($res);
				}

			} else {
				$res['success'] = false;
				$res['message'] = 'Update user data failed !';
				DB::rollback();
				return response($res);
			}

		} catch(Exception $ex){
			$res['success'] = false;
		   $res['message'] = 'Update Failed. Please Check All Your Data and Filter Type';
      //$res['message'] = $ex->getMessage();
      DB::rollback();
			return response($res);
		}

		return json_encode($result);
    }

	public function deleteUserData(Request $request) {

		DB::beginTransaction();

		try{

			$user_id = $request->input('user_id_');

			$delete = DB::statement("[spVDWH_DeleteUserFilterTypeData] '$user_id'");
			if( $delete ) {
				$data = DB::statement("[spVDWH_DeleteUserData] '$user_id'");

				if($data) {

					$res['success'] = true;
					$res['message'] = 'Delete data success !';
					DB::commit();
					return response($res);

				} else {

					$res['success'] = false;
					$res['message'] = "Delete user data failed !";
					DB::rollback();
					return response($res);

				}
			} else {
				$res['success'] = false;
				$res['message'] = "Delete user filter type failed !";
				DB::rollback();
				return response($res);
			}

		} catch(Exception $ex){
			$res['success'] = false;
			$res['result'] = 'Query Exception.. Please Check Database!';
			DB::rollback();
			return response($res);
		}

		return json_encode($result);
    }
}
