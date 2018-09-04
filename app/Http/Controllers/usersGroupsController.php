<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class usersGroupsController extends Controller
{
  public function addUsers(Request $request)
  {
    try
    {
      $name = $request->name;
      $group = $request->group;
      $branch = $request->branch;
      $note = $request->note;
      $username = $request->username;
      $password = $request->password;
      $status = '1';


      $data = DB::statement("[spVDWH_InsertNewUser] '$name', '$group', '$branch', '$note', '$username', '$password'");



      $res['success'] = true;
      $res['result'] = "Add User Success";

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Add User Failed';

      return response($res);
    }
  }

  public function updateUsers(Request $request)
  {
    try
    {
      date_default_timezone_set('Asia/Jakarta');
      $now = date("Ymdhis");

      $user_id = $request->user_id;
      $name = $request->name;
      $group = $request->group;
      $branch = $request->branch;
      $note = $request->note;
      $username = $request->username;
      $newpassword = $request->password;
      $status = $request->status;
      $session_username = $request->session_username;
      $session_name = $request->session_name;
      $session_user_id = $request->session_user_id;

      $check = DB::select("[spVDWH_ViewDetailUser] '$user_id' ");

      $check = json_encode($check);
      $check = json_decode($check, true);

      $old_name = $check[0]['name'];
      $old_password = $check[0]['password'];
      $old_group_id = $check[0]['group_id'];
      $old_description = $check[0]['description'];
      $old_FID = $check[0]['FID'];
      $old_status = $check[0]['status'];
      $old_branch_code = $check[0]['branch_code'];

      $desc_at = "";
      if($name != $old_name || $note != $old_description || $newpassword != $old_password || $group != $old_group_id || $branch != $old_branch_code || $status != $old_status) {
          $desc_at = "Change";
      }

      if ($name != $old_name) {
          if($desc_at == "Change") {
              $desc_at .= " name";
          }
      }

      if ($group != $old_group_id) {
          if($desc_at == "Change") {
              $desc_at .= " group";
          } else {
          	$desc_at .= ", group";
          }
      }

      if ($branch != $old_branch_code) {
          if($desc_at == "Change") {
              $desc_at .= " branch code";
          } else {
          	$desc_at .= ", branch code";
          }
      }

      if ($newpassword != $old_password) {
          if($desc_at == "Change") {
              $desc_at .= " password";
          } else {
          	$desc_at .= ", password";
          }
      }

      if ($note != $old_description) {
          if($desc_at == "Change") {
              $desc_at .= " note";
          } else {
          	$desc_at .= ", note";
          }
      }

      if ($status != $old_status) {
          if($desc_at == "Change") {
              $desc_at .= " status";
          } else {
          	$desc_at .= ", status";
          }
      }

      if ($old_password == $newpassword) {
      $newpassword == $old_password;
      }

      $update = DB::statement("[spVDWH_updateUser] '$user_id', '$username', '$newpassword', '$name', '$note', '$group', '$branch', '$status'");

      $res['success'] = true;
      $res['result'] = "Update User Success";

      $audit_trail = DB::statement("[spVDWH_InsertAuditTrail] '18', '$session_user_id', '$session_username', '$session_name', $now, 'Update user for username : $username. $desc_at'");

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Update User Failed';

      return response($res);
    }
  }

  public function deleteUsers(Request $request)
  {
    try
    {
      date_default_timezone_set('Asia/Jakarta');
      $now = date("Ymdhis");

      $delete_user_id = $request->delete_user_id;
      $delete_name = $request->delete_name;
      $session_username = $request->session_username;
      $session_name = $request->session_name;
      $session_user_id = $request->session_user_id;

      $data = DB::statement("[spVDWH_DeleteUser] '$delete_user_id' ");

      $res['success'] = true;
      $res['result'] = "Delete User Success";

      $audit_trail = DB::statement("[spVDWH_InsertAuditTrail] '19', '$session_user_id', '$session_username', '$session_name', $now, 'Delete user, $delete_name'");


      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Delete User Failed';

      return response($res);
    }
  }

  public function addGroups(Request $request)
  {
    try
    {
      date_default_timezone_set('Asia/Jakarta');
      $now = date("Ymdhis");

      $name = $request->name;
      $note = $request->note;
      $institute = $request->institute;
      $merchant = $request->merchant;
      $priv = $request->priv;
      $session_username = $request->session_username;
      $session_name = $request->session_name;
      $session_user_id = $request->session_user_id;
      //$date = $request->date;

      $data = DB::statement("[spVDWH_InsertNewGroups] '$name', '$note', '$institute', '$merchant','1'");

      $group = DB::select("[spVDWH_SelectGroupId] '$name'");

      $group = json_encode($group);
      $group = json_decode($group, true);

      $groupID = $group[0]['group_id'];
      foreach($priv as $value)
      {
        $insert_policy = DB::statement("[spVDWH_InsertPrivilege] '$value','$groupID'");
      }

      $res['success'] = true;
      $res['result'] = "Add Group Success";

      $audit_trail = DB::statement("[spVDWH_InsertAuditTrail] '14', '$session_user_id', '$session_username', '$session_name', $now, 'Add group $name for $institute and $merchant'");

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Add Group Failed';

      return response($res);
    }
  }

  public function updateGroups(Request $request)
  {
    try
    {
      date_default_timezone_set('Asia/Jakarta');
      $now = date("Ymdhis");

      $group_id = $request->group_id;
      $name = $request->name;
      $institute = $request->institute;
      $merchant = $request->merchant;
      $note = $request->note;
      $status = $request->status;
      $priv = $request->priv;
      $session_username = $request->session_username;
      $session_user_id = $request->sesssion_user_id;
      $session_name = $request->session_name;

      $old_priv = array();
      $rs_priv = DB::select("[spVDWH_ViewPolicyByGroupID] '$group_id' ");

      $rs_priv = json_encode($rs_priv);
      $rs_priv = json_decode($rs_priv, true);

      $total = count($rs_priv);

      for($i = 0; $i < $total; $i++)
      {
        $tp = $rs_priv[$i]['policy_id'];

        $old_priv[] = $tp;
      }

      $changePriv = "n";

      if(count($priv) != count($old_priv)) {
          $changePriv = "y";
      } else {
          foreach ($priv as $value){
              if(!in_array($value, $old_priv)) {

                 $changePriv = "y";

                  break;
              }
          }
      }

      $check = DB::select("[spVDWH_ViewDetailGroup] '$group_id' ");

      $check = json_encode($check);
      $check = json_decode($check, true);

      $old_groupName = $check[0]['groupName'];
      $old_description = $check[0]['description'];
      $old_FID = $check[0]['FID'];
      $old_status = $check[0]['status'];
      $old_merchant = $check[0]['merch_id'];

      $desc_at = "";
      if($name != $old_groupName || $note != $old_description || $status != $old_status || $institute != $old_FID || $merchant != $old_merchant || $changePriv != "n") {
          $desc_at = "Change";
      }

      if ($name != $old_groupName) {
          if($desc_at == "Change") {
              $desc_at .= " group name";
          }
      }

      if ($note != $old_description) {
          if($desc_at == "Change") {
              $desc_at .= " note";
          } else {
              $desc_at .= ", note";
          }
      }

      if ($institute != $old_FID) {
          if($desc_at == "Change") {
              $desc_at .= " host";
          } else {
              $desc_at .= ", host";
          }
      }

      if ($merchant != $old_merchant) {
          if($desc_at == "Change") {
              $desc_at .= " merchant";
          } else {
              $desc_at .= ", old_merchant";
          }
      }

      if ($status != $old_status) {
          if($desc_at == "Change") {
              $desc_at .= " status";
          } else {
              $desc_at .= ", status";
          }
      }

      if($changePriv == "y") {
          if($desc_at == "Change") {
              $desc_at .= " privileges";
          } else {
              $desc_at .= ", privileges";
          }
      }

      $data = DB::statement("[spVDWH_UpdateGroup] '$group_id', '$name', '$institute', '$merchant', '$note', '$status' ");

      try
      {
        if($data)
        {
          $q_del_policy = DB::statement("[spVDWH_DeletePolicy] '$group_id'");

          if($q_del_policy)
          {
            foreach($priv as $value)
            {
              $q_ins_policy = DB::statement("[spVDWH_InsertPolicy] '$value', '$group_id'");
              if($q_ins_policy) {}
              else
              {
                throw new Exception("Insert TGROUP_POLICY failed.");
                $messages = "Insert Group Policy Failed";
                echo "Insert TGROUP_POLICY failed.";
                break;
              }
            }
          }
          else
          {
            throw new Exception("Insert TGROUP_POLICY failed.");
            $messages = "Insert Group Policy Failed";
            echo "Delete TGROUP_POLICY failed.";
            break;
          }
        }
        $messages ="berhasil";
      }
      catch (QueryException $ex) {
        $messages = "Update TGROUP failed.";
        echo "Update TGROUP failed.";
      }

      if($messages == 'berhasil')
      {
        $res['success'] = true;
        $res['result'] = 'Update Group Success';

        $audit_trail = DB::statement("[spVDWH_InsertAuditTrail] '15', '$session_user_id', '$session_username', '$session_name', $now, 'Update group $name for $institute. $desc_at'");
      }

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Update Group Failed';

      return response($res);
    }
  }

  public function deleteGroups(Request $request)
  {
    try
    {
      date_default_timezone_set('Asia/Jakarta');
      $now = date("Ymdhis");

      $delete_group_id = $request->delete_group_id;
      $delete_groupName = $request->delete_groupName;
      $delete_group_host = $request->delete_group_host;

      $session_username = $request->session_username;
      $session_user_id = $request->sesssion_user_id;
      $session_name = $request->session_name;

      $data = DB::statement("[spVDWH_DeleteGroup] '$delete_group_id' ");

      $res['success'] = true;
      $res['result'] = "Delete Group Success";

      $audit_trail = DB::statement("[spVDWH_InsertAuditTrail] '16', '$session_user_id', '$session_username', '$session_name', $now, 'Delete group $delete_groupName for $delete_group_host'");

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Delete Group Failed';

      return response($res);
    }
  }
}
