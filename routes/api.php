<?php

use App\Http\Controllers\AuthController;
<<<<<<< Updated upstream
=======
use App\Http\Controllers\BillController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SizeController;

>>>>>>> Stashed changes
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\TablesController;
use App\Http\Controllers\TimeOrderTableController;
use App\Http\Controllers\UserController;
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



Route::prefix('admin')->middleware(['auth', 'checkRole:Supper Admin,Cộng tác viên,Quản trị viên'])->group(function () {


    // users
    Route::apiResource('users', UserController::class)->middleware('auth', 'checkRole:Supper Admin,Quản trị viên');

    // roles+

    Route::apiResource('roles', RoleController::class)->middleware('auth', 'checkRole:Supper Admin,Quản trị viên');

    // customer
    Route::apiResource('customer', CustomerController::class)->middleware('auth', 'checkRole:Cộng tác viên,Supper Admin,Quản trị viên');

    // categories
    Route::apiResource('category', CategoryController::class)->middleware('auth', 'checkRole:Cộng tác viên,Supper Admin,Quản trị viên');

    // sub-categories
    Route::apiResource('subcategory', SubcategoryController::class)->middleware('auth', 'checkRole:Cộng tác viên,Supper Admin,Quản trị viên');

    // tables
    Route::apiResource('tables', TablesController::class)->middleware('auth', 'checkRole:Cộng tác viên,Supper Admin,Quản trị viên');

    // timeOrderTable
    Route::apiResource('time_order_table', TimeOrderTableController::class)->middleware('auth', 'checkRole:Cộng tác viên,Supper Admin,Quản trị viên');
});


