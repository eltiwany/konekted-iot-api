<?php

namespace App\Http\Controllers\API\Sensors;

use App\Http\Controllers\ResponsesController;
use App\Models\SensorColumn;
use App\Models\SensorPin;
use App\Models\UserSensor;
use App\Models\UserSensorConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserSensorsController extends ResponsesController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userSensors = $this->fetchAllUserSensors()->get();
        $userSensorsWithPins = $this->fetchPinNumbers($userSensors);
        $this->saveToLog('User Sensors', 'Getting list of user sensors');
        return $this->sendResponse($userSensorsWithPins, '');
    }

    public function getUserSensorPinTypes()
    {
        return $this->sendResponse($this->fetchUserSensorPinTypes(), '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserSensors(Request $request)
    {
        // Datatable search & pagination parameters
        $dt = $this->dtResponse($request);
        $searchValue = $dt->searchValue;

        $totalRecords =
        count(
            $this->fetchPinNumbers(
                $this->fetchAllUserSensors()
                ->get()
            )
        );

        $totalRecordswithFilter =
        count(
            $this->fetchPinNumbers(
                $this->fetchAllUserSensors()
                ->where(function ($query) use ($searchValue) {
                    $query
                        ->where('b.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('bp.pin_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('pt.type', 'like', '%' . $searchValue . '%');
                })->get()
            )
        );

        // Fetch records
        $records = $this->fetchAllUserSensors()
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

        $this->saveToLog('User Sensors', 'Getting list of user sensors');
        return $this->sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $dt->draw);
    }


    public function fetchAllUserSensors()
    {
        return DB::table('user_sensors as ub')
            ->join('sensors as b', 'b.id', '=', 'ub.sensor_id')
            ->leftJoin('sensor_pins as bp', 'b.id', '=', 'bp.sensor_id')
            ->leftJoin('pin_types as pt', 'pt.id', '=', 'bp.pin_type_id')
            ->selectRaw('
                            ub.id,
                            b.id as sensor_id,
                            b.name,
                            ub.name as user_defined_name,
                            b.description,
                            b.image_url,
                            ub.interval
            ')
            ->whereRaw('ub.user_id = ?', [ auth()->user()->id ])
            ->groupBy('ub.id');
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
                'pin_count' => $pin->pin_count,
            ];
        });

    }

    public function fetchPinNumbers($sensors)
    {
        $sensorsWithPins = [];
        foreach ($sensors as $sensor) {
            // Pins
            $pins = SensorPin::where('sensor_id', $sensor->sensor_id)
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

            // Columns
            $sensorColumns = SensorColumn::where('sensor_id', $sensor->sensor_id)
            ->orderBy('column', 'asc')
            ->get();
            $filteredSensorColumns = $sensorColumns->map(function(SensorColumn $column) {
                return [
                    'column' => $column->column,
                    'id' => $column->id,
                ];
            });

            // Connections
            $sensorConnections = UserSensorConnection::where('user_sensor_id', $sensor->id)
            ->get();
            $filteredUserSensorConnetions = $sensorConnections->map(function(UserSensorConnection $connection) {
                return [
                    'id' => $connection->id,
                    'sensor_pin' => $connection->sensor_pin->pin_type->type,
                    'board_pin' => $connection->board_pin->pin_type->type,
                    'board_pin_number' => $connection->board_pin->pin_number,
                ];
            });

            array_push($sensorsWithPins, [
                "sensor" => $sensor,
                "pinTypes" => $this->fetchSensorPinTypes($sensor->sensor_id),
                "pins" => $filteredPins,
                "columns" => $filteredSensorColumns,
                "connections" => $filteredUserSensorConnetions
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
        // Update userSensor through post
        if ($request->has('id'))
            return $this->update($request, $request->get('id'));

        $validator = Validator::make($request->all(), [
            'sensorId' => 'required',
            'interval' => 'required',
            'connections' => 'required',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $sensorId = $request->get('sensorId');
        $name = $request->get('name');
        $userBoardId = $request->get('userBoardId');
        $interval = $request->get('interval');
        $connections = $request->get('connections');

        // Save userSensor
        $userSensor = new UserSensor;
        $userSensor->user_id = auth()->user()->id;
        $userSensor->sensor_id = $sensorId;
        $userSensor->name = $name;
        $userSensor->user_board_id = $userBoardId;
        $userSensor->interval = $interval;
        $userSensor->save();

        // Save board pins
        $userSensorId = UserSensor::orderBy('created_at', 'desc')->first()->id;
        foreach ($connections as $connection) {
            $sensorConnection = new UserSensorConnection();
            $sensorConnection->user_sensor_id = $userSensorId;
            $sensorConnection->board_pin_id =  $connection['boardPinId'];
            $sensorConnection->sensor_pin_id = $connection['sensorPinId'];
            $sensorConnection->save();
        }

        $this->saveToLog('Sensors', 'Linked Sensor with sensorId: ' . $sensorId);
        return $this->sendResponse([], 'Sensor has been linked to your account!');
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $userSensor = UserSensor::find($id);
        $sensorName = $userSensor->sensor->name;
        $userSensor->delete();
        $this->saveToLog('User Sensors', 'Unlink sensor: ' . $sensorName);
        return $this->sendResponse([], 'Sensor has been unlinked from your account!');
    }
}
