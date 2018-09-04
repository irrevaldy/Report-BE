<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class corporateMerchantController extends Controller
{
  public function addCorporate(Request $request)
  {
    try
    {
      $corporateName = $request->corporateName;
      $file = $request->file;
      //$date = $request->date;

      $data = DB::statement("[spVDWH_InsertNewCorporate] '$corporateName', '$file' ");

      $res['success'] = true;
      $res['result'] = "Add Corporate Success";

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Add Corporate Failed';

      return response($res);
    }
  }

  public function updateCorporate(Request $request)
  {
    try
    {
      $corporateId = $request->corporateId;
      $corporateName = $request->corporateName;
      $file = $request->file;
      //$date = $request->date;

      $data = DB::statement("[spVDWH_UpdateNewCorporate] '$corporateId', '$corporateName', '$file' ");

      $res['success'] = true;
      $res['result'] = "Update Corporate Success";

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Update Corporate Failed';

      return response($res);
    }
  }

  public function deleteCorporate(Request $request)
  {
    try
    {
      $corporateIdDel = $request->corporateIdDel;

      $data = DB::statement("[spVDWH_DeleteNewCorporate] '$corporateIdDel' ");

      $res['success'] = true;
      $res['result'] = "Delete Corporate Success";

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Delete Corporate Failed';

      return response($res);
    }
  }

  public function addMerchant(Request $request)
  {
    try
    {
      $corporateId = $request->corporateId;
      $merchName = $request->merchName;
      $file = $request->file;
      //$date = $request->date;

      $data = DB::statement("[spVDWH_InsertNewMerchant] '$corporateId', '$merchName','$file' ");

      $res['success'] = true;
      $res['result'] = "Add Merchant Success";

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Add Merchant Failed';

      return response($res);
    }
  }

  public function updateMerchant(Request $request)
  {
    try
    {
      $merchId = $request->merchId;
      $corporateId = $request->corporateId;
      $merchName = $request->merchName;
      $file = $request->file;
      //$date = $request->date;

      $data = DB::statement("[spVDWH_UpdateMerchant] '$merchId', '$corporateId', '$merchName', '$file' ");

      $res['success'] = true;
      $res['result'] = "Update Merchant Success";

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Update Merchant Failed';

      return response($res);
    }
  }

  public function deleteMerchant(Request $request)
  {
    try
    {
      $merchantIdDel = $request->merchIdDel;

      $data = DB::statement("[spVDWH_DeleteMerchant] '$merchantIdDel' ");

      $res['success'] = true;
      $res['result'] = "Delete Merchant Success";

      return response($res);
    }
    catch(QueryException $ex)
    {
      $res['success'] = false;
      $res['result'] = 'Delete Merchant Failed';

      return response($res);
    }
  }

}
