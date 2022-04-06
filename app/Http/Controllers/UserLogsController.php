<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use Illuminate\Support\Facades\Auth;

class UserLogsController extends Controller
{
    /**
     * Save user logs
     * @param $pageName - Name of the accessed page
     * @param $action - Action performed on that page
     */
    public function saveToLog($pageName, $action)
    {
        $user = Auth::user();
        $log = new UserLog;
        $log->email = $user->email;
        $log->name = $user->name;
        $log->page = $pageName;
        $log->action = $action;
        $log->save();
    }
}
