<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ResponsesController;
use App\Models\ActuatorPin;
use App\Models\BoardPin;
use App\Models\PinType;
use App\Models\SensorPin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PinTypesController extends ResponsesController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pin_type = PinType::all();
        $this->saveToLog('Pin Types', 'Getting list of pin types');
        return $this->sendResponse($pin_type, '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPinTypes(Request $request)
    {
        // Datatable search & pagination parameters
        $dt = $this->dtResponse($request);
        $searchValue = $dt->searchValue;

        $totalRecords = $this->fetchAllPinTypes()->count();

        $totalRecordswithFilter = $this->fetchAllPinTypes()
            ->where(function ($query) use ($searchValue) {
                $query->where('p.type', 'like', '%' . $searchValue . '%');
            })
            ->get()
            ->count();

        // Fetch records
        $records = $this->fetchAllPinTypes()
            ->orderBy($dt->columnName, $dt->columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('p.type', 'like', '%' . $searchValue . '%');
            })
            ->skip($dt->start)
            ->take($dt->rowPerPage)
            ->get();

        $this->saveToLog('Pin Types', 'Getting list of pin types');
        return $this->sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $dt->draw);
    }


    public function fetchAllPinTypes()
    {
        return DB::table('pin_types as p')
            ->selectRaw('p.*');
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
            'type' => 'required',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $pin_type = new PinType;
        $pin_type->type = $request->get('type');
        $pin_type->save();

        $this->saveToLog('Pin Types', 'Created pin type: ' . $request->get('type'));
        return $this->sendResponse([], 'Pin type has been created!');
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
            'type' => 'required',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $pin_type = PinType::find($id);
        $pin_type->type = $request->get('type');
        $pin_type->save();

        $this->saveToLog('Pin Types', 'Updated pin type: ' . $request->get('type'));
        return $this->sendResponse([], 'Pin type has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $pin_type = PinType::find($id);
        $type = $pin_type->type;
        if (
            BoardPin::where('pin_type_id', $id)->exists()   ||
            SensorPin::where('pin_type_id', $id)->exists()  ||
            ActuatorPin::where('pin_type_id', $id)->exists()
        )
            return $this->sendError('These pin type has been assigned to board, sensor or actuators');
        PinType::destroy($id);
        $this->saveToLog('Pin Types', 'Deleted pin type: ' . $type);
        return $this->sendResponse([], 'Pin type has been deleted!');
    }
}




