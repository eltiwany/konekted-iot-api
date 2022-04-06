<?php

namespace App\Http\Controllers\API\Settings;

use App\Http\Controllers\ResponsesController;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class PagesController extends ResponsesController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = Page::OrderBy('name', 'asc')->get();
        $this->saveToLog('Pages', 'Getting list of pages');
        return $this->sendResponse($page, '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPages(Request $request)
    {
        // Datatable search & pagination parameters
        $dt = $this->dtResponse($request);
        $searchValue = $dt->searchValue;

        $totalRecords = Page::count();

        $totalRecordswithFilter = Page::where(function ($query) use ($searchValue) {
            $query->where('name', 'like', '%' . $searchValue . '%')
                ->orWhere('id', 'like', '%' . $searchValue . '%');
        })
            ->get()
            ->count();

        // Fetch records
        $records = Page::OrderBy($dt->columnName, $dt->columnSortOrder)
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('id', 'like', '%' . $searchValue . '%');
            })
            ->skip($dt->start)
            ->take($dt->rowPerPage)
            ->get();

        $this->saveToLog('Pages', 'Getting list of pages');
        return $this->sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $dt->draw);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = FacadesValidator::make($request->all(), [
            'pageName' => 'required'
        ]);

        if (Page::where('name', $request->get('pageName'))->exists())
            return $this->sendError('Page already exists', $validator->errors(), 401);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $page = new Page;
        $page->name = $request->get('pageName');
        $page->save();

        $this->saveToLog('Pages', 'Added new page with name: ' . $request->get('pageName'));
        return $this->sendResponse([], 'Page has been added!');
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
        $validator = FacadesValidator::make($request->all(), [
            'pageName' => 'required'
        ]);

        if (Page::where('name', $request->get('pageName'))->exists()) {
            $modelId = Page::where('name', $request->get('pageName'))->first()->id;
            if ($id != $modelId)
                return $this->sendError('Page already exists', $validator->errors(), 401);
        }

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $page = Page::find($id);
        $page->name = $request->get('pageName');
        $page->save();

        $this->saveToLog('Pages', 'Updated page with name: ' . $request->get('pageName'));
        return $this->sendResponse([], 'Page has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Page::destroy($id);
        $this->saveToLog('Pages', 'Deleted page with ID: ' . $id);
        return $this->sendResponse([], 'Page has been deleted!');
    }
}
