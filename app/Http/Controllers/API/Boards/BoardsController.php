<?php

namespace App\Http\Controllers\API\Boards;

use App\Http\Controllers\ResponsesController;
use App\Models\Board;
use App\Models\BoardPin;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BoardsController extends ResponsesController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $boards = $this->fetchAllBoards()->get();
        $boardsWithPins = $this->fetchPinNumbers($boards);
        $this->saveToLog('Boards', 'Getting list of boards');
        return $this->sendResponse($boardsWithPins, '');
    }

    public function getBoardPinTypes()
    {
        return $this->sendResponse($this->fetchBoardPinTypes(), '');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBoards(Request $request)
    {
        // Datatable search & pagination parameters
        $dt = $this->dtResponse($request);
        $searchValue = $dt->searchValue;

        $totalRecords =
        count(
            $this->fetchPinNumbers(
                $this->fetchAllBoards()
                ->get()
            )
        );

        $totalRecordswithFilter =
        count(
            $this->fetchPinNumbers(
                $this->fetchAllBoards()
                ->where(function ($query) use ($searchValue) {
                    $query
                        ->where('b.name', 'like', '%' . $searchValue . '%')
                        ->orWhere('bp.pin_number', 'like', '%' . $searchValue . '%')
                        ->orWhere('pt.type', 'like', '%' . $searchValue . '%');
                })->get()
            )
        );

        // Fetch records
        $records = $this->fetchAllBoards()
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

        $this->saveToLog('Boards', 'Getting list of boards');
        return $this->sendDTResponse($records, $totalRecords, $totalRecordswithFilter, $dt->draw);
    }


    public function fetchAllBoards()
    {
        return DB::table('boards as b')
            ->leftJoin('board_pins as bp', 'b.id', '=', 'bp.board_id')
            ->leftJoin('pin_types as pt', 'pt.id', '=', 'bp.pin_type_id')
            ->selectRaw('
                            b.id,
                            b.name,
                            b.description,
                            b.image_url
            ')
            ->groupBy('b.id');
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

    public function fetchPinNumbers($boards)
    {
        $boardsWithPins = [];
        foreach ($boards as $board) {
            $pins = BoardPin::where('board_id', $board->id)
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
                "pinTypes" => $this->fetchBoardPinTypes($board->id),
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
        // Update board through post
        if ($request->has('id'))
            return $this->update($request, $request->get('id'));

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'imageUrl' => 'required|mimes:png,jpg,jpeg,bmp,gif,svg',
            'boardPins' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $path = 'boards';

        if ($request->hasFile('imageUrl')) {
            $fileNameWithExt = $request->file('imageUrl')->getClientOriginalName();
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('imageUrl')->getClientOriginalExtension();
            $image = $filename . '_' . time() . '.' . $extension;
            $request->file('imageUrl')->storeAs('public/' . $path, $image);
        }

        // Save board
        $board = new Board;
        $board->name = $request->get('name');
        $board->description = $request->get('description');
        $board->image_url = $path . "/" . $image;
        $board->save();

        // Save board pins
        $boardId = Board::orderBy('created_at', 'desc')->first()->id;
        $boardPins =
            json_decode(
                $request->get('boardPins')
            );
        foreach ($boardPins as $_boardPin) {
            $boardPin = new BoardPin;
            $boardPin->board_id = $boardId;
            $boardPin->pin_type_id = $_boardPin->pinType;
            $boardPin->pin_number = $_boardPin->pinNumber;
            $boardPin->remarks = $_boardPin->remarks;
            $boardPin->save();
        }

        $this->saveToLog('Boards', 'Create board with name: ' . $request->get('name'));
        return $this->sendResponse([], 'Board has been created!');
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
            'boardPins' => 'required'
        ]);

        if ($validator->fails())
            return $this->sendError('Validation fails', $validator->errors(), 401);

        $path = 'boards';

        if ($request->hasFile('imageUrl')) {
            $board = Board::find($id);
            if (explode('/', $board->image_url)[0] != 'boards-bak')
                Storage::delete('public/' . $board->image_url);
            $fileNameWithExt = $request->file('imageUrl')->getClientOriginalName();
            $filename = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('imageUrl')->getClientOriginalExtension();
            $image = $filename . '_' . time() . '.' . $extension;
            $request->file('imageUrl')->storeAs('public/' . $path, $image);
        }

        // Save board
        $board = Board::find($id);
        $board->name = $request->get('name');
        $board->description = $request->get('description');
        if ($request->hasFile('imageUrl'))
            $board->image_url = $path . "/" . $image;
        $board->save();

        // Save board pins
        BoardPin::where('board_id', $id)->delete();
        $boardPins =
            json_decode(
                $request->get('boardPins')
            );
        foreach ($boardPins as $_boardPin) {
            $boardPin = new BoardPin;
            $boardPin->board_id = $id;
            $boardPin->pin_type_id = $_boardPin->pinType;
            $boardPin->pin_number = $_boardPin->pinNumber;
            $boardPin->remarks = $_boardPin->remarks;
            $boardPin->save();
        }

        $this->saveToLog('Boards', 'Updated board with name: ' . $request->get('name'));
        return $this->sendResponse([], 'Board has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $board = Board::find($id);
        if (explode('/', $board->image_url)[0] != 'boards-bak')
                Storage::delete('public/' . $board->image_url);
        $boardName = $board->name;
        Board::destroy($id);
        $this->saveToLog('Boards', 'Deleted board: ' . $boardName);
        return $this->sendResponse([], 'Board has been deleted!');
    }
}
