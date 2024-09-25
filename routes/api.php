<?php

use App\Http\Controllers\Admin\AuthController;


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
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\TablesController;
use App\Http\Controllers\Admin\TimeOrderTableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Client\VoucherController as ClientVoucherController;

use App\Http\Controllers\Client\CategoryController as ClientCategoryController;
use App\Http\Controllers\Client\OnlineCartController;
use App\Http\Controllers\Client\ProductClientController;
use App\Http\Controllers\Client\TimeOrderTableController as ClientTimeOrderTableController;
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

Route::post('refresh', [AuthController::class, 'refreshToken']);


//power
Route::prefix('admin')->middleware(['auth', 'checkRole:qtv,admin'])->group(function () {

    //dash
    Route::get('dashboard', [DashboardController::class, 'index']);


    // users
    Route::apiResource('users', UserController::class)->middleware('auth', 'checkRole:qtv,admin');

    Route::apiResource('roles', RoleController::class)->middleware('auth', 'checkRole:qtv,admin');
    Route::get('/user/{user}/roles', [UserController::class, 'getUserRoles']);
    Route::put('/user/{user}/roles', [UserController::class, 'updateUserRoles'])->middleware('checkRole:admin');
    Route::put('/user/{user}/locked', [UserController::class, 'is_locked'])->middleware('checkRole:admin');



    // //cate
    // Route::apiResource('category', AdminCategoryController::class)->middleware('auth', 'checkRole:qtv,admin');
    // Route::apiResource('sub_category', SubcategoryController::class)->middleware('auth', 'checkRole:qtv,admin');

    // customer
    Route::apiResource('customers', CustomerController::class)->middleware('auth', 'checkRole:qtv,admin');
    // voucher
    Route::apiResource('vouchers', VoucherController::class)->middleware('auth', 'checkRole:qtv,admin');

    // sizes
    Route::apiResource('sizes', SizeController::class)->middleware('auth', 'checkRole:qtv,admin');
    Route::put('size_status/{id}', [SizeController::class, 'statusSize'])->middleware('auth', 'checkRole:qtv,admin');

    // payments
    Route::apiResource('payments', PaymentController::class)->middleware('auth', 'checkRole:qtv,admin');


    //cart
    Route::apiResource('carts', CartController::class)->middleware('auth', 'checkRole:qtv,admin');


    Route::apiResource('products', ProductController::class)->middleware('auth', 'checkRole:qtv,admin');
    Route::put('product/{id}/status', [ProductController::class, 'updateStatus'] )->middleware('auth', 'checkRole:qtv,admin');
    // Route::post('products/{id}', [ProductController::class, 'update']);

    // Bills
    Route::apiResource('bills', BillController::class)->middleware('auth', 'checkRole:qtv,admin');
    //Bill detail
    Route::apiResource('billsDetail', BillDetailController::class)->middleware('auth', 'checkRole:qtv,admin');

    // tables
    Route::apiResource('tables', TablesController::class)->middleware('auth', 'checkRole:qtv,admin');

    // timeOrderTable
    Route::apiResource('time_order_table', TimeOrderTableController::class)->middleware('auth', 'checkRole:qtv,admin');


    // category admin
    Route::apiResource('category', AdminCategoryController::class)->middleware('auth', 'checkRole:qtv,admin');

    // update status category
    Route::post('category/update/{id}/status', [AdminCategoryController::class, 'updateStatus'])->middleware('auth', 'checkRole:qtv,admin');

    // all list categories
    Route::get('list/category', [AdminCategoryController::class, 'listCategories'])->middleware('auth', 'checkRole:qtv,admin');

    // api key client
    Route::get('api_key', [ClientKeyController::class, 'index'])->middleware('auth', 'checkRole:qtv,admin');
    Route::post('api_key', [ClientKeyController::class, 'store'])->middleware('auth', 'checkRole:admin');
    Route::put('api_key_status/{id}', [ClientKeyController::class, 'statusKey'])->middleware('auth', 'checkRole:qtv,admin');

});


Route::prefix('client')->middleware('check.api.key')->group(function () {

    Route::apiResource('online_cart', OnlineCartController::class)->middleware('auth');

    Route::apiResource('category', ClientCategoryController::class);


    // Đổi voucher cho customer
    Route::post('/change_voucher', [ClientVoucherController::class, 'changeVoucher']);
    // vouchers của customer
    Route::get('/vouchers_customer', [ClientVoucherController::class, 'vouchersCustomer']);


    Route::prefix('order_table')->middleware('auth')->group(function () {
        Route::get('/', [ClientTimeOrderTableController::class, 'index']);
        Route::get('/{idTable}', [ClientTimeOrderTableController::class, 'show']);
        Route::post('/', [ClientTimeOrderTableController::class, 'store']);
        Route::put('/{id}', [ClientTimeOrderTableController::class, 'update']);
        Route::delete('/{id}', [ClientTimeOrderTableController::class, 'destroy']);
    });

    Route::get('products', [ProductClientController::class, 'getProduct']); // fe lấy cái này theo product k lấy detail 
    Route::get('products_details', [ProductClientController::class, 'getProductAllWithDetail']);// fe lấy cái này all cả detail 
    Route::get('product/{id}', [ProductClientController::class, 'getProductWithDetailByID']); // api get theo id product nhé fe
    Route::get('product_cate/{id}', [ProductClientController::class, 'getProductByCate']); // api get product theo id cate nhé fe


});
