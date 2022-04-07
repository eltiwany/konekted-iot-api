<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ResponsesController;
use App\Models\User;
use App\Models\UserLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UsersController extends ResponsesController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = User::OrderBy('email', 'asc')->get();
        $this->saveToLog('Users', 'Getting list of users');
        return $this->sendResponse($user, '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUsers(Request $request)
    {
        // Datatable search & pagination parameters
        $dt = $this->dtResponse($request);
        $searchValue = $dt->searchValue;

        $totalRecords = $this->fetchAllUsers()->count();

        $totalRecordswithFilter = $this->fetchAllUsers()
            ->where(function ($query) use ($searchValue) {
                $query->where('u.email', 'like', '%' . $searchValue . '%')
                    ->orWhere('e.id', 'like', '%' . $searchValue . '%')
                    ->orWhere('r.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('u.id', 'like', '%' . $searchValue . '%');
            })
            ->get()
            ->count();

        // Fetch records
        $records = $this->fetchAllUsers()
            ->orderBy($dt->columnName, $dt->columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('u.email', 'like', '%' . $searchValue . '%')
                    ->orWhere('e.id', 'like', '%' . $searchValue . '%')
                    ->orWhere('u.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('r.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('u.id', 'like', '%' . $searchValue . '%');
            })
            ->skip($dt->start)
            ->take($dt->rowPerPage)
            ->get();

        $this->saveToLog('Users', 'Getting list of users');
        return $this->sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $dt->draw);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserLogs(Request $request)
    {
        // Datatable search & pagination parameters
        $dt = $this->dtResponse($request);
        $searchValue = $dt->searchValue;

        $totalRecords = UserLog::count();

        $totalRecordswithFilter = UserLog::where(function ($query) use ($searchValue) {
            $query->where('action', 'like', '%' . $searchValue . '%')
                ->orWhere('page', 'like', '%' . $searchValue . '%')
                ->orWhere('name', 'like', '%' . $searchValue . '%')
                ->orWhere('email', 'like', '%' . $searchValue . '%')
                ->orWhere('id', 'like', '%' . $searchValue . '%');
        })
            ->get()
            ->count();

        // Fetch records
        $records = UserLog::orderBy($dt->columnName, $dt->columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('action', 'like', '%' . $searchValue . '%')
                    ->orWhere('page', 'like', '%' . $searchValue . '%')
                    ->orWhere('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%')
                    ->orWhere('id', 'like', '%' . $searchValue . '%');
            })
            ->skip($dt->start)
            ->take($dt->rowPerPage)
            ->get();

        // $this->saveToLog('Users', 'Getting user logs');
        return $this->sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $dt->draw);
    }


    public function fetchAllUsers()
    {
        return DB::table('users as u')
            ->join('roles as r', 'r.id', '=', 'u.role_id')
            ->selectRaw('u.*, r.name as role_name, u.name as full_name');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'roleId' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $user = new User;
        $user->email = $request->get('email');
        $user->role_id = $request->get('roleId');
        $user->password = bcrypt($request->get('password'));
        $user->is_active = 1;
        $user->save();

        $this->saveToLog('Users', 'Create user with email: ' . $request->get('email'));
        return $this->sendResponse([], 'User has been added!');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'roleId' => 'required',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $user = User::find($id);
        $user->email = $request->get('email');
        $user->role_id = $request->get('roleId');
        $user->save();

        $this->saveToLog('Users', 'Updated user with email: ' . $request->get('email'));
        return $this->sendResponse([], 'User has been updated!');
    }


    /**
     * Reset users password.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $user = User::find($request->get('userId'));

        // Below two columns are in-effect if user is blocked
        $user->incorrect_login_attempt = 0;
        $user->is_active = 0;

        $user->password = bcrypt('user12345');
        $user->save();

        $this->saveToLog('Users', 'Reset user password with userId: ' . $request->get('userId'));
        return $this->sendResponse([], 'User password has been reset to user12345');
    }

    /**
     * Clear user logs
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function clearUserLogs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $date = Carbon::parse($request->get('date'))
            ->toDateTimeString();

        $dateLog = Carbon::parse($request->get('date'))
            ->format('M j, Y h:i:s A');

        UserLog::where('created_at', '<', $date)->delete();

        $this->saveToLog('User Logs', 'Cleared all logs before ' . $dateLog . '');
        return $this->sendResponse([], 'User Logs have been cleared!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if ($user->email == 'admin@nafuutronics.com' || $user->email == 'admin@konekted.com')
            return $this->sendError('You can not delete default admins!');
        User::destroy($id);
        $this->saveToLog('Users', 'Deleted user with userId: ' . $id);
        return $this->sendResponse([], 'User has been deleted!');
    }
}




