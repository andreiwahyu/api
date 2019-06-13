<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\Generator;

use Auth;
use App\Checklist;
use App\Item;
use App\History;


class ChecklistController extends Controller
{
    public function getAll(Request $request){
        try {
            $generator = new Generator('Checklist', $request, route('checklist.all'));

            $results = [];
            $results['meta'] = $generator->meta();
            $results['links'] = $generator->paginator();
            
            foreach ($generator->data() as $checkList) {
                $results['data'][] = [
                                'type' => 'checklists',
                                'id' => $checkList->id,
                                'attributes' =>[
                                    'object_domain' =>  $checkList->object_domain,
                                    'object_id' =>  $checkList->object_id,
                                    'description' => $checkList->description,
                                    "is_completed" => $checkList->is_completed,
                                    "due" => ($checkList->due ? Carbon::parse($checkList->due)->toW3cString() : null),
                                    "task_id" => $checkList->template_id,
                                    "urgency" => $checkList->urgency,
                                    "completed_at" => ($checkList->completed_at ? Carbon::parse($checkList->completed_at)->toW3cString() : null),
                                    "last_update_by" => $checkList->updated_by,
                                    "created_by" => $checkList->created_by,
                                    "created_at" => Carbon::parse($checkList->created_at)->toW3cString(),
                                    "update_at" => Carbon::parse($checkList->updated_at)->toW3cString()
                                ],
                                'links' => [
                                    'self' => route('checklist.one', ['checklistId' => $checkList->id])
                                ],
                            ];

                    if ($request->has('include')) {
                        $items = [];
                        foreach ($checkList->item as $item) {
                            $items[] = $item;
                        }
                        $results['data'][count($results['data'])-1]['attributes']['items'] = $items;
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

    public function getOne(Request $request, $id){
        try {
            $checkList = Checklist::find($id);

            if(!$checkList){
                return response()->json([
                    'status' => "404",
                    "error" => "Not Found"
                ], 404);
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
                        "due" => ($checkList->due ? Carbon::parse($checkList->due)->toW3cString() : null),
                        "urgency" => $checkList->urgency,
                        "completed_at" => ($checkList->completed_at ? Carbon::parse($checkList->completed_at)->toW3cString() : null),
                        "last_update_by" => $checkList->updated_by,
                        "created_by" => $checkList->created_by,
                        "created_at" => Carbon::parse($checkList->created_at)->toW3cString(),
                        "update_at" => Carbon::parse($checkList->updated_at)->toW3cString()
                    ],
                    'links' => [
                        'self' => route('checklist.one', ['checklistId' => $checkList->id])
                    ],
                ]
            ];

            if ($request->has('include')) {
                $items = [];
                foreach ($checkList->item as $item) {
                    $items[] = $item;
                }

                $results['data']['attributes']['items'] = $items;

            }
            return response()->json($results, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500', 
                'error' => 'Server Error'
            ], 500);
        }
    }

    public function store(Request $request){
        try {
            $raw = ( empty($request->json()->all()) ? $request->all() : $request->json()->all());
            $data = $raw['data']['attributes'];
            if(empty($data['object_domain']) || empty($data['object_id']) || empty($data['description']) || empty($data['items'])){
                return response()->json([
                    'status' => "400",
                    "error" => "Some Value Required"
                ], 400);
            }
            $checkList = new Checklist();
            $checkList->object_domain = $data['object_domain'];
            $checkList->object_id = $data['object_id'];
            $checkList->description = $data['description'];
            $checkList->is_completed = $data['is_completed'] ?? false;
            $checkList->completed_at = ( isset($data['completed_at']) ? Carbon::parse($data['completed_at'])->toDateTimeString() : null);
            $checkList->updated_by = $data['updated_by'] ?? 0;
            $checkList->due = ( isset($data['due']) ? Carbon::parse($data['due'])->toDateTimeString() : null);
            $checkList->urgency = $data['urgency'] ?? 0;
            $checkList->template_id = $data['task_id']?? null;
            $checkList->save();

            History::create([
                'loggable_type' => 'Checklist',
                'loggable_id' => $checkList->id,
                'action' => 'create',
                'kwuid' => Auth::user()->id,
                'value' => $checkList->toJson()
            ]);
            
            if(isset($data['items'])){
                foreach ($data['items'] as $itemDescription ) {
                    $item = new Item();
                    $item->checklist_id = $checkList->id;
                    $item->description = $itemDescription;
                    $item->save();
    
                    History::create([
                        'loggable_type' => 'items',
                        'loggable_id' => $item->id,
                        'action' => 'create',
                        'kwuid' => Auth::user()->id,
                        'value' => $item->toJson()
                    ]);
                }
            }
    
            $results = [
                'data' => [
                    'type' => 'checklists',
                    'id' => $checkList->id,
                    'attributes' =>[
                        'object_domain' =>  $checkList->object_domain,
                        'object_id' =>  $checkList->object_id,
                        'task_id' => $checkList->template_id,
                        'description' => $checkList->description,
                        "is_completed" => $checkList->is_completed,
                        "due" => ($checkList->due ? Carbon::parse($checkList->due)->toW3cString() : null),
                        "urgency" => $checkList->urgency,
                        "completed_at" => ($checkList->completed_at ? Carbon::parse($checkList->completed_at)->toW3cString() : null),
                        "updated_by" => $checkList->updated_by,
                        "created_by" => $checkList->created_by,
                        "created_at" => Carbon::parse($checkList->created_at)->toW3cString(),
                        "updated_at" => Carbon::parse($checkList->updated_at)->toW3cString()
                    ],
                    'links' => [
                        'self' => route('checklist.one', ['checklistId' => $checkList->id])
                    ],
                ]
            ];
            return response()->json($results, 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => "500",
                "error" => "Server Error"
            ], 500);
        }
    }

    public function update(Request $request, $id){
        try {            
            $raw = ( empty($request->json()->all()) ? $request->all() : $request->json()->all());
            $data = $raw['data']['attributes'];
            if(empty($data['object_domain']) || empty($data['object_id']) || empty($data['description']) 
                || empty($raw['data']['id']) || empty($raw['data']['type']) ){
                return response()->json([
                    'status' => "400",
                    "error" => "Some Value Required"
                ], 400);
            }
            $checkList = Checklist::find($id);
            if(!$checkList){
                return response()->json([
                    'status' => "404",
                    "error" => "Not Found"
                ], 404);
            }
            $checkList->object_domain = $data['object_domain'] ;
            $checkList->object_id = $data['object_id'] ;
            $checkList->description = $data['description'] ;
            $checkList->is_completed = $data['is_completed'] ?? false;
            $checkList->completed_at = ( isset($data['completed_at']) ? Carbon::parse($data['completed_at'])->toDateTimeString() : null);
            $checkList->updated_by = Auth::user()->id;
            $checkList->due = ( isset($data['due']) ? Carbon::parse($data['due'])->toDateTimeString() : null);
            $checkList->urgency = $data['urgency'] ?? 0;
            $checkList->template_id = $data['task_id']?? null;
            $checkList->save();

            History::create([
                'loggable_type' => 'checklist',
                'loggable_id' => $checkList->id,
                'action' => 'update',
                'kwuid' => Auth::user()->id,
                'value' => $checkList->toJson()
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => "500",
                "error" => "Server Error"
            ], 500);
        }

        $results = [
            'data' => [
                'type' => 'checklists',
                'id' => $checkList->id,
                'attributes' =>[
                    'object_domain' =>  $checkList->object_domain,
                    'object_id' =>  $checkList->object_id,
                    'task_id' => $checkList->template_id,
                    'description' => $checkList->description,
                    "is_completed" => $checkList->is_completed,
                    "due" => ($checkList->due ? Carbon::parse($checkList->due)->toW3cString() : null),
                    "urgency" => $checkList->urgency,
                    "completed_at" => ($checkList->completed_at ? Carbon::parse($checkList->completed_at)->toW3cString() : null),
                    "updated_by" => $checkList->updated_by,
                    "created_by" => $checkList->created_by,
                    "created_at" => Carbon::parse($checkList->created_at)->toW3cString(),
                    "updated_at" => Carbon::parse($checkList->updated_at)->toW3cString()
                ],
                'links' => [
                    'self' => route('checklist.one', ['checklistId' => $checkList->id])
                ],
            ]
        ];
        return response()->json($results, 201);
    }

    public function destroy($id){
        $checkList = Checklist::find($id);

        if(!$checkList){
            return response()->json([
                'status' => "404",
                "error" => "Not Found"
            ], 404);
        }

        try {
            $checkList->delete();

            History::create([
                'loggable_type' => 'checklist',
                'loggable_id' => $checkList->id,
                'action' => 'delete',
                'kwuid' => Auth::user()->id,
                'value' => $checkList->toJson()
            ]);
            return response()->json('', 204);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => "500",
                "error" => "Server Error"
            ], 500);
        }
    }
}
