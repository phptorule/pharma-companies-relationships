<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/test', function (){
    return response()->json(['test-data'=>'all is ok!']);
});

Route::post('login', 'AuthController@login');
//Route::post('register', 'AuthController@register');
Route::post('recover', 'AuthController@recover');

Route::post('password/reset/{token}', 'Auth\ResetPasswordController@postReset')->name('password.resetPost');

Route::get('app-version', 'Controller@getAppVersion');

Route::group(['middleware'=>['jwt.auth']],function () {

    Route::get('/auth-test', function (){
        return response()->json(['test-data'=>'all is ok!']);
    });

    Route::get('/logged-user', 'UserController@showLoggedUserData')->name('user.showLoggedUserData');

    Route::post('/user/update-profile-picture', 'UserController@updateProfilePicture');

    Route::post('/user/remove-avatar', 'UserController@removeAvatar');

    Route::put('/user/update-profile-settings', 'UserController@updateProfileSettings');

    Route::post('/user/change-password', 'UserController@changePassword');

    Route::post('/user/logout', 'AuthController@logout');

    Route::get('/addresses', 'AddressesController@index')->name('address.index');

    Route::get('/addresses-paginated', 'AddressesController@loadAddressesPaginated')->name('address.loadAddressesPaginated');

    Route::get('/addresses-load-filters', 'AddressesController@loadFilterValues')->name('address.loadFilterValues');

    Route::get('/address-details/{address}', 'AddressesController@show')->name('address.show');

    Route::get('/address-details/{address}/people', 'AddressesController@loadPeopleByAddressId')->name('address.show');

    Route::put('/address-details/{address}/update-status', 'AddressesController@updateCustomerStatus')->name('address.updateCustomerStatus');

    Route::get('/address-details/{address}/load-contacts-chain-data', 'AddressesController@getContactsChain')->name('address.getContactsChain');

    Route::get('/address-details/{address}/get-cluster-members-paginated', 'AddressesController@getClusterMembersPaginated')->name('address.getClusterMembersPaginated');

    Route::get('/address-details/{address}/get-cluster-staff-paginated', 'AddressesController@getClusterStaffPaginated')->name('address.getClusterStaffPaginated');

    Route::get('/address-details/{address}/get-cluster-products-paginated', 'AddressesController@getClusterProductsPaginated')->name('address.getClusterProductsPaginated');
    
    Route::get('/address-details/{address}/get-all-cluster-staff', 'AddressesController@getAllClusterStaff');
    
    Route::put('/address-details/{address}/update-details', 'AddressesController@updateAddressDetails')->name('address.updateAddressDetails');

    Route::get('/address-details/{address}/get-all-tags', 'AddressesController@loadAllTags')->name('address.loadAllTags');

    Route::get('/address-details/{address}/load-selected-tags', 'AddressesController@loadSelectedTags')->name('address.loadSelectedTags');
    
    Route::put('/address-details/{address}/update-cluster-name', 'AddressesController@updateClusterName');

    Route::get('/customer-statuses', 'CustomerStatusesController@show')->name('customerStatus.show');

    Route::get('/people/{person}/relationships', 'PeopleController@getPersonRelationships')->name('people.getPersonRelationships');

    Route::get('/people/{person}/relationship-details', 'PeopleController@getRelationshipDetails')->name('people.getRelationshipDetails');

    Route::get('/people/{person}', 'PeopleController@show')->name('people.show');

    Route::get('/get-roles', 'PeopleController@getRoles');

    Route::put('/people/{person}/update', 'PeopleController@updateEmploye')->name('people.updateEmploye');

    Route::get('/connection-types', 'PeopleController@getConnectionTypes')->name('people.getConnectionTypes');

    Route::get('/people/{mainPersonId}/get-person-graph-data', 'PeopleController@getPersonGraphInfo')->name('people.getPersonGraphInfo');

    Route::get('/clusters', 'AddressesController@getClusters')->name('address.getClusters');

    Route::put('/clusters/{address}', 'AddressesController@updateClusters')->name('address.updateClusters');

    Route::get('/products', 'AddressesController@getProducts')->name('address.getProducts');

    Route::post('/products/{address}/update', 'AddressesController@updateProducts')->name('address.updateProducts');

    Route::post('/products/create', 'AddressesController@createProduct');

    Route::post('/clusters/create/{address}', 'AddressesController@createCluster');

    Route::get('/people/autocomplete/{searchQuery}', 'PeopleController@getPeopleAutocomplete');
});
