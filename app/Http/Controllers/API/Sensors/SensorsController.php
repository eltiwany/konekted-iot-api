<?php

namespace App\Http\Controllers\API\Sensors;

use App\Http\Controllers\ResponsesController;
use App\Models\Sensor;
use App\Models\SensorColumn;
use App\Models\SensorPin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SensorsController extends ResponsesController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sensors = $this->fetchAllSensors()->get();
        $sensorsWithPins = $this->fetchPinNumbers($sensors);
        $this->saveToLog('Sensors', 'Getting list of sensors');
        return $this->sendResponse($sensorsWithPins, '');
    }

    public function getSensorPinTypes()
    {
        return $this->sendResponse($this->fetchSensorPinTypes(), '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSensors(Request $request)
    {
        // Datatable search & pagination parameters
        $dt = $this->dtResponse($request);
        $searchValue = $dt->searchValue;

        $totalRecords =
        count(
            $this->fetchPinNumbers(
                $this->fetchAllSensors()
                ->get()
            )
        );

        $totalRecordswithFilter =
        count(
            $this->fetchPinNumbers(
                $this->fetchAllSensors()
                ->where(function ($query) use ($searchValue) {
                    $query
                        ->where('b.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('bp.pin_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('pt.type', 'like', '%' . $searchValue . '%');
                })->get()
            )
        );

        // Fetch records
        $records = $this->fetchAllSensors()
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

        $this->saveToLog('Sensors', 'Getting list of sensors');
        return $this->sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $dt->draw);
    }


    public function fetchAllSensors()
    {
        return DB::table('sensors as b')
            ->leftJoin('sensor_pins as bp', 'b.id', '=', 'bp.sensor_id')
            ->leftJoin('pin_types as pt', 'pt.id', '=', 'bp.pin_type_id')
            ->selectRaw('
                            b.id,
                            b.name,
                            b.description,
                            b.image_url
            ')
            ->groupBy('b.id');
    }

    public function fetchSensorPinTypes($sensorId = false)
    {
        $sensorPins = SensorPin::selectRaw('distinct pin_type_id, count(pin_type_id) as pin_count');
        // If specific sensor
        if ($sensorId)
            $sensorPins = $sensorPins->where('sensor_id', $sensorId);
        $sensorPins = $sensorPins
        ->groupBy('pin_type_id')
        ->get();

        return $sensorPins->map(function (SensorPin $pin) {
            return [
                'pin_type_id' => $pin->pin_type_id,
                'pin_type' => $pin->pin_type->type,
                'pin_count' => $pin->pin_count
            ];
        });

    }

    public function fetchPinNumbers($sensors)
    {
        $sensorsWithPins = [];
        foreach ($sensors as $sensor) {
            $pins = SensorPin::where('sensor_id', $sensor->id)
            ->orderBy('pin_type_id', 'asc')
            ->orderBy('pin_number', 'asc')
            ->get();
            $filteredPins = $pins->map(function(SensorPin $pin) {
                return [
                    'pin_type_id' => $pin->pin_type_id,
                    'pin_type' => $pin->pin_type->type,
                    'pin_number' => (int) $pin->pin_number,
                    'remarks' => $pin->remarks,
                    'id' => $pin->id,
                ];
            });

            $columns = SensorColumn::where('sensor_id', $sensor->id)->get();
            $filteredColumns = $columns->map(function(SensorColumn $column) {
                return [
                    'id' => $column->id,
                    'column' => $column->column,
                ];
            });

            array_push($sensorsWithPins, [
                "sensor" => $sensor,
                "pinTypes" => $this->fetchSensorPinTypes($sensor->id),
                "pins" => $filteredPins,
                "columns" => $filteredColumns,
            ]);
        }
        return $sensorsWithPins;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Update sensor through post
        if ($request->has('id'))
            return $this->update($request, $request->get('id'));

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'imageUrl' => 'required|mimes:png,jpg,jpeg,bmp,gif,svg',
            'sensorPins' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $path = 'sensors';

        if ($request->hasFile('imageUrl')) {
            $fileNameWithExt = $request->file('imageUrl')->getClientOriginalName();
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('imageUrl')->getClientOriginalExtension();
            $image = $filename . '_' . time() . '.' . $extension;
            $request->file('imageUrl')->storeAs('public/' . $path, $image);
        }

        // Save sensor
        $sensor = new Sensor;
        $sensor->name = $request->get('name');
        $sensor->description = $request->get('description');
        $sensor->image_url = $path . "/" . $image;
        $sensor->save();

        // Save sensor pins
        $sensorId = Sensor::orderBy('created_at', 'desc')->first()->id;
        $sensorPins =
            json_decode(
                $request->get('sensorPins')
            );
        foreach ($sensorPins as $_sensorPin) {
            $sensorPin = new SensorPin;
            $sensorPin->sensor_id = $sensorId;
            $sensorPin->pin_type_id = $_sensorPin->pinType;
            $sensorPin->pin_number = $_sensorPin->pinNumber;
            $sensorPin->remarks = $_sensorPin->remarks;
            $sensorPin->save();
        }

        $columns =
            json_decode(
                $request->get('columns')
            );
        foreach ($columns as $_column) {
            $column = new SensorColumn;
            $column->sensor_id = $sensorId;
            $column->column = $_column->column;
            $column->save();
        }

        $this->saveToLog('Sensors', 'Create sensor with name: ' . $request->get('name'));
        return $this->sendResponse([], 'Sensor has been created!');
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
            'sensorPins' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $path = 'sensors';

        if ($request->hasFile('imageUrl')) {
            $sensor = Sensor::find($id);
            if (explode('/', $sensor->image_url)[0] != 'sensors-bak')
                Storage::delete('public/' . $sensor->image_url);
            $fileNameWithExt = $request->file('imageUrl')->getClientOriginalName();
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('imageUrl')->getClientOriginalExtension();
            $image = $filename . '_' . time() . '.' . $extension;
            $request->file('imageUrl')->storeAs('public/' . $path, $image);
        }

        // Save sensor
        $sensor = Sensor::find($id);
        $sensor->name = $request->get('name');
        $sensor->description = $request->get('description');
        if ($request->hasFile('imageUrl'))
            $sensor->image_url = $path . "/" . $image;
        $sensor->save();

        // Save sensor pins
        SensorPin::where('sensor_id', $id)->delete();
        $sensorPins =
            json_decode(
                $request->get('sensorPins')
            );
        foreach ($sensorPins as $_sensorPin) {
            $sensorPin = new SensorPin;
            $sensorPin->sensor_id = $id;
            $sensorPin->pin_type_id = $_sensorPin->pinType;
            $sensorPin->pin_number = $_sensorPin->pinNumber;
            $sensorPin->remarks = $_sensorPin->remarks;
            $sensorPin->save();
        }

        SensorColumn::where('sensor_id', $id)->delete();
        $columns =
            json_decode(
                $request->get('columns')
            );
        foreach ($columns as $_column) {
            $column = new SensorColumn;
            $column->sensor_id = $id;
            $column->column = $_column->column;
            $column->save();
        }

        $this->saveToLog('Sensors', 'Updated sensor with name: ' . $request->get('name'));
        return $this->sendResponse([], 'Sensor has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $sensor = Sensor::find($id);
        if (explode('/', $sensor->image_url)[0] != 'sensors-bak')
                Storage::delete('public/' . $sensor->image_url);
        $sensorName = $sensor->name;
        Sensor::destroy($id);
        $this->saveToLog('Sensors', 'Deleted sensor: ' . $sensorName);
        return $this->sendResponse([], 'Sensor has been deleted!');
    }
}
