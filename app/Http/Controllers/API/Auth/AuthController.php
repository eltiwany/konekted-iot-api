<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\ResponsesController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends ResponsesController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('api', ['except' => ['auth']]);
    }

    /**
     * Get JWT authenticated token
     * given user credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        $credentials = request(['email', 'password']);

        if (!auth()->attempt($credentials)) {
            // Set incorrect attempt and block if attempts reached 3 times
            if (User::where('email', $request->get('email'))->exists() && $request->get('email') != 'admin@nafuutronics.com' && $request->get('email') != 'admin@konekted.com') {
                $user = User::where('email', $request->get('email'))->first();
                $incorrectAttempt = $user->incorrect_login_attempt + 1;
                $user->incorrect_login_attempt = $incorrectAttempt;
                if ($incorrectAttempt >= 3)
                    $user->is_active = 0;
                $user->save();
                // Show the user that they are blocked
                if ($incorrectAttempt >= 3)
                    return $this->sendError('Your account is blocked, please contact system administrator!', ['error' => 'Unauthorised'], 401);
            }
            return $this->sendError('Invalid Email and/or Password.', ['error' => 'Unauthorised'], 401);
        } else {
            $user = Auth::user();
            // Check if user is blocked
            if (!$user->is_active && $user->incorrect_login_attempt >= 3)
                return $this->sendError('Your account is blocked, please contact system administrator!', ['error' => 'Unauthorised'], 401);
            // Check if user is not active
            else if (!$user->is_active)
                return $this->sendError('Your account is not active, please activate your account!', ['error' => 'Unauthorised'], 401);

            $rolePermissions = $user->role->role_permission;
            $permissions = [];
            foreach ($rolePermissions as $rolePermission)
                array_push($permissions, [
                    'name' => $rolePermission->permission->name,
                    'page' => $rolePermission->page->name
                ]);

            $token = auth()->claims([
                'name' => $user->name,
                'email' => $user->email,
                'permissions' => $permissions,
            ])->attempt($credentials);

            $success = [
                'accessToken' => $token,
                // 'tokenType' => 'bearer',
                // 'expiresIn' => auth()->factory()->getTTL() * 60,
            ];

            return $this->sendResponse($success, 'Login was successful!');
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuth()
    {
        return $this->sendResponse(auth()->user(), '');
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function invalidateAuth()
    {
        if (auth()->check())
            auth()->logout(true);

        return $this->sendResponse([], 'Successfully logged out.', 200);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        if (!Hash::check($request->get('currentPassword'), auth()->user()->password))
            return $this->sendError('Invalid Current Password!', $validator->errors(), 401);

        $user = User::find(auth()->user()->id);
        $user->password = bcrypt($request->get('password'));
        $user->save();

        return $this->sendResponse([], "Password has been changed!");
    }

    public function activateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'currentPassword' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        if (User::where('email', $request->get('email'))->exists()) {
            $user = User::where('email', $request->get('email'))->first();

            if (!Hash::check($request->get('currentPassword'), $user->password))
                return $this->sendError('Invalid default password!', $validator->errors(), 401);

            // Check if user is blocked
            if ($user->incorrect_login_attempt >= 3)
                return $this->sendError('Your account is blocked, please contact system administrator!', ['error' => 'Unauthorised'], 401);

            $user->incorrect_login_attempt = 0;
            $user->is_active = 1;
            $user->password = bcrypt($request->get('password'));
            $user->save();
        } else
            return $this->sendError('Provided email is not valid!', ['error' => 'Unauthorised'], 404);

        return $this->sendResponse([], "Account activated, please login with your new password.");
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->sendResponse([
            'accessToken' => auth()->refresh(true, true)
        ], '');
    }
}
