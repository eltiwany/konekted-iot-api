<?php

namespace App\Http\Controllers;

class ResponsesController extends UserLogsController
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message, $code = 200)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $draw, $code = 200)
    {
        $response = [
            'success' => true,
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $records
        ];

        return response()->json($response, $code);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];


        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }

    /**
     * Datatable request for searching,
     * sorting and pagination
     *
     * @param Illuminate\Http\Request $request
     * @return Object
     */
    public function dtResponse($request)
    {
        return (object) [
            'draw' => $request->get('draw'),
            'start' => $request->get("start"),
            'rowPerPage' => $request->get("length"), // Rows display per Page

            'columnIndexArr' => $request->get('order'),
            'columnNameArr' => $request->get('columns'),
            'orderArr' => $request->get('order'),
            'searchArr' => $request->get('search'),
            'columnIndex' => $request->get('order')[0]['column'], // Column index

            'columnName' => $request->get('columns')[$request->get('order')[0]['column']]['data'], // Column name
            'columnSortOrder' => $request->get('order')[0]['dir'], // asc or desc
            'searchValue' => $request->get('search')['value'], // Search value
        ];
    }
}
