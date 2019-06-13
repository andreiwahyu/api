<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\Generator;
use Auth;

use App\Template;
use App\Checklist;
use App\Item;
use App\History;

class TemplateController extends Controller
{
    public function store(Request $request){
        try {            
            $raw = ( empty($request->json()->all()) ? $request->all() : $request->json()->all());
            $data = $raw['data']['attributes'];
            if(empty($data['name'])){
                return response()->json([
                    'status' => "400",
                    "error" => "Some Value Required"
                ], 400);
            }

            $results = [];

            $template = new Template();
            $template->name = $data['name'];
            $template->save();

            $checklist = new Checklist();
            $checklist->description = $data['checklist']['description'];
            $checklist->due_interval = $data['checklist']['due_interval'];
            $checklist->due_unit = $data['checklist']['due_unit'];
            $checklist->template_id = $template->id;
            $checklist->save();

            $results = [
                'data' => [
                    'id' => $template->id,
                    'attributes' => [
                        'name' => $template->name,
                        'checklist' => [
                            'description' => $checklist->description,
                            'due_interval' => $checklist->due_interval,
                            'due_unit' => $checklist->due_unit,
                        ]
                    ]
                ]
            ];

            foreach ($data['items'] as $itemData) {
                $item = new Item();
                $item->description = $itemData['description'];
                $item->urgency = $itemData['urgency'];
                $item->due_interval = $itemData['due_interval'];
                $item->due_unit = $itemData['due_unit'];
                $item->checklist_id = $checklist->id;
                $item->save();

                $results['data']['attributes']['items'][] = [
                    'description' => $item->description,
                    'urgency' => $item->urgency,
                    'due_interval' => $item->due_interval,
                    'due_unit' => $item->due_unit,
                ];
            }

            History::create([
                'loggable_type' => 'template',
                'loggable_id' => $template->id,
                'action' => 'create',
                'kwuid' => Auth::user()->id,
                'value' => $template->toJson()
            ]);

            return response()->json($results, 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500', 
                'error' => 'Server Error'
            ], 500);
        }
    }

    public function getAll(Request $request){
        try {
            $generator = new Generator('Template', $request, route('templates.all'));

            $results = [];
            $results['meta'] = $generator->meta();
            $results['links'] = $generator->paginator();

            foreach ($generator->data() as $template) {
                $results['data'][] = [
                                'name' => $template->name,
                            ];

                    $items = [];
                    foreach ($template->checklist as $checklist) {
                        $results['data'][count($results['data'])-1]['checklist'][] = [
                            'description' => $checklist->description,
                            'due_interval' => $checklist->due_interval,
                            'due_unit' => $checklist->due_unit,
                        ];

                        foreach ($checklist->item as $item) {
                            $items[] = $item;
                        }
                    }

                    foreach ($items as $item) {
                        $results['data'][count($results['data'])-1]['items'][] = [
                            'description' => $item->description,
                            'urgency' => $item->urgency,
                            'due_interval' => $item->due_interval,
                            'due_unit' => $item->due_unit
                        ];
                    }
            }
            dd($results);
            return response()->json($results, 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500', 
                'error' => 'Server Error'
            ], 500);
        }
    }


    public function getOne($templateId){
        try {
            $template = Template::find($templateId);
            if(!$template){
                return response()->json([
                    'status' => "404",
                    "error" => "Not Found"
                ], 404);
            }

            
            $results = [
                'data' => [
                    'type' => 'templates',
                    'id' => $template->id,
                    'attributes' => [
                        'name' => $template->name,
                        'checklist' => [
                            'description' => $template->checklist->first()->description,
                            'due_interval' => $template->checklist->first()->due_interval,
                            'due_unit' => $template->checklist->first()->due_unit,
                        ]
                    ],
                    'links' => [
                        'self' => route('templates.one', ['templateId' => $template->id])
                    ]
                ]
            ];

            foreach ($template->checklist->first()->item as $itemData) {
                $results['data']['attributes']['items'][] = [
                    'urgency' => $itemData['urgency'],
                    'due_unit' => $itemData['due_unit'],
                    'description' => $itemData['description'],
                    'due_interval' => $itemData['due_interval'],
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

    public function update(Request $request, $templateId){
        try {
            $template = Template::find($templateId);
            if(!$template){
                return response()->json([
                    'status' => "404",
                    "error" => "Not Found"
                ], 404);
            }

            $raw = ( empty($request->json()->all()) ? $request->all() : $request->json()->all());
            $data = $raw['data']['attributes'];

            $template->name = $data['name'];
            $template->save();
            
            $checklist = Checklist::find($template->checklist->first()->id);  
            $checklist->description = $data['checklist']['description'];         
            $checklist->due_interval = $data['checklist']['due_interval'];         
            $checklist->due_unit = $data['checklist']['due_unit'];  
            $checklist->save();

            $results = [
                'data' => [
                    'id' => $template->id,
                    'attributes' => [
                        'name' => $template->name,
                        'checklist' => [
                            'description' => $template->checklist->first()->description,
                            'due_interval' => $template->checklist->first()->due_interval,
                            'due_unit' => $template->checklist->first()->due_unit,
                        ]
                    ],
                    'links' => [
                        'self' => route('templates.one', ['templateId' => $template->id])
                    ]
                ]
            ];
            $i = 0;
            foreach ($template->checklist->first()->item as $itemData) {
                if(count($data['items']) <= $i ){
                    continue;
                }
                $item = Item::find($itemData['id']);
                $item->description = $data['items'][$i]['description'];
                $item->urgency = $data['items'][$i]['urgency'];
                $item->due_interval = $data['items'][$i]['due_interval'];
                $item->due_unit = $data['items'][$i]['due_unit'];
                $item->save();
                $i++;
                $results['data']['attributes']['items'][] = [
                    'description' => $item->description,
                    'urgency' => $item->urgency,
                    'due_interval' => $item->due_interval,
                    'due_unit' => $item->due_unit,
                ];
            }

            History::create([
                'loggable_type' => 'template',
                'loggable_id' => $template->id,
                'action' => 'update',
                'kwuid' => Auth::user()->id,
                'value' => $template->toJson()
            ]);

            return response()->json($results, 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => '500', 
                'error' => 'Server Error'
            ], 500);
        }
    }

    public function destroy($templateId){
        try {
            $template = Template::find($templateId);

            if(!$template){
                return response()->json([
                    'status' => "404",
                    "error" => "Not Found"
                ], 404);
            }

            $template->delete();

            History::create([
                'loggable_type' => 'template',
                'loggable_id' => $template->id,
                'action' => 'delete',
                'kwuid' => Auth::user()->id,
                'value' => $template->toJson()
            ]);

            return response()->json('', 204);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => "500",
                "error" => "Server Error"
            ], 500);
        }
    }

    public function assigns(Request $request, $templateId){
        try {
            $template = Template::find($templateId);
            if(!$template){
                return response()->json([
                    'status' => "404",
                    "error" => "Not Found"
                ], 404);
            }
            $raw = ( empty($request->json()->all()) ? $request->all() : $request->json()->all());
            $data = $raw['data'];

            $checklists = [];
            foreach ($data as $checklistData) {
                $checklist = Checklist::where('object_id', $checklistData['attributes']['object_id'])
                            ->where('object_domain', $checklistData['attributes']['object_domain'])->first();
                if(!$checklist){
                    continue;
                }
                
                $checklist->template_id = $templateId;
                $checklist->save();

               $checklists[] = $checklist; 
            }

            $results['meta'] = [
                'count' => count($checklists),
                'total' => count($checklists)
            ];

            $items = [];
            foreach ($checklists as $checklist) {
                $results['data'][] = [
                    'type' => 'checklist',
                    'id' => $checklist->id,
                    'attributes' => [
                        'object_domain' => $checklist->object_domain,
                        'object_id' => $checklist->object_id,
                        'description' => $checklist->description,
                        'is_completed' => $checklist->is_completed,
                        'due' => ($checklist->due ? Carbon::parse($checklist->due)->toW3cString() : null),
                        'urgency' => $checklist->urgency,
                        'completed_at' => ($checklist->completed_at ? Carbon::parse($checklist->completed_at)->toW3cString() : null),
                        'updated_by' => $checklist->updated_by,
                        'created_by' => $checklist->created_by,
                        'created_at' => ($checklist->created_at ? Carbon::parse($checklist->created_at)->toW3cString() : null),
                        'updated_at' => ($checklist->updated_at ? Carbon::parse($checklist->updated_at)->toW3cString() : null),
                    ],
                    'links' => [
                        'self' => route('checklist.one', ['checklistId' => $checklist->id])
                    ],
                    'relationships' => [
                        'items'=> [
                            'links' => [
                                'self'=> route('checklist.one', ['checklistId' => $checklist->id]) . '?include=items',
                                'related'=> route('items.all' , ['checklistId' => $checklist->id])
                            ]
                        ]
                    ]
                ];
                foreach ($checklist->item as $item) {
                    $results['data'][count($results['data'])-1]['relationships']['items']['data'][] = [
                        'type' => 'items',
                        'id' => $item->id,
                    ];
                    $items[] = $item;
                }
            }
            foreach ($items as $item) {
                $results['included'][] = [
                    'type' => 'items',
                    'id' => $item->id,
                    'attributes' => [
                        'description' => $item->description,
                        "is_completed" => $item->is_completed,
                        "completed_at" => ($item->completed_at ? Carbon::parse($item->completed_at)->toW3cString() : null),
                        "due" => ($item->due ? Carbon::parse($item->due)->toW3cString() : null),
                        "urgency" => $item->urgency,
                        "updated_by" => $item->updated_by,
                        "user_id" => $item->updated_by,
                        "checklist_id" => $item->checklist_id,
                        "deleted_at" => null,
                        "updated_at" => Carbon::parse($item->updated_at)->toW3cString(),
                        "created_at" => Carbon::parse($item->created_at)->toW3cString(),
                    ],
                    'links' => [
                        'self' => route('items.one', ['checklistId' => $item->checklist_id , 'itemId' => $item->id ])
                    ]
                ];
            }

            History::create([
                'loggable_type' => 'template',
                'loggable_id' => $template->id,
                'action' => 'asigns',
                'kwuid' => Auth::user()->id,
                'value' => $template->toJson()
            ]);

            return response()->json($results, 201);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => "500",
                "error" => "Server Error"
            ], 500);
        }
    }
}