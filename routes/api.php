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

    Route::get('/people-paginated', 'PeopleController@getPeoplePaginated')->name('people.getPeoplePaginated');

    Route::get('/people/{person}/relationships', 'PeopleController@getPersonRelationships')->name('people.getPersonRelationships');

    Route::get('/people/{person}/relationship-details', 'PeopleController@getRelationshipDetails')->name('people.getRelationshipDetails');

    Route::get('/people/{person}', 'PeopleController@show')->name('people.show');

    Route::get('/get-roles', 'PeopleController@getRoles');

    Route::put('/people/{person}/update', 'PeopleController@updateEmploye')->name('people.updateEmploye');

    Route::get('/connection-types', 'PeopleController@getConnectionTypes')->name('people.getConnectionTypes');

	Route::get('/tenders-by-address/{id}', 'TendersController@getTendersByAddress')->name('tenders.getTendersByAddress');

	Route::get('/tenders-by-product-and-address-paginated/{product}/{address}', 'TendersController@getTendersByProductAndAdderssPaginated')->name('tenders.getTendersByProductAndAdderssPaginated');

	Route::get('/tenders-by-address-paginated/{address}', 'TendersController@getTendersByAdderssPaginated')->name('tenders.getTendersByAdderssPaginated');

	Route::get('/tenders-by-product-and-address-to-excel/{product}/{address}', 'TendersController@getTendersByProductAndAddressToExcel')->name('tenders.getTendersByProductAndAddressToExcel');

	Route::get('/tenders-by-address-to-excel/{address}', 'TendersController@getTendersByAddressToExcel')->name('tenders.getTendersByAddressToExcel');

	Route::get('/product-by-id/{product}', 'ProductsController@productById')->name('products.productById');

	Route::get('/product-by-tenders/{id}/{address}', 'ProductsController@productByTenders')->name('products.productByTenders');

	Route::get('/product-load-tags', 'ProductsController@loadTagsValues')->name('products.loadTagsValues');

	Route::get('/product-by-address/{address}', 'ProductsController@productByAddressPaginated')->name('products.productByAddressPaginated');

	Route::get('/tenders-by-product-chart/{id}/{address}', 'ProductsController@tenderByProductChart')->name('products.tenderByProductChart');

    Route::get('/people/{mainPersonId}/get-person-graph-data', 'PeopleController@getPersonGraphInfo')->name('people.getPersonGraphInfo');

    Route::get('/clusters', 'AddressesController@getClusters')->name('address.getClusters');

    Route::put('/clusters/{address}', 'AddressesController@updateClusters')->name('address.updateClusters');

    Route::get('/products', 'AddressesController@getProducts')->name('address.getProducts');

    Route::post('/products/{address}/update', 'AddressesController@updateProducts')->name('address.updateProducts');

    Route::post('/products/create', 'AddressesController@createProduct');

    Route::post('/clusters/create/{address}', 'AddressesController@createCluster');

    Route::get('/people/autocomplete/{searchQuery}/{fromPersonId}', 'PeopleController@getPeopleAutocomplete');

    Route::post('/logger/log-data', 'LogController@logData');

    Route::post('/address-details/create-person-relation', 'AddressesController@createPersonRelation');

    Route::post('/address-details/delete-person-relation', 'AddressesController@deletePersonRelation');

    Route::get('/addresses/pre-process-global-search', 'AddressesController@preProcessGlobalSearch');

    Route::get('/people-for-map', 'PeopleController@getDataForMap');

    Route::get('/product-consumables-sum/{addressId}/{productId}', 'ProductsController@getProductConsumableSum');

    Route::get('/tenders/{tender}', 'TendersController@show');

    Route::get('/get-product-list-by-address/{address}', 'ProductsController@getProductListForAddress');

    Route::get('/tenders/{address}/products-graph', 'TendersController@getGraphDataForProductsByTendersAndAddress');

    Route::get('/get-used-consumables-by-address-and-product', 'ProductsController@getUsedConsumablesByAddressAndProduct');

    Route::get('/get-used-consumables-by-address/{addressId}', 'TendersController@getUsedConsumablesByAddressAndTender');

    Route::post('/assign-addresses-to-person/{person}', 'PeopleController@assignAddressesToPerson');

    Route::get('/count-global-search-results', 'GlobalSearchController@searchForAutoSuggesting');
});