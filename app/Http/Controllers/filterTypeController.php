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

class filterTypeController extends Controller
{
   	public function __construct(Request $request){
        
    }
	
    public function getFilterTypeData(Request $request, $id_filter_type) {
		
		try{
			$data = DB::select("[spVDWH_getFilterTypeData] '$id_filter_type'");
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
	
	public function insertFilterTypeData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			
			$filterTypeName = $request->input('filterTypeName_');
			
			$register = DB::statement("[spVDWH_InsertFilterTypeData] '$filterTypeName'");
			
			if($register){
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
			
		} catch(QueryException $ex){ 
			$res['success'] = false;
			$res['message'] = 'Query Exception.. Please Check Database!';
			DB::rollback();
	
			return response($res);
		}
		
		return json_encode($result);
    }
	
	public function updateFilterTypeData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			
			$filter_type_id 	= $request->input('filter_type_id_');
			$filterTypeName 	= $request->input('filterTypeName_');
			
			$data = DB::statement("[spVDWH_UpdateFilterTypeData] '$filter_type_id', '$filterTypeName'");
			
			if( $data ) {
				
				$res['success'] = true;
				$res['message'] = "Update data success !";
				DB::commit();
		
				return response($res);
				
			} else {
				$res['success'] = false;
				$res['message'] = "Update data failed !";
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
	
	public function deleteFilterTypeData(Request $request) {
		
		DB::beginTransaction();
		
		try{
			
			$filter_type_id = $request->input('filter_type_id_');
			
			$delete = DB::statement("[spVDWH_DeleteFilterTypeData] '$filter_type_id'");
				
			if( $delete ) {
				$res['success'] = true;
				$res['message'] = "Delete data success !";
				DB::commit();
		
				return response($res);
			} else {
				$res['success'] = false;
				$res['message'] = "Delete tran package privilege failed !";
				
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
