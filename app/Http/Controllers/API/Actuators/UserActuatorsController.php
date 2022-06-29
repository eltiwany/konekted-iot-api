<?php

namespace App\Http\Controllers\API\Actuators;

use App\Http\Controllers\ResponsesController;
use App\Models\ActuatorColumn;
use App\Models\ActuatorPin;
use App\Models\UserActuator;
use App\Models\UserActuatorConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserActuatorsController extends ResponsesController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userActuators = $this->fetchAllUserActuators()->get();
        $userActuatorsWithPins = $this->fetchPinNumbers($userActuators);
        $this->saveToLog('User Actuators', 'Getting list of user actuators');
        return $this->sendResponse($userActuatorsWithPins, '');
    }

    public function switchActuator(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userActuatorId' => 'required',
            'isSwitchedOn' => 'required',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $userActuatorId = $request->get('userActuatorId');
        $isSwitchedOn = $request->get('isSwitchedOn');

        // Save userActuator
        $userActuator = UserActuator::find($userActuatorId);
        $name = $userActuator->actuator->name;
        $userActuator->is_switched_on = $isSwitchedOn;
        $userActuator->save();

        $this->saveToLog('Control Actuators', 'Actuator: ' . $name . ' has been turned ' . ($isSwitchedOn ? 'on' : 'off'));
        return $this->sendResponse([], 'Actuator: ' . $name . ' has been turned ' . ($isSwitchedOn ? 'on' : 'off'));
    }

    public function getUserActuatorPinTypes()
    {
        return $this->sendResponse($this->fetchUserActuatorPinTypes(), '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserActuators(Request $request)
    {
        // Datatable search & pagination parameters
        $dt = $this->dtResponse($request);
        $searchValue = $dt->searchValue;

        $totalRecords =
        count(
            $this->fetchPinNumbers(
                $this->fetchAllUserActuators()
                ->get()
            )
        );

        $totalRecordswithFilter =
        count(
            $this->fetchPinNumbers(
                $this->fetchAllUserActuators()
                ->where(function ($query) use ($searchValue) {
                    $query
                        ->where('b.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('bp.pin_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('pt.type', 'like', '%' . $searchValue . '%');
                })->get()
            )
        );

        // Fetch records
        $records = $this->fetchAllUserActuators()
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

        $this->saveToLog('User Actuators', 'Getting list of user actuators');
        return $this->sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $dt->draw);
    }


    public function fetchAllUserActuators()
    {
        return DB::table('user_actuators as ub')
            ->join('actuators as b', 'b.id', '=', 'ub.actuator_id')
            ->leftJoin('actuator_pins as bp', 'b.id', '=', 'bp.actuator_id')
            ->leftJoin('pin_types as pt', 'pt.id', '=', 'bp.pin_type_id')
            ->selectRaw('
                            ub.id,
                            b.id as actuator_id,
                            b.name,
                            ub.name as user_defined_name,
                            b.description,
                            b.image_url,
                            ub.is_switched_on,
                            ub.is_active_low,
                            ub.operating_value
            ')
            ->whereRaw('ub.user_id = ?', [ auth()->user()->id ])
            ->groupBy('ub.id');
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
                'pin_count' => $pin->pin_count,
            ];
        });

    }

    public function fetchPinNumbers($actuators)
    {
        $actuatorsWithPins = [];
        foreach ($actuators as $actuator) {
            // Pins
            $pins = ActuatorPin::where('actuator_id', $actuator->actuator_id)
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

            // Columns
            $actuatorColumns = ActuatorColumn::where('actuator_id', $actuator->actuator_id)
            ->orderBy('column', 'asc')
            ->get();
            $filteredActuatorColumns = $actuatorColumns->map(function(ActuatorColumn $column) {
                return [
                    'column' => $column->column,
                    'id' => $column->id,
                ];
            });

            // Connections
            $actuatorConnections = UserActuatorConnection::where('user_actuator_id', $actuator->id)
            ->get();
            $filteredUserActuatorConnetions = $actuatorConnections->map(function(UserActuatorConnection $connection) {
                return [
                    'id' => $connection->id,
                    'actuator_pin' => $connection->actuator_pin->pin_type->type,
                    'board_pin' => $connection->board_pin->pin_type->type,
                    'board_pin_number' => $connection->board_pin->pin_number,
                ];
            });

            array_push($actuatorsWithPins, [
                "actuator" => $actuator,
                "pinTypes" => $this->fetchActuatorPinTypes($actuator->actuator_id),
                "pins" => $filteredPins,
                "columns" => $filteredActuatorColumns,
                "connections" => $filteredUserActuatorConnetions
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
        // Update userActuator through post
        if ($request->has('id'))
            return $this->update($request, $request->get('id'));

        $validator = Validator::make($request->all(), [
            'actuatorId' => 'required',
            'isActiveLow' => 'required',
            'connections' => 'required',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $actuatorId = $request->get('actuatorId');
        $name = $request->get('name');
        $isActiveLow = $request->get('isActiveLow');
        $userBoardId = $request->get('userBoardId');
        $connections = $request->get('connections');

        // Save userActuator
        $userActuator = new UserActuator;
        $userActuator->user_id = auth()->user()->id;
        $userActuator->name = $name;
        $userActuator->actuator_id = $actuatorId;
        $userActuator->is_active_low = $isActiveLow;
        $userActuator->is_switched_on = $isActiveLow;
        $userActuator->user_board_id = $userBoardId;
        $userActuator->save();

        // Save board pins
        $userActuatorId = UserActuator::orderBy('created_at', 'desc')->first()->id;
        foreach ($connections as $connection) {
            $actuatorConnection = new UserActuatorConnection();
            $actuatorConnection->user_actuator_id = $userActuatorId;
            $actuatorConnection->board_pin_id =  $connection['boardPinId'];
            $actuatorConnection->actuator_pin_id = $connection['actuatorPinId'];
            $actuatorConnection->save();
        }

        $this->saveToLog('Actuators', 'Linked Actuator with actuatorId: ' . $actuatorId);
        return $this->sendResponse([], 'Actuator has been linked to your account!');
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
        $userActuator = UserActuator::find($id);
        $actuatorName = $userActuator->actuator->name;
        $userActuator->delete();
        $this->saveToLog('User Actuators', 'Unlink actuator: ' . $actuatorName);
        return $this->sendResponse([], 'Actuator has been unlinked from your account!');
    }
}
