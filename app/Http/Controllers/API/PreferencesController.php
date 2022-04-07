<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponsesController;
use App\Models\Preference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PreferencesController extends ResponsesController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $preferences = Preference::all();
        return $this->sendResponse($preferences, '');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->createIfNotExists($id);
        $preference = Preference::where('key', $id)->first();
        // $this->saveToLog('Preferences', 'View preference informations with key: ' . $id);
        return $this->sendResponse($preference, '');
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
            'key' => 'required',
            // 'value' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        if (!Preference::where('key', $request->get('key'))->exists())
            $preference = new Preference;
        else
            $preference = Preference::where('key', $request->get('key'))->first();
        $preference->key = $request->get('key');
        $preference->value = $request->get('value');
        $preference->save();

        $this->saveToLog('Preferences', 'Updated prefence informations with key: ' . $request->get('key'));
        return $this->sendResponse([], 'Preference updated!');
    }

    public function updatePreferenceFiles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required',
            'value' => 'required|mimes:png,jpg,jpeg,bmp,gif,svg',
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $path = 'preference-files';

        if ($request->hasFile('value')) {
            $fileNameWithExt = $request->file('value')->getClientOriginalName();
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('value')->getClientOriginalExtension();
            $image = $filename . '_' . time() . '.' . $extension;
            $request->file('value')->storeAs('public/' . $path, $image);
        }

        if (!Preference::where('key', $request->get('key'))->exists())
            $preference = new Preference;
        else {
            $preference = Preference::where('key', $request->get('key'))->first();
            Storage::delete('public/' . $preference->value);
        }
        $preference->key = $request->get('key');
        $preference->value = $path . "/" . $image;
        $preference->save();

        // $this->saveToLog('Preferences', 'Updated prefence file with key: ' . $request->get('key'));
        return $this->sendResponse([], 'Preference updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Preference::where('key', $id)->exists()) {
            $preference = Preference::where('key', $id)->first();
            Storage::delete('public/' . $preference->value);
            Preference::where('key', $id)->delete();
            $this->saveToLog('Preferences', 'Removed preference with key: ' . $id);
            return $this->sendResponse([], "Requested preference has been removed!");
        }
        $this->saveToLog('Preferences', 'Attempted to remove preference with key: ' . $id . ' but failed');
        return $this->sendError('No preference found!');
    }

    /**
     * Create key value pair in database if key
     * doesnt exists yet
     *
     * @param $id as key
     * @return void
     */
    public function createIfNotExists($id)
    {
        if (!Preference::where('key', $id)->exists()) {
            $preference = new Preference;
            $preference->key = $id;
            $preference->value = '';
            $preference->save();
        }
    }
}
