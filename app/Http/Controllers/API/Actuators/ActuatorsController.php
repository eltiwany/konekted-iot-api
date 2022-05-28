<?php

namespace App\Http\Controllers\API\Actuators;

use App\Http\Controllers\ResponsesController;
use App\Models\Actuator;
use App\Models\ActuatorColumn;
use App\Models\ActuatorPin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ActuatorsController extends ResponsesController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $actuators = $this->fetchAllActuators()->get();
        $actuatorsWithPins = $this->fetchPinNumbers($actuators);
        $this->saveToLog('Actuators', 'Getting list of actuators');
        return $this->sendResponse($actuatorsWithPins, '');
    }

    public function getActuatorPinTypes()
    {
        return $this->sendResponse($this->fetchActuatorPinTypes(), '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getActuators(Request $request)
    {
        // Datatable search & pagination parameters
        $dt = $this->dtResponse($request);
        $searchValue = $dt->searchValue;

        $totalRecords =
        count(
            $this->fetchPinNumbers(
                $this->fetchAllActuators()
                ->get()
            )
        );

        $totalRecordswithFilter =
        count(
            $this->fetchPinNumbers(
                $this->fetchAllActuators()
                ->where(function ($query) use ($searchValue) {
                    $query
                        ->where('b.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('bp.pin_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('pt.type', 'like', '%' . $searchValue . '%');
                })->get()
            )
        );

        // Fetch records
        $records = $this->fetchAllActuators()
            ->orderBy($dt->columnName, $dt->columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query
                    ->where('b.name', 'like', '%' . $searchValue . '%')
                    ->orWhere('bp.pin_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('pt.type', 'like', '%' . $searchValue . '%');
            })
            ->skip($dt->start)
            ->take($dt->rowPerPage)
            ->get();

        $records = $this->fetchPinNumbers($records);

        $this->saveToLog('Actuators', 'Getting list of actuators');
        return $this->sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $dt->draw);
    }


    public function fetchAllActuators()
    {
        return DB::table('actuators as b')
            ->leftJoin('actuator_pins as bp', 'b.id', '=', 'bp.actuator_id')
            ->leftJoin('pin_types as pt', 'pt.id', '=', 'bp.pin_type_id')
            ->selectRaw('
                            b.id,
                            b.name,
                            b.description,
                            b.image_url
            ')
            ->groupBy('b.id');
    }

    public function fetchActuatorPinTypes($actuatorId = false)
    {
        $actuatorPins = ActuatorPin::selectRaw('distinct pin_type_id, count(pin_type_id) as pin_count');
        // If specific actuator
        if ($actuatorId)
            $actuatorPins = $actuatorPins->where('actuator_id', $actuatorId);
        $actuatorPins = $actuatorPins
        ->groupBy('pin_type_id')
        ->get();

        return $actuatorPins->map(function (ActuatorPin $pin) {
            return [
                'pin_type_id' => $pin->pin_type_id,
                'pin_type' => $pin->pin_type->type,
                'pin_count' => $pin->pin_count
            ];
        });

    }

    public function fetchPinNumbers($actuators)
    {
        $actuatorsWithPins = [];
        foreach ($actuators as $actuator) {
            $pins = ActuatorPin::where('actuator_id', $actuator->id)
            ->orderBy('pin_type_id', 'asc')
            ->orderBy('pin_number', 'asc')
            ->get();
            $filteredPins = $pins->map(function(ActuatorPin $pin) {
                return [
                    'pin_type_id' => $pin->pin_type_id,
                    'pin_type' => $pin->pin_type->type,
                    'pin_number' => (int) $pin->pin_number,
                    'remarks' => $pin->remarks,
                    'id' => $pin->id,
                ];
            });

            $columns = ActuatorColumn::where('actuator_id', $actuator->id)->get();
            $filteredColumns = $columns->map(function(ActuatorColumn $column) {
                return [
                    'id' => $column->id,
                    'column' => $column->column,
                ];
            });

            array_push($actuatorsWithPins, [
                "actuator" => $actuator,
                "pinTypes" => $this->fetchActuatorPinTypes($actuator->id),
                "pins" => $filteredPins,
                "columns" => $filteredColumns,
            ]);
        }
        return $actuatorsWithPins;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Update actuator through post
        if ($request->has('id'))
            return $this->update($request, $request->get('id'));

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'imageUrl' => 'required|mimes:png,jpg,jpeg,bmp,gif,svg',
            'actuatorPins' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $path = 'actuators';

        if ($request->hasFile('imageUrl')) {
            $fileNameWithExt = $request->file('imageUrl')->getClientOriginalName();
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('imageUrl')->getClientOriginalExtension();
            $image = $filename . '_' . time() . '.' . $extension;
            $request->file('imageUrl')->storeAs('public/' . $path, $image);
        }

        // Save actuator
        $actuator = new Actuator;
        $actuator->name = $request->get('name');
        $actuator->description = $request->get('description');
        $actuator->image_url = $path . "/" . $image;
        $actuator->save();

        // Save actuator pins
        $actuatorId = Actuator::orderBy('created_at', 'desc')->first()->id;
        $actuatorPins =
            json_decode(
                $request->get('actuatorPins')
            );
        foreach ($actuatorPins as $_actuatorPin) {
            $actuatorPin = new ActuatorPin;
            $actuatorPin->actuator_id = $actuatorId;
            $actuatorPin->pin_type_id = $_actuatorPin->pinType;
            $actuatorPin->pin_number = $_actuatorPin->pinNumber;
            $actuatorPin->remarks = $_actuatorPin->remarks;
            $actuatorPin->save();
        }

        $columns =
        json_decode(
            $request->get('columns')
        );
        foreach ($columns as $_column) {
            $column = new ActuatorColumn;
            $column->actuator_id = $actuatorId;
            $column->column = $_column->column;
            $column->save();
        }

        $this->saveToLog('Actuators', 'Create actuator with name: ' . $request->get('name'));
        return $this->sendResponse([], 'Actuator has been created!');
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
            'name' => 'required',
            'description' => 'required',
            'actuatorPins' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $path = 'actuators';

        if ($request->hasFile('imageUrl')) {
            $actuator = Actuator::find($id);
            if (explode('/', $actuator->image_url)[0] != 'actuators-bak')
                Storage::delete('public/' . $actuator->image_url);
            $fileNameWithExt = $request->file('imageUrl')->getClientOriginalName();
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('imageUrl')->getClientOriginalExtension();
            $image = $filename . '_' . time() . '.' . $extension;
            $request->file('imageUrl')->storeAs('public/' . $path, $image);
        }

        // Save actuator
        $actuator = Actuator::find($id);
        $actuator->name = $request->get('name');
        $actuator->description = $request->get('description');
        if ($request->hasFile('imageUrl'))
            $actuator->image_url = $path . "/" . $image;
        $actuator->save();

        // Save actuator pins
        ActuatorPin::where('actuator_id', $id)->delete();
        $actuatorPins =
            json_decode(
                $request->get('actuatorPins')
            );
        foreach ($actuatorPins as $_actuatorPin) {
            $actuatorPin = new ActuatorPin;
            $actuatorPin->actuator_id = $id;
            $actuatorPin->pin_type_id = $_actuatorPin->pinType;
            $actuatorPin->pin_number = $_actuatorPin->pinNumber;
            $actuatorPin->remarks = $_actuatorPin->remarks;
            $actuatorPin->save();
        }

        ActuatorColumn::where('actuator_id', $id)->delete();
        $columns =
        json_decode(
            $request->get('columns')
        );
        foreach ($columns as $_column) {
            $column = new ActuatorColumn;
            $column->actuator_id = $id;
            $column->column = $_column->column;
            $column->save();
        }

        $this->saveToLog('Actuators', 'Updated actuator with name: ' . $request->get('name'));
        return $this->sendResponse([], 'Actuator has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $actuator = Actuator::find($id);
        if (explode('/', $actuator->image_url)[0] != 'actuators-bak')
                Storage::delete('public/' . $actuator->image_url);
        $actuatorName = $actuator->name;
        Actuator::destroy($id);
        $this->saveToLog('Actuators', 'Deleted actuator: ' . $actuatorName);
        return $this->sendResponse([], 'Actuator has been deleted!');
    }
}
