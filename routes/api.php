<?php

use App\Http\Controllers\API\Actuators\ActuatorsController;
use App\Http\Controllers\API\Actuators\UserActuatorsController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Boards\BoardsController;
use App\Http\Controllers\API\Boards\UserBoardsController;
use App\Http\Controllers\API\PinTypesController;
use App\Http\Controllers\API\PreferencesController;
use App\Http\Controllers\API\Sensors\SensorsController;
use App\Http\Controllers\API\Sensors\UserSensorsController;
use App\Http\Controllers\API\Settings\PagesController;
use App\Http\Controllers\API\Settings\PermissionsController;
use App\Http\Controllers\API\Settings\RolesController;
use App\Http\Controllers\API\UsersController;
use App\Http\Middleware\API\JWTAuth;
use App\Http\Middleware\API\PagesPermissions;
use App\Http\Middleware\Token;
use Illuminate\Support\Facades\Artisan;
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

/**
 * Microcontroller Routes
 * Doesnt require auth
 */
Route::get('migrate', function() {
    Artisan::call('migrate');
    Artisan::call('db:seed');
    return response()->json(Artisan::output());
});

Route::get('link', function() {
    Artisan::call('storage:link');
    return response()->json(Artisan::output());
});

Route::group([
    'middleware' => [ Token::class ]
], function () {
    Route::get('get-board-omc', [UserBoardsController::class, 'getBoardOMC']);
    Route::get('get-connections-omc', [UserBoardsController::class, 'getConnectionsOMC']);
    Route::get('get-actuators-omc', [UserBoardsController::class, 'getActuatorsOMC']);
    Route::get('get-sensors-omc', [UserBoardsController::class, 'getSensorsOMC']);
    Route::get('set-board-omc', [UserBoardsController::class, 'setBoardOMC']);
    Route::get('set-sensor-data-omc', [UserSensorsController::class, 'setSensorData']);
    Route::get('get-actuator-status-omc/{userActuatorId}', [UserBoardsController::class, 'getActuatorStatus']);
});


Route::group([
    'middleware' => ['api']
], function () {

    // Auth API's
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth', [AuthController::class, 'authenticate']);
    Route::post('activate-account', [AuthController::class, 'activateAccount']);
    Route::post('register', [AuthController::class, 'register']);
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

        // Boards
        Route::post('get-boards', [BoardsController::class, 'getBoards']);
        Route::post('get-board-pin-types', [BoardsController::class, 'getBoardPinTypes']);
        Route::resource('boards', BoardsController::class);
        // -------------------------- - - - - - ----------------------------- //
        Route::resource('user-boards', UserBoardsController::class);

        // Sensors
        Route::post('get-sensors', [SensorsController::class, 'getSensors']);
        Route::post('get-sensor-pin-types', [SensorsController::class, 'getSensorPinTypes']);
        Route::resource('sensors', SensorsController::class);
        // -------------------------- - - - - - ----------------------------- //
        Route::resource('user-sensors', UserSensorsController::class);
        Route::get('get-sensor-omc', [UserSensorsController::class, 'getSensorOMC']);

        // Actuators
        Route::post('get-actuators', [ActuatorsController::class, 'getActuators']);
        Route::post('get-actuator-pin-types', [ActuatorsController::class, 'getActuatorPinTypes']);
        Route::resource('actuators', ActuatorsController::class);
        // -------------------------- - - - - - ----------------------------- //
        Route::post('switch-actuator', [UserActuatorsController::class, 'switchActuator']);
        Route::resource('user-actuators', UserActuatorsController::class);

        // Pins
        Route::post('get-pin-types', [PinTypesController::class, 'getPinTypes']);
        Route::resource('pin-types', PinTypesController::class);

        // Settings API's
        Route::group([
            'prefix' => 'user-boards'
        ], function () {
            // Page Access
            Route::resource('get-boards', PagesController::class);
            Route::post('get-pages', [PagesController::class, 'getPages']);

            // Roles
            Route::resource('roles', RolesController::class);
            Route::post('get-roles', [RolesController::class, 'getRoles']);

            // Permissions
            Route::resource('permissions', PermissionsController::class);
            Route::post('get-permissions', [PermissionsController::class, 'getPermissions']);
        });

        // Settings API's
        Route::group([
            'prefix' => 'settings'
        ], function () {
            // Page Access
            Route::resource('pages', PagesController::class);
            Route::post('get-pages', [PagesController::class, 'getPages']);

            // Roles
            Route::resource('roles', RolesController::class);
            Route::post('get-roles', [RolesController::class, 'getRoles']);

            // Permissions
            Route::resource('permissions', PermissionsController::class);
            Route::post('get-permissions', [PermissionsController::class, 'getPermissions']);
        });
    });

    // Preferences can be accessable without authentication
    Route::post('preference-files', [PreferencesController::class, 'updatePreferenceFiles']);
    Route::resource('preferences', PreferencesController::class);
});
