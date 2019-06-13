<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Generator;
use Carbon\Carbon;

use App\History;

class HistoryController extends Controller
{
    public function getAll(Request $request){
        try {
            $generator = new Generator('History', $request, route('history.all'));

            $results = [];
            $results['meta'] = $generator->meta();
            $results['links'] = $generator->paginator();
            
            foreach ($generator->data() as $history) {
                $results['data'][] = [
                                'type' => 'history',
                                'id' => $history->id,
                                'attributes' =>[
                                    "loggable_type" => $history->loggable_type,
                                    "loggable_id" => $history->loggable_id,
                                    "action" => $history->action,
                                    "kwuid" => $history->kwuid,
                                    "value" => $history->value,
                                    "updated_at" => Carbon::parse($history->updated_at)->toW3cString(),
                                    "created_at" => Carbon::parse($history->created_at)->toW3cString(),
                                ],
                                'links' => [
                                    'self' => route('history.one', ['historyId' => $history->id])
                                ],
                            ];

            }
            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500', 
                'error' => 'Server Error'
            ], 500);
        }
    }

    public function getone($historyId){
        try {
            $history = History::find($historyId);
            if(!$history){
                return response()->json([
                    'status' => "404",
                    "error" => "Not Found"
                ], 404);
            }
            
            $results['data'] = [
                            'type' => 'history',
                            'id' => $history->id,
                            'attributes' =>[
                                "loggable_type" => $history->loggable_type,
                                "loggable_id" => $history->loggable_id,
                                "action" => $history->action,
                                "kwuid" => $history->kwuid,
                                "value" => $history->value,
                                "updated_at" => Carbon::parse($history->updated_at)->toW3cString(),
                                "created_at" => Carbon::parse($history->created_at)->toW3cString(),
                            ],
                            'links' => [
                                'self' => route('history.one', ['historyId' => $history->id])
                            ],
                        ];

            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500', 
                'error' => 'Server Error'
            ], 500);
        }
    }

}