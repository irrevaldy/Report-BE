<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//menu
// Route::get('/menu/main/{groupid}/{token}',['uses' => 'MenuController@getMenuMain']);
// Route::get('/menu/regular/{groupid}/{token}',['uses' => 'MenuController@getMenuRegular']);

//menu
Route::get('/menu/main/{group_id}/{user_id}/{api_token}',['uses' => 'MenuController@getMenuMain']);
Route::get('/menu/regular/{group_id}/{user_id}/{api_token}',['uses' => 'MenuController@getMenuRegular']);


/* Login & logout */
Route::post('/login', ['uses' => 'loginController@login']);
//Route::post('/login', ['uses' => 'login\loginController@login']);
//Route::get('/login', ['uses' => 'login\loginController@login']);
Route::post('/logout/{username}', ['uses' => 'loginController@logout']);
//Route::post('/logout/{username}', ['uses' => 'login\loginController@logout']);
//Route::get('/logout/{username}', ['uses' => 'login\loginController@logout']);
//getAPItoken
Route::get('/user/{username}', ['uses' => 'UserController@getUserToken']);
Route::post('/token_check', ['uses' => 'tokenController@tokenCheck']);

/* Global function */
Route::get('/host_data', ['uses' => 'globalController@getHostData']);
Route::get('/host_data1', ['uses' => 'globalController@getHostData1']);
Route::get('/corporate_data', ['uses' => 'globalController@getCorporateData']);
Route::post('/merchant_data', ['uses' => 'globalController@GetMerchantDataByCorpId']);
Route::get('/merchant_data1', ['uses' => 'globalController@GetMerchantData1']);
Route::get('/card_data', ['uses' => 'globalController@getCardData']);
Route::get('/branch_data', ['uses' => 'globalController@getBranchData']);
Route::get('/branch_data1', ['uses' => 'globalController@getBranchData1']);
Route::get('/bank_data', ['uses' => 'globalController@getBankData']);
// Route::post('/group_data', ['uses' => 'globalController@getGroupData']);
Route::post('/institute_data', ['uses' => 'globalController@getInstituteData']);
Route::post('/policy_data', ['uses' => 'globalController@getPolicyData']);

/* Search transaction */
Route::post('/search_transaction', ['uses' => 'searchTransactionController@search']);
Route::get('/search_transaction/line_data', ['uses' => 'searchTransactionController@getLineData']);
Route::get('/search_transaction/series_seconds', ['uses' => 'searchTransactionController@getSeriesSeconds']);

/* Transaction Report */
Route::post('transaction_report', ['uses' => 'transactionReportController@insertAuditTrail']);
Route::post('transaction_report_financial',['uses' => 'transactionReportFinancialController@insertAuditTrail']);

/* Summary transaction */
Route::post('/summary_transaction', ['uses' => 'summaryTransactionController@summaryTransaction']);
Route::post('/summary_response_code', ['uses' => 'summaryTransactionController@summaryResponseCode']);

Route::post('/other_report/monthly_revenue', ['uses' => 'otherReportController@monthlyRevenue']);
Route::post('/other_report/monthly_revenue_t10h', ['uses' => 'otherReportController@monthlyRevenueT10H']);
Route::post('/other_report/monthly_revenue_t10l', ['uses' => 'otherReportController@monthlyRevenueT10H']);
Route::post('/other_report/monthly_onoff', ['uses' => 'otherReportController@monthlyOnOff']);
Route::post('/other_report/monthly_errorcode', ['uses' => 'otherReportController@monthlyErrorCode']);

Route::post('/tcash/setup', ['uses' => 'tcashSetupController@setTcashLimit']);
Route::post('/tcash/checkLimit', ['uses' => 'tcashSetupController@checkLimit']);

Route::post('/edc_data/checkSN', ['uses' => 'edcDataController@checkSN']);
Route::post('/edc_data/getSN', ['uses' => 'edcDataController@getSN']);
Route::post('/edc_data/deleteSN', ['uses' => 'edcDataController@deleteSN']);
Route::post('/edc_data/upload_edc', ['uses' => 'edcDataController@uploadEdc']);
Route::post('/edc_data/activate_edc', ['uses' => 'edcDataController@activateEdc']);


Route::post('/corporate', ['uses' => 'globalController@getCorporateData']);
Route::post('/add_corporate', ['uses' =>'corporateMerchantController@addCorporate']);
Route::post('/update_corporate', ['uses' =>'corporateMerchantController@updateCorporate']);
Route::post('/delete_corporate', ['uses' =>'corporateMerchantController@deleteCorporate']);

Route::post('/merchant', ['uses' => 'globalController@getMerchantData']);
Route::post('/add_merchant', ['uses' =>'corporateMerchantController@addMerchant']);
Route::post('/update_merchant', ['uses' =>'corporateMerchantController@updateMerchant']);
Route::post('/delete_merchant', ['uses' =>'corporateMerchantController@deleteMerchant']);

Route::post('/users', ['uses' => 'globalController@getUsersData']);
Route::post('/add_users', ['uses' =>'usersGroupsController@addUsers']);
Route::post('/update_users', ['uses' =>'usersGroupsController@updateUsers']);
Route::post('/delete_users', ['uses' =>'usersGroupsController@deleteUsers']);

Route::post('/groups', ['uses' => 'globalController@getGroupsData']);
Route::post('/add_groups', ['uses' =>'usersGroupsController@addGroups']);
Route::post('/update_groups', ['uses' =>'usersGroupsController@updateGroups']);
Route::post('/delete_groups', ['uses' =>'usersGroupsController@deleteGroups']);

Route::post('/update_password', ['uses' =>'globalController@updatePassword']);

Route::post('/audit_trail', ['uses' => 'auditTrailController@getAuditTrail']);

Route::get('/monitoring_bar_chart', ['uses' => 'chartController@barChart']);

Route::get('/test', ['uses' => 'testController@index']);

Route::get('/monthly_branchtop', ['uses' => 'merchantDashboardController@getMonthlyBranchTransactionTop5']);
Route::get('/monthly_branchlow', ['uses' => 'merchantDashboardController@getMonthlyBranchTransactionLow5']);
Route::get('/monthly_storetop', ['uses' => 'merchantDashboardController@getMonthlyStoreTransactionTop5']);
Route::get('/monthly_storelow', ['uses' => 'merchantDashboardController@getMonthlyStoreTransactionLow5']);
Route::get('/merchant_get_data', ['uses' => 'merchantDashboardController@getData']);

Route::get('/bank_get_data', ['uses' => 'bankDashboardController@getdata']);

Route::get('/provider_get_data', ['uses' => 'providerDashboardController@getdata']);

Route::get('/serviceprovider_get_data', ['uses' => 'serviceproviderDashboardController@getdata']);


Route::post('/list_detail_report', ['uses' => 'otherReportController@listDetailReport']);
Route::post('/list_detail_report_filtered', ['uses' => 'otherReportController@listDetailReportFiltered']);
Route::post('/list_recon_report', ['uses' => 'otherReportController@listReconReport']);
Route::post('/list_recon_report_filtered', ['uses' => 'otherReportController@listReconReportFiltered']);

Route::post('/list_detail_report_acquirer', ['uses' => 'otherReportController@listDetailReportAcquirer']);
Route::post('/list_detail_report_filtered_acquirer', ['uses' => 'otherReportController@listDetailReportFilteredAcquirer']);
Route::post('/list_recon_report_acquirer', ['uses' => 'otherReportController@listReconReportAcquirer']);
Route::post('/list_recon_report_filtered_acquirer', ['uses' => 'otherReportController@listReconReportFilteredAcquirer']);

Route::post('/list_detail_report_branch', ['uses' => 'otherReportController@listDetailReportBranch']);
Route::post('/list_detail_report_filtered_branch', ['uses' => 'otherReportController@listDetailReportFilteredBranch']);
Route::post('/list_recon_report_branch', ['uses' => 'otherReportController@listReconReportBranch']);
Route::post('/list_recon_report_filtered_branch', ['uses' => 'otherReportController@listReconReportFilteredBranch']);


// Route::get('/provider_get_data', ['uses' => 'providerDashboardController@getdata']);

/* Change Password */
Route::post('/change_password_data', 									['uses' => 'passwordController@updatePasswordData']);

/* User Setup */
Route::get('/user_data/{id_user}', 										['uses' => 'userController@getUserData']);
Route::post('/user_data_insert', 										['uses' => 'userController@insertUserData']);
Route::post('/user_data_update', 										['uses' => 'userController@updateUserData']);
Route::post('/user_data_delete', 										['uses' => 'userController@deleteUserData']);
Route::get('/user_filter_type_data/{id_user}', 							['uses' => 'userController@getUserFilterTypeData']);
Route::get('/filter_value_option/{filter_type}', 					    ['uses' => 'userController@getFilterValueOption']);
Route::get('/user_privilege/{username}', 								['uses' => 'userController@getUserPrivilegeData']);

/* Subgroup Setup */
Route::get('/subgroup_data/group/{id_group}', 							['uses' => 'subgroupController@getSubgroupPerGroupData']);
Route::get('/subgroup_data/{id_subgroup}', 								['uses' => 'subgroupController@getSubgroupData']);
Route::post('/subgroup_data_insert', 									['uses' => 'subgroupController@insertSubgroupData']);
Route::post('/subgroup_data_update', 									['uses' => 'subgroupController@updateSubgroupData']);
Route::post('/subgroup_data_delete', 									['uses' => 'subgroupController@deleteSubgroupData']);

/* Group Setup */
Route::get('/group_data/{id_group}', 									['uses' => 'groupController@getGroupData']);
Route::post('/group_data_insert', 										['uses' => 'groupController@insertGroupData']);
Route::post('/group_data_update', 										['uses' => 'groupController@updateGroupData']);
Route::post('/group_data_delete', 										['uses' => 'groupController@deleteGroupData']);
Route::get('/group_privilege_data/{id_group}', 							['uses' => 'groupController@getGroupPrivilegeData']);
Route::get('/group_filter_type_data/{id_group}', 						['uses' => 'groupController@getGroupFilterTypeData']);

/* Package Setup */
Route::get('/package_data/{id_package}', 								['uses' => 'packageController@getPackageData']);
Route::post('/package_data_insert', 									['uses' => 'packageController@insertPackageData']);
Route::post('/package_data_update', 									['uses' => 'packageController@updatePackageData']);
Route::post('/package_data_delete', 									['uses' => 'packageController@deletePackageData']);

/* Tran Package Privilege */
Route::get('/tran_host_package_privilege_data/{id_package}', 			['uses' => 'packageController@getTranPackagePrivilegeData']);
Route::get('/tran_subgroup_privilege_data/{id_subgroup}', 				['uses' => 'subgroupController@getTranSubgroupPrivilegeData']);

/* Privilege Setup */
Route::get('/privilege_data/{id_privilege}', 							['uses' => 'privilegeController@getPrivilegeData']);
Route::post('/privilege_data_insert', 									['uses' => 'privilegeController@insertPrivilegeData']);
Route::post('/privilege_data_update', 									['uses' => 'privilegeController@updatePrivilegeData']);
Route::post('/privilege_data_delete', 									['uses' => 'privilegeController@deletePrivilegeData']);

/* Filter Type Setup */
Route::get('/filter_type_data/{id_filter_type}', 						['uses' => 'filterTypeController@getFilterTypeData']);
Route::post('/filter_type_data_insert', 								['uses' => 'filterTypeController@insertFilterTypeData']);
Route::post('/filter_type_data_update', 								['uses' => 'filterTypeController@updateFilterTypeData']);
Route::post('/filter_type_data_delete', 								['uses' => 'filterTypeController@deleteFilterTypeData']);
