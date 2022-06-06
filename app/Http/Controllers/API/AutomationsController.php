<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponsesController;
use App\Models\Automation;
use App\Models\UserActuator;
use App\Models\UserSensorValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AutomationsController extends ResponsesController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $automation = $this->fetchAutomations()->get();
        $this->saveToLog('Automations', 'Getting list of automations');
        return $this->sendResponse($automation, '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAutomations(Request $request)
    {
        // Datatable search & pagination parameters
        $dt = $this->dtResponse($request);
        $searchValue = $dt->searchValue;

        $totalRecords = $this->fetchAutomations()->get()->count();

        $totalRecordswithFilter = $this->fetchAutomations()
            ->where(function ($query) use ($searchValue) {
                $query->where('a.id', 'like', '%' . $searchValue . '%')
                        ->orWhere('us.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('sc.column', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.comparison_operation', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.value', 'like', '%' . $searchValue . '%')
                        ->orWhere('ua.name', 'like', '%' . $searchValue . '%');
            })
            ->get()
            ->count();

        // Fetch records
        $records = $this->fetchAutomations()
            ->orderBy($dt->columnName, $dt->columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('a.id', 'like', '%' . $searchValue . '%')
                        ->orWhere('us.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('sc.column', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.comparison_operation', 'like', '%' . $searchValue . '%')
                        ->orWhere('a.value', 'like', '%' . $searchValue . '%')
                        ->orWhere('ua.name', 'like', '%' . $searchValue . '%');
            })
            ->skip($dt->start)
            ->take($dt->rowPerPage)
            ->get();

        $this->saveToLog('Automations', 'Getting list of automations');
        return $this->sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $dt->draw);
    }


    public function fetchAutomations()
    {
        return DB::table('automations as a')
            ->join('user_sensors as us', 'us.id', '=', 'a.user_sensor_id')
            ->join('sensor_columns as sc', 'sc.id', '=', 'a.sensor_column_id')
            ->join('user_actuators as ua', 'ua.id', '=', 'a.user_actuator_id')
            ->selectRaw('
                            a.id,
                            us.id as sensor_id,
                            us.name as sensor_name,
                            sc.id as column_id,
                            sc.column as column_name,
                            a.comparison_operation,
                            a.value,
                            ua.id as actuator_id,
                            ua.name as actuator_name,
                            a.is_switched_on
                        ')
            ->where('us.user_id', auth()->user()->id);
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
            'userSensorId' => 'required',
            'sensorColumnId' => 'required',
            'comparisonOperation' => 'required',
            'value' => 'required',
            'isSwitchedOn' => 'required',
            'userActuatorId' => 'required',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $automation = new Automation;
        $automation->user_sensor_id = $request->get('userSensorId');
        $automation->sensor_column_id = $request->get('sensorColumnId');
        $automation->comparison_operation = $request->get('comparisonOperation');
        $automation->value = $request->get('value');
        $automation->operating_value = $request->get('operatingValue') ?? 0;
        $automation->is_switched_on = $request->get('isSwitchedOn');
        $automation->user_actuator_id = $request->get('userActuatorId');
        $automation->save();

        $this->saveToLog('Automations', 'Created automation: ' . $request->get('type'));
        return $this->sendResponse([], 'Automation sequence has been created!');
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
            'userSensorId' => 'required',
            'sensorColumnId' => 'required',
            'comparisonOperation' => 'required',
            'value' => 'required',
            'isSwitchedOn' => 'required',
            'userActuatorId' => 'required',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $automation = Automation::find($id);
        $automation->user_sensor_id = $request->get('userSensorId');
        $automation->sensor_column_id = $request->get('sensorColumnId');
        $automation->comparison_operation = $request->get('comparisonOperation');
        $automation->value = $request->get('value');
        $automation->operating_value = $request->get('operatingValue') ?? 0;
        $automation->is_switched_on = $request->get('isSwitchedOn');
        $automation->user_actuator_id = $request->get('userActuatorId');
        $automation->save();

        $this->saveToLog('Automations', 'Updated automation: ' . $request->get('type'));
        return $this->sendResponse([], 'Automation sequence has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Automation::destroy($id);
        $this->saveToLog('Automations', 'Deleted automation with id: ' . $id);
        return $this->sendResponse([], 'Automation sequence has been deleted!');
    }

    static function triggerAutomation($userSensorId, $columnId)
    {
        $automations = DB::table('automations as a')
            ->join('user_sensors as us', 'us.id', '=', 'a.user_sensor_id')
            ->join('sensor_columns as sc', 'sc.id', '=', 'a.sensor_column_id')
            ->selectRaw('a.*')
            ->whereRaw('us.id = ? and sc.id = ?', [$userSensorId, $columnId])
            ->get();

        foreach($automations as $automation) {
            $userSensorValue = UserSensorValue::where(['user_sensor_id' => $userSensorId, 'sensor_column_id' => $columnId])->orderBy('created_at', 'desc')->first()->value;

            if ($automation->comparison_operation == "E" && $userSensorValue == $automation->value) {
                $userActuator = UserActuator::find($automation->user_actuator_id);
                $userActuator->is_switched_on = $automation->is_switched_on;
                $userActuator->save();
            }

            if ($automation->comparison_operation == "GE" && $userSensorValue >= $automation->value) {
                $userActuator = UserActuator::find($automation->user_actuator_id);
                $userActuator->is_switched_on = $automation->is_switched_on;
                $userActuator->save();
            }

            if ($automation->comparison_operation == "NE" && $userSensorValue != $automation->value) {
                $userActuator = UserActuator::find($automation->user_actuator_id);
                $userActuator->is_switched_on = $automation->is_switched_on;
                $userActuator->save();
            }

            if ($automation->comparison_operation == "LE" && $userSensorValue <= $automation->value) {
                $userActuator = UserActuator::find($automation->user_actuator_id);
                $userActuator->is_switched_on = $automation->is_switched_on;
                $userActuator->save();
            }

            if ($automation->comparison_operation == "G" && $userSensorValue > $automation->value) {
                $userActuator = UserActuator::find($automation->user_actuator_id);
                $userActuator->is_switched_on = $automation->is_switched_on;
                $userActuator->save();
            }

            if ($automation->comparison_operation == "L" && $userSensorValue < $automation->value) {
                $userActuator = UserActuator::find($automation->user_actuator_id);
                $userActuator->is_switched_on = $automation->is_switched_on;
                $userActuator->save();
            }
        }
    }
}
