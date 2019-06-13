<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use Auth;
use Illuminate\Support\Facades\Log;

use App\Checklist;
use App\Item;
use App\History;

class ItemsController extends Controller
{
    public function complete(Request $request){
        try {
            $data = $request->data;
            $results = [];
            foreach ($data as $key => $value) {
                $item = Item::find($value['item_id']);
                $item->is_completed = true;
                $item->completed_at = Carbon::now()->toDateTimeString();
                $item->updated_by = Auth::user()->id;
                $item->save();

                History::create([
                    'loggable_type' => 'items',
                    'loggable_id' => $item->id,
                    'action' => 'complete',
                    'kwuid' => Auth::user()->id,
                    'value' => $item->toJson()
                ]);

                $results['data'][] = [
                    "id" => $item->id,
                    "item_id" => $item->id,
                    "is_completed" => $item->is_completed,
                    "checklist_id"=> $item->checklists_id
                ];
                $isAll = True;
                foreach ($item->checklist->item as $itemChecklist) {
                    $isAll = ($itemChecklist['is_completed'] == False ? False : True );
                }
                if($isAll){
                    $item->checklist->is_completed = $isAll;
                    $item->checklist->updated_by = Auth::user()->id;
                    $item->checklist->completed_at = Carbon::now()->toDateTimeString();
                    $item->checklist->save();

                    History::create([
                        'loggable_type' => 'checklist',
                        'loggable_id' => $item->checklist->id,
                        'action' => 'complete',
                        'kwuid' => Auth::user()->id,
                        'value' => $item->checklist->toJson()
                    ]);
                }
                
            }
            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500', 
                'error' => 'Server Error'
            ], 500);
        }
    }


    public function incomplete(Request $request){
        try {
            $data = $request->data;
            $results = [];
            foreach ($data as $key => $value) {
                $item = Item::find($value['item_id']);
                $item->is_completed = false;
                $item->updated_by = Auth::user()->id;
                $item->completed_at = null;
                $item->save();

                $results['data'][] = [
                    "id" => $item->id,
                    "item_id" => $item->id,
                    "is_completed" => $item->is_completed,
                    "checklist_id"=> $item->checklist_id
                ];

                History::create([
                    'loggable_type' => 'item',
                    'loggable_id' => $item->id,
                    'action' => 'incomplete',
                    'kwuid' => Auth::user()->id,
                    'value' => $item->toJson()
                ]);

                $item->checklist->is_completed = False;
                $item->checklist->updated_by = Auth::user()->id;
                $item->checklist->completed_at = null;
                $item->checklist->save();

                History::create([
                    'loggable_type' => 'checklist',
                    'loggable_id' => $item->checklist->id,
                    'action' => 'incomplete',
                    'kwuid' => Auth::user()->id,
                    'value' => $item->checklist->toJson()
                ]);
            }
            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500', 
                'error' => 'Server Error'
            ], 500);
        }
    }

    public function store(Request $request, $checklistId){
        try {
            $raw = ( empty($request->json()->all()) ? $request->all() : $request->json()->all());
            $data = $raw['data']['attribute'];

            if(empty($data['description'])){
                return response()->json([
                    'status' => "400",
                    "error" => "Some Value Required"
                ], 400);
            }

            $checkList = Checklist::find($checklistId);

            if(!$checkList){
                return response()->json([
                    'status' => "404",
                    "error" => "Not Found"
                ], 404);
            }

            $item = new Item();
            $item->description = $data['description'];
            $item->is_completed = $data['is_completed'] ?? false;
            $item->completed_at = ( isset($data['completed_at']) ? Carbon::parse($data['completed_at'])->toDateTimeString() : null);
            $item->due = ( isset($data['due']) ? Carbon::parse($data['due'])->toDateTimeString() : null);
            $item->urgency = $data['urgency'] ?? 0;
            $item->updated_by = $data['updated_by'] ?? 0;
            $item->assignee_id = $data['assignee_id']?? null;
            $item->checklist_id = $checklistId;
            $item->save();

            History::create([
                'loggable_type' => 'items',
                'loggable_id' => $item->id,
                'action' => 'create',
                'kwuid' => Auth::user()->id,
                'value' => $item->toJson()
            ]);

            $results = [
                'data' => [
                    'type' => 'items',
                    'id' => $item->id,
                    'attributes' =>[
                        'description' => $item->description,
                        "is_completed" => $item->is_completed,
                        "completed_at" => ($item->completed_at ? Carbon::parse($item->completed_at)->toW3cString() : null),
                        "due" => ($item->due ? Carbon::parse($item->due)->toW3cString() : null),
                        "urgency" => $item->urgency,
                        "updated_by" => $item->updated_by,
                        "updated_at" => Carbon::parse($item->updated_at)->toW3cString(),
                        "created_at" => Carbon::parse($item->created_at)->toW3cString(),
                    ],
                    'links' => [
                        'self' => route('items.one', ['checklistId' => $item->checklist_id , 'itemId' => $item->id ])
                    ],
                ]
            ];
            return response()->json($results, 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500', 
                'error' => 'Server Error'
            ], 500);
        }
        
    }

    public function getAll($checklistId){
        try {
            $checkList = Checklist::find($checklistId);

            if(!$checkList){
                return response()->json([
                    'status' => "404",
                    "error" => "Not Found"
                ], 404);
            }

            $items = [];
            foreach ($checkList->item as $item) {
                $items[] = $item;
            }

            $results = [
                'data' => [
                    'type' => 'checklists',
                    'id' => $checkList->id,
                    'attributes' =>[
                        'object_domain' =>  $checkList->object_domain,
                        'object_id' =>  $checkList->object_id,
                        'description' => $checkList->description,
                        "is_completed" => $checkList->is_completed,
                        "due" => Carbon::parse($checkList->due)->toW3cString(),
                        "urgency" => $checkList->urgency,
                        "completed_at" => ($checkList->completed_at ? Carbon::parse($checkList->completed_at)->toW3cString() : null),
                        "last_update_by" => $checkList->updated_by,
                        "created_by" => $checkList->created_by,
                        "created_at" => Carbon::parse($checkList->created_at)->toW3cString(),
                        "update_at" => Carbon::parse($checkList->updated_at)->toW3cString(),
                        "items" => $items
                    ],
                    'links' => [
                        'self' => route('checklist.one', ['checklistId' => $checkList->id])
                    ],
                ]
            ];
            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500', 
                'error' => 'Server Error'
            ], 500);
        }
    }

    public function getone($checklistId, $itemId){
        $checkList = Checklist::find($checklistId);
        $item = Item::find($itemId);
        
        if(!$checkList || !$item || $checkList->id != $item->checklist_id){
            return response()->json([
                'status' => "404",
                "error" => "Not Found"
            ], 404);
        }

        $results = [
            'data' => [
                'type' => 'items',
                'id' => $item->id,
                'attributes' =>[
                    'description' => $item->description,
                    "is_completed" => $item->is_completed,
                    "completed_at" => ($item->completed_at ? Carbon::parse($item->completed_at)->toW3cString() : null),
                    "due" => ($item->due ? Carbon::parse($item->due)->toW3cString() : null),
                    "urgency" => $item->urgency,
                    "updated_by" => $item->updated_by,
                    "created_by" => $item->updated_by,
                    "checklist_id" => $item->checklist_id,
                    "assignee_id" => $item->assignee_id,
                    "task_id" => $item->checklist_id,
                    "deleted_at" => null,
                    "updated_at" => Carbon::parse($item->updated_at)->toW3cString(),
                    "created_at" => Carbon::parse($item->created_at)->toW3cString(),
                ],
                'links' => [
                    'self' => route('items.one', ['checklistId' => $item->checklist_id , 'itemId' => $item->id ])
                ],
            ]
        ];
        return response()->json($results, 200);
    }
    
    public function update(Request $request, $checklistId, $itemId){
        try {
            $checkList = Checklist::find($checklistId);
            $item = Item::find($itemId);
            
            if(!$checkList || !$item || $checkList->id != $item->checklist_id){
                return response()->json([
                    'status' => "404",
                    "error" => "Not Found"
                ], 404);
            }

            $raw = ( empty($request->json()->all()) ? $request->all() : $request->json()->all());
            $data = $raw['data']['attribute'];
            
            $item->description = $data['description'];
            $item->is_completed = $data['is_completed'] ?? false;
            $item->completed_at = ( isset($data['completed_at']) ? Carbon::parse($data['completed_at'])->toDateTimeString() : null);
            $item->due = ( isset($data['due']) ? Carbon::parse($data['due'])->toDateTimeString() : null);
            $item->urgency = $data['urgency'] ?? 0;
            $item->updated_by = Auth::user()->id;
            $item->assignee_id = $data['assignee_id']?? null;
            $item->checklist_id = $checklistId;
            $item->save();

            History::create([
                'loggable_type' => 'items',
                'loggable_id' => $item->id,
                'action' => 'update',
                'kwuid' => Auth::user()->id,
                'value' => $item->toJson()
            ]);

            $results = [
                'data' => [
                    'type' => 'items',
                    'id' => $item->id,
                    'attributes' =>[
                        'description' => $item->description,
                        "is_completed" => $item->is_completed,
                        "due" => ($item->due ? Carbon::parse($item->due)->toW3cString() : null),
                        "urgency" => $item->urgency,
                        "assignee_id" => $item->assignee_id,
                        "completed_at" => ($item->completed_at ? Carbon::parse($item->completed_at)->toW3cString() : null),
                        "updated_by" => $item->updated_by,
                        "updated_at" => Carbon::parse($item->updated_at)->toW3cString(),
                        "created_at" => Carbon::parse($item->created_at)->toW3cString(),
                    ],
                    'links' => [
                        'self' => route('items.one', ['checklistId' => $item->checklist_id , 'itemId' => $item->id ])
                    ],
                ]
            ];
            return response()->json($results, 201);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500', 
                'error' => 'Server Error'
            ], 500);
        }
    }

    public function destroy($checklistId, $itemId){
        $checkList = Checklist::find($checklistId);
        $item = Item::find($itemId);
        
        if(!$checkList || !$item || $checkList->id != $item->checklist_id){
            return response()->json([
                'status' => "404",
                "error" => "Not Found"
            ], 404);
        }

        try {
            $item->delete();

            History::create([
                'loggable_type' => 'items',
                'loggable_id' => $item->id,
                'action' => 'delete',
                'kwuid' => Auth::user()->id,
                'value' => $item->toJson()
            ]);

            return response()->json('', 204);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => "500",
                "error" => "Server Error"
            ], 500);
        }
    }

    public function updatebulk(Request $request, $checklistId){
        $checkList = Checklist::find($checklistId);
        if(!$checkList){
            return response()->json([
                'status' => "404",
                "error" => "Not Found"
            ], 404);
        }
        try {
            $raw = ( empty($request->json()->all()) ? $request->all() : $request->json()->all());
            $datas = $raw['data'];
            $results = [];
            foreach ($datas as $index => $data) {
                $id = $data['id'];
                $action = $data['action'];
                $item = Item::find($id);
                if($item && $action == "update"){
                    $field = $data['attributes'];
                    $item->description = $field['description'];
                    $item->due = $field['due'];
                    $item->urgency = $field['urgency'];
                    $item->save();

                    $results['data'][] = [
                        "id" => $id ,
                        "action" => $action,
                        "status" => 200,
                    ];

                    History::create([
                        'loggable_type' => 'items',
                        'loggable_id' => $item->id,
                        'action' => 'bulk update',
                        'kwuid' => Auth::user()->id,
                        'value' => $item->toJson()
                    ]);
                }
                else{
                    $results['data'][] = [
                        "id" => $id ,
                        "action" => $action,
                        "status" => 404,
                    ];
                }
            }

            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => "500",
                "error" => "Server Error"
            ], 500);
        }
    }

    public function summaries(Request $request){
        if(!$request->has('date')){
            return response()->json([
                'status' => "400",
                "error" => "Some Value Required"
            ], 400);
        }

        try {

            if($request->has('tz')){                
                $date = Carbon::parse(str_replace(' ','+',$request->date), $request->tz);
            } else {
                $date = Carbon::parse(str_replace(' ','+',$request->date));
            }

            $today = ($request->has('object_domain') ? app("App\\Checklist")::where('object_domain' , $request->object_domain)->first()->item() : app("App\\Item"))
                    ->whereDate('completed_at', Carbon::now()->toDateString())->count();
            $past_due = ($request->has('object_domain') ? app("App\\Checklist")::where('object_domain' , $request->object_domain)->first()->item() : app("App\\Item"))
                    ->whereColumn('completed_at', '>', 'due')->count();
            $this_week = ($request->has('object_domain') ? app("App\\Checklist")::where('object_domain' , $request->object_domain)->first()->item() : app("App\\Item"))
                    ->whereBetween('completed_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
            $past_week = ($request->has('object_domain') ? app("App\\Checklist")::where('object_domain' , $request->object_domain)->first()->item() : app("App\\Item"))
                    ->whereBetween('completed_at', [(Carbon::now()->subWeek())->startOfWeek(), (Carbon::now()->subWeek())->endOfWeek()])->count();
            $this_month = ($request->has('object_domain') ? app("App\\Checklist")::where('object_domain' , $request->object_domain)->first()->item() : app("App\\Item"))
                    ->whereBetween('completed_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
            $past_month = ($request->has('object_domain') ? app("App\\Checklist")::where('object_domain' , $request->object_domain)->first()->item() : app("App\\Item"))
                    ->whereBetween('completed_at', [(Carbon::now()->subMonth())->startOfMonth(), (Carbon::now()->subMonth())->endOfMonth()])->count();
            $total = ($request->has('object_domain') ? app("App\\Checklist")::where('object_domain' , $request->object_domain)->first()->item() : app("App\\Item"))
                    ->whereBetween('completed_at', [(Carbon::now()->subMonth())->startOfMonth(), Carbon::now()->endOfMonth()])->count();

            $results = [
                    'data' => [
                        'today' => $today,
                        'past_due' => $past_due,
                        'this_week' => $this_week,
                        'past_week' => $past_week,
                        'this_month' => $this_month,
                        'past_month' => $past_month,
                        'total' => $total,
                    ]
                ];
            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => "500",
                "error" => "Server Error"
            ], 500);
        }
    }

}