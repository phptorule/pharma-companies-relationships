<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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


Route::group(['middleware'=>['jwt.auth']],function (){

    Route::get('/auth-test', function (){
        return response()->json(['test-data'=>'all is ok!']);
    });

    Route::get('/logged-user', 'UserController@showLoggedUserData')->name('user.showLoggedUserData');

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

    Route::get('/customer-statuses', 'CustomerStatusesController@show')->name('customerStatus.show');

    Route::get('/people/{person}/relationships', 'PeopleController@getPersonRelationships')->name('people.getPersonRelationships');

    Route::get('/people/{person}', 'PeopleController@show')->name('people.show');

    Route::get('/connection-types', 'PeopleController@getConnectionTypes')->name('people.getConnectionTypes');

	Route::get('/tenders-by-address/{id}', 'TendersController@getTendersByAddress')->name('tenders.getTendersByAddress');

	Route::get('/product-by-purchase/{id}', 'ProductsController@productByPurchase')->name('products.productByPurchase');

	Route::get('/product-by-tenders/{id}', 'ProductsController@productByTenders')->name('products.productByTenders');

	Route::get('/product-by-tenders-paginated/{id}', 'ProductsController@getProductByTendersPaginated')->name('products.getProductByTendersPaginated');

	Route::get('/product-by-tenders-to-excel/{id}', 'ProductsController@getProductByTendersToExcel')->name('products.getProductByTendersToExcel');

	Route::get('/product-load-tags', 'ProductsController@loadTagsValues')->name('products.loadTagsValues');

	Route::get('/product-by-address/{address}', 'ProductsController@productByAddressPaginated')->name('products.productByAddressPaginated');

	Route::get('/tenders-by-product-chart/{id}', 'ProductsController@tenderByProductChart')->name('products.tenderByProductChart');

	Route::get('/load-top-products', 'ProductsController@loadTopProducts')->name('products.loadTopProducts');

});
