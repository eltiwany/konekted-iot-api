<?php

namespace App\Http\Controllers\API\Boards;

use App\Http\Controllers\ResponsesController;
use App\Models\BoardPin;
use App\Models\UserBoard;
use App\Models\UserBoardPin;
use App\Models\UserSensorConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserBoardsController extends ResponsesController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userBoards = $this->fetchAllUserBoards()->get();
        $userBoardsWithPins = $this->fetchPinNumbers($userBoards);
        $this->saveToLog('User Boards', 'Getting list of user boards');
        return $this->sendResponse($userBoardsWithPins, '');
    }

    public function getBoardOMC(Request $request)
    {
        $token = $request->get('token');
        $userActiveBoard = $this->fetchAllUserBoards($token)->first();

        if (!$userActiveBoard)
            return $this->sendError('No board found or invalid token provided!');

        $this->saveToLog('OMC', 'Getting board info from MC', $token);
        return $this->sendResponse($userActiveBoard, '');
    }

    public function getConnectionsOMC(Request $request)
    {
        $token = $request->get('token');


        $userBoard = $this->fetchAllUserBoards($token)->first();
        $connections = $this->fetchConnections($userBoard->id);
        $this->saveToLog('OMC', 'Fetching devices connections', $token);
        return $this->sendResponse($connections, '');
    }

    public function getActuatorsOMC(Request $request)
    {
        $token = $request->get('token');

        $userBoard = $this->fetchAllUserBoards($token)->first();
        $connections = $this->fetchConnections($userBoard->id, "actuators");
        $this->saveToLog('OMC', 'Fetching devices connections', $token);
        return $this->sendResponse($connections, '');
    }

    public function getActuatorStatus(Request $request, $userActuatorId)
    {
        // Getting actuators connected with this board
        $userActuator = DB::table('user_actuator_connections as usc')
                    ->join('user_actuators as us', 'usc.user_actuator_id', '=', 'us.id')
                    ->join('user_boards as ub', 'us.user_board_id', '=', 'ub.id')
                    ->join('boards as b', 'ub.board_id', '=', 'b.id')
                    ->join('actuators as s', 's.id', '=', 'us.actuator_id')
                    ->selectRaw('us.id, s.name, us.operating_value, us.is_switched_on')
                    ->where('us.id', $userActuatorId)
                    ->groupBy('us.id')
                    ->first();

        if (!$userActuator) {
            $this->saveToLog('OMC', 'Error: can\'t find user actuator details', $request->get('token'));
            return $this->sendError('No actuator found!');
        }

        $this->saveToLog('OMC', 'Getting actuator (' . $userActuator->name . ') status as (' . ($userActuator->is_switched_on ? 'ON' : 'OFF') . ')', $request->get('token'));
        return $this->sendResponse($userActuator, '');

    }

    public function setBoardOMC(Request $request)
    {
        $userActiveBoard = UserBoard::where('token', $request->get('token'))->first();
        $userActiveBoard->is_online = $request->get('status') == 1 ?? 1;
        $userActiveBoard->save();

        $this->saveToLog('OMC', 'Board is ' . ($request->get('status') == 1 ? 'online' : 'offline') . '!', $request->get('token'));
        return $this->sendResponse([], 'Board is ' . ($request->get('status') == 1 ? 'online' : 'offline') . '!');
    }

    public function fetchConnections($userBoardId, $filter = "all")
    {
        $connections = [];

        if ($filter == "all" || $filter == "sensors") {
            // Getting sensors connected with this board
            $userSensors = DB::table('user_sensor_connections as usc')
                        ->join('user_sensors as us', 'usc.user_sensor_id', '=', 'us.id')
                        ->join('user_boards as ub', 'us.user_board_id', '=', 'ub.id')
                        ->join('boards as b', 'ub.board_id', '=', 'b.id')
                        ->join('sensors as s', 's.id', '=', 'us.sensor_id')
                        ->selectRaw('us.id, s.name, us.interval')
                        ->where('ub.id', $userBoardId)
                        ->groupBy('us.id')
                        ->get();

                // For each sensor return simple connections and expected data
                $sensorsTemp = [];
                foreach ($userSensors as $userSensor) {
                    // Sensor connections
                    $userSensorConnections = DB::table('user_sensor_connections as usc')
                                            ->join('sensor_pins as sp', 'usc.sensor_pin_id', '=', 'sp.id')
                                            ->join('pin_types as spt', 'sp.pin_type_id', '=', 'spt.id')
                                            ->join('board_pins as bp', 'usc.board_pin_id', '=', 'bp.id')
                                            ->join('pin_types as bpt', 'bp.pin_type_id', '=', 'bpt.id')
                                            ->selectRaw('
                                                            spt.type as sensor_pin_type,
                                                            bpt.type as board_pin_type,
                                                            bp.pin_number as board_pin_number
                                                        ')
                                            ->where('usc.user_sensor_id', $userSensor->id)
                                            ->get();
                    // Sensor expected data
                    $userSensorColumns = DB::table('sensor_columns as sc')
                                            ->join('user_sensors as us', 'us.sensor_id', '=', 'sc.sensor_id')
                                            ->selectRaw('
                                                            sc.column
                                                        ')
                                            ->where('us.id', $userSensor->id)
                                            ->get();
                    array_push($sensorsTemp,
                        [
                            'sensor'        => $userSensor,
                            'columns'       => $userSensorColumns,
                            'connections'   => $userSensorConnections,
                        ]
                    );
                }

            array_push(
                $connections, [
                    "sensors" => $sensorsTemp,
                ]
            );
        }

        if ($filter == "all" || $filter == "actuators") {
            // Getting actuators connected with this board
            $userActuators = DB::table('user_actuator_connections as usc')
                        ->join('user_actuators as us', 'usc.user_actuator_id', '=', 'us.id')
                        ->join('user_boards as ub', 'us.user_board_id', '=', 'ub.id')
                        ->join('boards as b', 'ub.board_id', '=', 'b.id')
                        ->join('actuators as s', 's.id', '=', 'us.actuator_id')
                        ->selectRaw('us.id, s.name, us.operating_value, us.is_switched_on')
                        ->where('ub.id', $userBoardId)
                        ->groupBy('us.id')
                        ->get();

                // For each actuator return simple connections
                $actuatorsTemp = [];
                foreach ($userActuators as $userActuator) {
                    // Actuator connections
                    $userActuatorConnections = DB::table('user_actuator_connections as usc')
                                            ->join('actuator_pins as sp', 'usc.actuator_pin_id', '=', 'sp.id')
                                            ->join('pin_types as spt', 'sp.pin_type_id', '=', 'spt.id')
                                            ->join('board_pins as bp', 'usc.board_pin_id', '=', 'bp.id')
                                            ->join('pin_types as bpt', 'bp.pin_type_id', '=', 'bpt.id')
                                            ->selectRaw('
                                                            spt.type as actuator_pin_type,
                                                            bpt.type as board_pin_type,
                                                            bp.pin_number as board_pin_number
                                                        ')
                                            ->where('usc.user_actuator_id', $userActuator->id)
                                            ->get();

                    array_push($actuatorsTemp,
                        [
                            'actuator'      => $userActuator,
                            'connections'   => $userActuatorConnections,
                        ]
                    );
                }

            array_push(
                $connections, [
                    "actuators" => $actuatorsTemp
                ]
            );
        }

        return $connections;
    }

    public function getUserBoardPinTypes()
    {
        return $this->sendResponse($this->fetchUserBoardPinTypes(), '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserBoards(Request $request)
    {
        // Datatable search & pagination parameters
        $dt = $this->dtResponse($request);
        $searchValue = $dt->searchValue;

        $totalRecords =
        count(
            $this->fetchPinNumbers(
                $this->fetchAllUserBoards()
                ->get()
            )
        );

        $totalRecordswithFilter =
        count(
            $this->fetchPinNumbers(
                $this->fetchAllUserBoards()
                ->where(function ($query) use ($searchValue) {
                    $query
                        ->where('b.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('bp.pin_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('pt.type', 'like', '%' . $searchValue . '%');
                })->get()
            )
        );

        // Fetch records
        $records = $this->fetchAllUserBoards()
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

        $this->saveToLog('User Boards', 'Getting list of user boards');
        return $this->sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $dt->draw);
    }


    public function fetchAllUserBoards($token = null)
    {
        $userBoards = DB::table('user_boards as ub')
            ->join('boards as b', 'b.id', '=', 'ub.board_id')
            ->leftJoin('board_pins as bp', 'b.id', '=', 'bp.board_id')
            ->leftJoin('pin_types as pt', 'pt.id', '=', 'bp.pin_type_id');

            if ($token)
                $userBoards = $userBoards->where(['token' => $token])
                                ->selectRaw('
                                    ub.id,
                                    b.id as board_id,
                                    ub.token,
                                    ub.is_online
                                ');

            else
                $userBoards = $userBoards->whereRaw('ub.user_id = ?', [ auth()->user()->id ])
                                ->selectRaw('
                                    ub.id,
                                    b.id as board_id,
                                    b.name,
                                    b.description,
                                    b.image_url,
                                    ub.token,
                                    ub.is_online
                                ');

            return $userBoards->groupBy('b.id');
    }

    public function fetchBoardPinTypes($boardId = false)
    {
        $boardPins = BoardPin::selectRaw('distinct pin_type_id, count(pin_type_id) as pin_count');
        // If specific board
        if ($boardId)
            $boardPins = $boardPins->where('board_id', $boardId);
        $boardPins = $boardPins
        ->groupBy('pin_type_id')
        ->get();

        return $boardPins->map(function (BoardPin $pin) {
            return [
                'pin_type_id' => $pin->pin_type_id,
                'pin_type' => $pin->pin_type->type,
                'pin_count' => $pin->pin_count
            ];
        });
    }

    public function fetchPinNumbers($boards, $token = null)
    {
        $boardsWithPins = [];
        foreach ($boards as $board) {
            $pins = BoardPin::where('board_id', $board->board_id)
            ->orderBy('pin_type_id', 'asc')
            ->orderBy('pin_number', 'asc')
            ->get();
            $filteredPins = $pins->map(function(BoardPin $pin) {
                return [
                    'pin_type_id' => $pin->pin_type_id,
                    'pin_type' => $pin->pin_type->type,
                    'pin_number' => (int) $pin->pin_number,
                    'remarks' => $pin->remarks,
                    'id' => $pin->id,
                ];
            });

            array_push($boardsWithPins, [
                "board" => $board,
                "pinTypes" => $this->fetchBoardPinTypes($board->board_id),
                "pins" => $filteredPins,
            ]);
        }
        return $boardsWithPins;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Update userBoard through post
        if ($request->has('id'))
            return $this->update($request, $request->get('id'));

        $validator = Validator::make($request->all(), [
            'boardId' => 'required',
            'token' => 'required',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $boardId = $request->get('boardId');
        $token = $request->get('token');

        // Save userBoard
        if (UserBoard::where('user_id', auth()->user()->id)->exists())
            $userBoard = UserBoard::where('user_id', auth()->user()->id)->first();
        else
            $userBoard = new UserBoard;
        $userBoard->user_id = auth()->user()->id;
        $userBoard->board_id = $boardId;
        $userBoard->token = $token;
        $userBoard->save();

        $this->saveToLog('Boards', 'Linked Board with boardId: ' . $boardId);
        return $this->sendResponse([], 'Board has been linked to your account!');
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
        $userBoard = UserBoard::find($id);
        $boardName = $userBoard->board->name;
        $userBoard->delete();
        $this->saveToLog('User Boards', 'Unlink board: ' . $boardName);
        return $this->sendResponse([], 'Board has been unlinked from your account!');
    }
}
