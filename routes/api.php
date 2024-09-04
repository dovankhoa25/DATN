<?php

use App\Http\Controllers\Admin\AuthController;


use App\Http\Controllers\Admin\CartController;

use App\Http\Controllers\Admin\BillController;
use App\Http\Controllers\Admin\BillDetailController;

use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SizeController;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\TablesController;
use App\Http\Controllers\Admin\TimeOrderTableController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VoucherController;

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



Route::prefix('admin')->middleware(['auth', 'checkRole:qtv,admin'])->group(function () {
    // users
    Route::apiResource('users', UserController::class)->middleware('auth', 'checkRole:qtv,admin');

    Route::apiResource('roles', RoleController::class)->middleware('auth', 'checkRole:qtv,admin');

    //cate
    Route::apiResource('category', CategoryController::class)->middleware('auth', 'checkRole:qtv,admin');
    Route::apiResource('sub_category', SubcategoryController::class)->middleware('auth', 'checkRole:qtv,admin');

    // customer
    Route::apiResource('customers', CustomerController::class)->middleware('auth', 'checkRole:qtv,admin');
    // voucher
    Route::apiResource('vouchers', VoucherController::class)->middleware('auth', 'checkRole:qtv,admin');

    // sizes
    Route::apiResource('sizes', SizeController::class)->middleware('auth', 'checkRole:qtv,admin');
    // payments
    Route::apiResource('payments', PaymentController::class)->middleware('auth', 'checkRole:qtv,admin');


    //cart
    Route::apiResource('carts', CartController::class)->middleware('auth', 'checkRole:qtv,admin');


    Route::apiResource('products', ProductController::class)->middleware('auth', 'checkRole:qtv,admin');

    // category
    Route::apiResource('category', CategoryController::class)->middleware('auth', 'checkRole:qtv,admin');
    // subcategory
    Route::apiResource('subcategory', SubcategoryController::class)->middleware('auth', 'checkRole:qtv,admin');


    // Bills
    Route::apiResource('bills', BillController::class)->middleware('auth', 'checkRole:qtv,admin');
    //Bill detail
    Route::apiResource('billsDetail', BillDetailController::class)->middleware('auth', 'checkRole:qtv,admin');

    // tables
    Route::apiResource('tables', TablesController::class)->middleware('auth', 'checkRole:qtv,admin');

    // timeOrderTable
    Route::apiResource('time_order_table', TimeOrderTableController::class)->middleware('auth', 'checkRole:qtv,admin');
});
