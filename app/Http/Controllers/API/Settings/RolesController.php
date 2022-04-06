<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\ResponsesController;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RolesController extends ResponsesController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::OrderBy('name', 'asc')->get();
        $this->saveToLog('Roles', 'Getting list of roles');
        return $this->sendResponse($roles, '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRoles(Request $request)
    {
        // Datatable search & pagination parameters
        $dt = $this->dtResponse($request);
        $searchValue = $dt->searchValue;

        $totalRecords = Role::count();

        $totalRecordswithFilter = Role::where(function ($query) use ($searchValue) {
            $query->where('name', 'like', '%' . $searchValue . '%')
                ->orWhere('id', 'like', '%' . $searchValue . '%');
        })
            ->get()
            ->count();

        // Fetch records
        $records = Role::OrderBy($dt->columnName, $dt->columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('id', 'like', '%' . $searchValue . '%');
            })
            ->skip($dt->start)
            ->take($dt->rowPerPage)
            ->get();

        $this->saveToLog('Roles', 'Getting list of roles');
        return $this->sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $dt->draw);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'roleName' => 'required',
            'rolePermissions' => 'required',
            'isDefault' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $id = rand(100000, 999999);
        if (Role::where('id', $id)->exists())
            $id = rand(100000, 999999);

        if ($request->get('isDefault')) {
            Role::where('id', '!=', 0)->update(['is_default' => 0]);
        }

        $role = new Role;
        $role->is_default = $request->get('isDefault');
        $role->id = $id;
        $role->name = $request->get('roleName');
        $role->save();

        foreach ($request->get('rolePermissions') as $permission) {
            $rolePermission = new RolePermission;
            $rolePermission->role_id = $id;
            $rolePermission->permission_id = $permission['permissionId'];
            $rolePermission->page_id = $permission['pageId'];
            $rolePermission->save();
        }

        $this->saveToLog('Roles', 'Added new role with name: ' . $request->get('roleName'));
        return $this->sendResponse([], 'Role has been added!');
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
            'roleName' => 'required',
            'rolePermissions' => 'required',
            'isDefault' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        if ($request->get('isDefault')) {
            Role::where('id', '!=', 0)->update(['is_default' => 0]);
        }

        $role = Role::find($id);
        $role->is_default = $request->get('isDefault');
        $role->name = $request->get('roleName');
        $role->save();

        RolePermission::where('role_id', $id)->delete();

        foreach ($request->get('rolePermissions') as $permission) {
            $rolePermission = new RolePermission;
            $rolePermission->role_id = $id;
            $rolePermission->permission_id = $permission['permissionId'];
            $rolePermission->page_id = $permission['pageId'];
            $rolePermission->save();
        }

        $this->saveToLog('Roles', 'Update role with name: ' . $request->get('roleName'));
        return $this->sendResponse([], 'Role has been updated!');
    }

    /**
     * Show the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);
        return $this->sendResponse([
            'role' => $role,
            'rolePermissions' => $role->role_permission,
        ], '');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Role::destroy($id);
        $this->saveToLog('Roles', 'Deleted roles with ID: ' . $id);
        return $this->sendResponse([], 'Role has been deleted!');
    }
}
