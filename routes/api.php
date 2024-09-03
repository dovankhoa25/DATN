<?php

use App\Http\Controllers\AuthController;

use App\Http\Controllers\CartController;

use App\Http\Controllers\BillController;
use App\Http\Controllers\BillDetailController;

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SizeController;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\TablesController;
use App\Http\Controllers\TimeOrderTableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoucherController;

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

    // customer
    Route::apiResource('customers', CustomerController::class)->middleware('auth', 'checkRole:qtv,admin');
    // voucher
    Route::post('vouchers', [VoucherController::class, 'store'])->middleware('auth', 'checkRole:qtv,admin');
    Route::get('vouchers/{id}', [VoucherController::class, 'show'])->middleware('auth', 'checkRole:qtv,admin');

    // sizes
    Route::apiResource('sizes', SizeController::class)->middleware('auth', 'checkRole:qtv,admin');
    // payments
    Route::apiResource('payments', PaymentController::class)->middleware('auth', 'checkRole:qtv,admin');


    //cart
    Route::apiResource('carts', CartController::class)->middleware('auth', 'checkRole:qtv,admin');


    Route::apiResource('products', PaymentController::class)->middleware('auth', 'checkRole:qtv,admin');


    Route::apiResource('category', CategoryController::class)->middleware('auth', 'checkRole:qtv,admin');
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
