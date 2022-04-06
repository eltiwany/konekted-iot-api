<?php

use App\Http\Controllers\API\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group([
    'middleware' => ['api']
], function () {

    // Auth API's
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth', [AuthController::class, 'authenticate']);
    Route::post('activate-account', [AuthController::class, 'activateAccount']);
    Route::post('logout', [AuthController::class, 'invalidateAuth']);

    Route::group([
        'middleware' => [JWTAuth::class, PagesPermissions::class]
    ], function () {
        // Auth API's
        Route::post('get-auth', [AuthController::class, 'getAuth']);

        // My Space
        Route::group([
            'prefix' => 'my-area'
        ], function () {
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });

        // Anonymous Users
        Route::post('get-users', [UsersController::class, 'getUsers']);
        Route::post('get-user-logs', [UsersController::class, 'getUserLogs']);
        Route::post('clear-user-logs', [UsersController::class, 'clearUserLogs']);
        Route::post('reset-password', [UsersController::class, 'reset']);
        Route::resource('users', UsersController::class);

        // Settings API's
        Route::group([
            'prefix' => 'settings'
        ], function () {
            // Page Access
            Route::resource('pages', PageAccessController::class);
            Route::post('get-pages', [PageAccessController::class, 'getPages']);

            // Roles
            Route::resource('roles', RolesController::class);
            Route::post('get-roles', [RolesController::class, 'getRoles']);

            // Permissions
            Route::resource('permissions', PermissionsController::class);
            Route::post('get-permissions', [PermissionsController::class, 'getPermissions']);

        });
    });
});
