<?php

use App\Http\Controllers\Auth\AuthController;


use App\Http\Controllers\Admin\CartController;

use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Admin\BillDetailController;

use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SizeController;

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ClientKeyController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StatisticController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\TablesController;
use App\Http\Controllers\Admin\TimeOrderTableController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Client\BillOrderController;
use App\Http\Controllers\Client\BillUser;
use App\Http\Controllers\Client\VoucherController as ClientVoucherController;

use App\Http\Controllers\Client\CategoryController as ClientCategoryController;
use App\Http\Controllers\Client\OnlineCartController;
use App\Http\Controllers\Client\OrderCartController;
use App\Http\Controllers\Client\ProductClientController;
use App\Http\Controllers\Client\TableController;
use App\Http\Controllers\Client\TimeOrderTableController as ClientTimeOrderTableController;
use App\Http\Controllers\Client\UpdateProfileController;
use App\Http\Controllers\Client\PaymentController as ClientPaymentController;
use App\Http\Controllers\Shipper\ShipperController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
GET /roles - index
GET /roles/{id} - show
POST /roles - store
PUT/PATCH /roles/{id} - update
DELETE /roles/{id} - destroy

*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// user
Route::post('register', [AuthController::class, 'register'])->name('api.register');
Route::post('login', [AuthController::class, 'login'])->name('api.login');
Route::get('user', [AuthController::class, 'getUser'])->middleware('auth');
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth');


Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword']);


Route::post('refresh', [AuthController::class, 'refreshToken']);
Route::post('pay_check', [TransactionController::class, 'webhook']);



Route::prefix('shipper')->middleware(['auth', 'checkRole:qtv,admin,shipper'])->group(function () {
    Route::get('bill', [ShipperController::class, 'listBill'])->middleware('auth', 'checkRole:shipper,admin,qtv');
    Route::put('updateShippingStatus/{id}', [ShipperController::class, 'updateShippingStatus']);
    Route::put('retryShipping/{id}', [ShipperController::class, 'retryShipping']);
});


//power
Route::prefix('admin')->middleware(['auth', 'checkRole:qtv,admin,ctv'])->group(function () {

    //dash
    Route::get('dashboard', [DashboardController::class, 'index'])->middleware('checkRole:admin');
    Route::get('dashboardfull', [StatisticController::class, 'index'])->middleware('checkRole:admin');


    // users
    Route::apiResource('users', UserController::class)->middleware('checkRole:qtv,admin,user,ctv');

    Route::apiResource('roles', RoleController::class)->middleware('checkRole:qtv,admin');
    Route::get('/user/{user}/roles', [UserController::class, 'getUserRoles'])->middleware('checkRole:qtv,admin,user');
    Route::put('/user/{user}/roles', [UserController::class, 'updateUserRoles'])->middleware('checkRole:admin');
    Route::put('/user/{user}/locked', [UserController::class, 'is_locked'])->middleware('checkRole:qtv,admin');



    // //cate
    // Route::apiResource('category', AdminCategoryController::class)->middleware('auth', 'checkRole:qtv,admin');
    // Route::apiResource('sub_category', SubcategoryController::class)->middleware('auth', 'checkRole:qtv,admin');

    // customer
    Route::apiResource('customers', CustomerController::class)->middleware('checkRole:qtv,admin,customer');
    // voucher
    Route::apiResource('vouchers', VoucherController::class)->middleware('checkRole:qtv,admin,voucher');

    // sizes
    Route::apiResource('sizes', SizeController::class)->middleware('checkRole:qtv,admin,size');
    Route::put('size_status/{id}', [SizeController::class, 'statusSize'])->middleware('checkRole:qtv,admin,size');

    // payments
    Route::apiResource('payments', PaymentController::class)->middleware('checkRole:qtv,admin,payment');


    //cart
    Route::apiResource('carts', CartController::class)->middleware('checkRole:qtv,admin,ctv');


    Route::apiResource('products', ProductController::class)->middleware('checkRole:qtv,admin,product');
    Route::put('product/{id}/status', [ProductController::class, 'updateStatus'])->middleware('checkRole:qtv,admin,product');
    Route::post('products/{id}', [ProductController::class, 'update'])->middleware('checkRole:qtv,admin,product');

    // Bills
    Route::apiResource('bills', BillController::class)->middleware('checkRole:qtv,admin,bill');
    Route::get('/bill_table/{table_number}', [BillController::class, 'getBillByTableNumber'])->middleware('checkRole:qtv,admin,bill');
    Route::put('/acive_item', [BillController::class, 'activeItems'])->middleware('checkRole:qtv,admin,bill');
    Route::get('shipping/{id}', [BillController::class, 'showShippingHistory'])->middleware('checkRole:qtv,admin,bill');


    //Bill detail
    Route::apiResource('billsDetail', BillDetailController::class)->middleware('checkRole:qtv,admin,bill');
    Route::put('/addtablefrombill', [BillController::class, 'addTableGroupToBill'])->middleware('checkRole:qtv,admin,bill');
    Route::put('/remotablefrombill', [BillController::class, 'removedTableFromBill'])->middleware('checkRole:qtv,admin,bill');


    // tables
    Route::apiResource('tables', TablesController::class)->middleware('checkRole:qtv,admin,table');
    Route::put('table/{id}/status', [TablesController::class, 'updateStatus'])->middleware('checkRole:qtv,admin,table');


    // timeOrderTable
    Route::apiResource('time_order_table', TimeOrderTableController::class)->middleware('checkRole:qtv,admin');


    // category admin
    Route::apiResource('category', AdminCategoryController::class)->middleware('checkRole:qtv,admin,categories');

    // update status category
    Route::post('category/update/{id}/status', [AdminCategoryController::class, 'updateStatus'])->middleware('checkRole:qtv,admin,categories');

    // all list categories
    Route::get('list/category', [AdminCategoryController::class, 'listCategories'])->middleware('checkRole:qtv,admin,categories');

    // api key client
    Route::get('api_key', [ClientKeyController::class, 'index'])->middleware('checkRole:qtv,admin,ctv');
    Route::post('api_key', [ClientKeyController::class, 'store'])->middleware('checkRole:qtv,admin,ctv');
    Route::put('api_key_status/{id}', [ClientKeyController::class, 'statusKey'])->middleware('checkRole:qtv,admin,ctv');

    Route::get('gettransaction', [TransactionController::class, 'index'])->middleware('checkRole:admin');
});

// ->middleware('check.api.key')
Route::prefix('client')->group(function () {


    // oder qua web 
    Route::apiResource('online_cart', OnlineCartController::class)->middleware('auth');

    Route::apiResource('category', ClientCategoryController::class);


    // Đổi voucher cho customer
    Route::post('/change_voucher', [ClientVoucherController::class, 'changeVoucher'])->middleware('auth');
    // vouchers của customer
    Route::get('/vouchers_customer', [ClientVoucherController::class, 'vouchersCustomer'])->middleware('auth');
    Route::get('/vouchers_yagi', [ClientVoucherController::class, 'voucherYagi'])->middleware('auth');

    // table

    Route::prefix('order_table')->middleware('auth')->group(function () {
        Route::get('/', [ClientTimeOrderTableController::class, 'index']);
        Route::get('/{idTable}', [ClientTimeOrderTableController::class, 'show']);
        Route::post('/', [ClientTimeOrderTableController::class, 'store']);
        Route::put('/{id}', [ClientTimeOrderTableController::class, 'update']);
        Route::delete('/{id}', [ClientTimeOrderTableController::class, 'destroy']);
    });

    // cart oder trực tiếp
    Route::get('order_cart/{ma_bill}', [OrderCartController::class, 'show']);
    Route::post('order_cart/', [OrderCartController::class, 'store']);
    Route::put('order_cart', [OrderCartController::class, 'update']);
    Route::delete('order_cart/{id}', [OrderCartController::class, 'destroy']);



    Route::prefix('profile')->middleware('auth')->group(function () {
        Route::get('/', [UpdateProfileController::class, 'index']);
        Route::put('/{id}', [UpdateProfileController::class, 'update']);
    });


    // product 
    Route::get('products', [ProductClientController::class, 'getProduct']); // fe lấy cái này theo product k lấy detail 
    Route::get('products_details', [ProductClientController::class, 'getProductAllWithDetail']); // fe lấy cái này all cả detail 
    Route::get('product/{id}', [ProductClientController::class, 'getProductWithDetailByID']); // api get theo id product nhé fe
    Route::get('product_cate/{id}', [ProductClientController::class, 'getProductByCate']); // api get product theo id cate nhé fe


    Route::get('list_tables', [TableController::class, 'getAllTables']);
    Route::post('open_table', [TableController::class, 'openTable'])->middleware('auth', 'checkRole:qtv,admin,ctv');
    Route::post('open_tables', [TableController::class, 'openTables'])->middleware('auth', 'checkRole:qtv,admin,ctv');


    Route::get('list_payments', [ClientPaymentController::class, 'listPaymentTrue'])->middleware('auth');


    Route::prefix('profile')->middleware('auth')->group(function () {
        Route::get('/', [UpdateProfileController::class, 'index']);
        Route::post('/store_address', [UpdateProfileController::class, 'storeAddress']);
        Route::put('/{idUser}', [UpdateProfileController::class, 'update']);
        Route::put('/update_address/{idAddress}', [UpdateProfileController::class, 'updateAddress']);
        Route::put('/addresses/{id}/default', [UpdateProfileController::class, 'updateDefaultAddress']);
        Route::delete('/destroy_address/{idAddress}', [UpdateProfileController::class, 'destroyAddress']);
    });

    //bill client
    Route::get('bill_user', [BillUser::class, 'billUser'])->middleware('auth');
    Route::post('bill_store', [BillUser::class, 'store'])->middleware('auth');
    Route::put('bills/{id}/cancel', [BillUser::class, 'requestCancelBill'])->middleware('auth');
    Route::get('billdetail/{id}', [BillUser::class, 'showBillDetail'])->middleware('auth');
    Route::get('shipping/{id}', [BillUser::class, 'showShippingHistory'])->middleware('auth');


    // order + bill order
    Route::post('oder_item', [BillOrderController::class, 'addItem']);
    Route::post('oder_item/cancelItem', [BillOrderController::class, 'cancelItem']);

    Route::post('bill_online', [BillOrderController::class, 'getBillOnline'])->middleware('auth');
    Route::put('pay_bill', [BillOrderController::class, 'saveBill'])->middleware('auth');
});
