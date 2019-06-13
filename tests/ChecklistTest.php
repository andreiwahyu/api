<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ChecklistTest extends TestCase
{
    public function testErrorAuthCreate()
    {
        $parameter = [
            "data" => [
                "attributes" => [
                  "object_domain" => "contact",
                  "object_id" => "1",
                  "due" => "2019-01-25T07:50:14+00:00",
                  "urgency" => 1,
                  "description" => "Need to verify this guy house.",
                  "items" => [
                    "Visit his house",
                    "Capture a photo",
                    "Meet him on the house"
                  ],
                  "task_id" => "123"
                ]
              ]
        ];
        $this->post(route('checklist.store'), $parameter, []);
        $this->seeStatusCode(401);
    }

    public function testErrorCreate()
    {
        $user = App\User::find(1);
        $parameter = [
            "foor" => "bar"
        ];        
        $this->actingAs(App\User::first());
        $this->post(route('checklist.store'), $parameter, []);
        $this->seeStatusCode(500);
    }

    public function testSuccessCreate()
    {
        $parameter = [
            "data" => [
                "attributes" => [
                  "object_domain" => "contact",
                  "object_id" => "1",
                  "due" => "2019-01-25T07:50:14+00:00",
                  "urgency" => 1,
                  "description" => "Need to verify this guy house.",
                  "items" => [
                    "Visit his house",
                    "Capture a photo",
                    "Meet him on the house"
                  ],
                  "task_id" => "123"
                ]
              ]
        ];        
        $this->actingAs(App\User::first());
        $this->post(route('checklist.store'), $parameter, []);
        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'object_domain', "object_id", "due", "urgency", "description", "task_id"
                ]
            ]
         ]);
    }

    public function testErrorAuthUpdate(){
        $parameter = [
            "data" => [
                "type" => "checklists",
                "id" => 1,
                "attributes" => [
                    "object_domain" => "contact",
                    "object_id"=> "1",
                    "description"=> "Need to verify this guy house.",
                    "is_completed"=> false,
                    "completed_at"=> null,
                    "created_at"=> "2018-01-25T07:50:14+00:00"
                ]
            ]
        ]; 
        $checklist = App\Checklist::first();
        $this->patch(route('checklist.update' , ['id' => $checklist->id]), $parameter, []);
        $this->seeStatusCode(401);
    }

    public function testErrorNotFoundUpdate(){
        $parameter = [
            "data" => [
                "type" => "checklists",
                "id" => 1,
                "attributes" => [
                    "object_domain" => "contact",
                    "object_id"=> "1",
                    "description"=> "Need to verify this guy house.",
                    "is_completed"=> false,
                    "completed_at"=> null,
                    "created_at"=> "2018-01-25T07:50:14+00:00"
                ]
            ]
        ]; 
        $this->patch(route('checklist.update' , ['checklistId' => 9999]), $parameter, []);
        $this->seeStatusCode(401);
    }

    public function testErrorValuesRequiredUpdate(){
        $parameter = [
            "data" => [
                "type" => "checklists",
                "id" => 1,
                "attributes" => [
                    "object_domain" => "contact",
                    "description"=> "Need to verify this guy house.",
                    "is_completed"=> false,
                    "completed_at"=> null,
                    "created_at"=> "2018-01-25T07:50:14+00:00"
                ]
            ]
        ]; 

        $checklist = App\Checklist::first();
        $this->actingAs(App\User::first());
        $this->patch(route('checklist.update' , ['checklistId' => $checklist->id]), $parameter, []);
        $this->seeStatusCode(400);
    }

    public function testErrorUpdate(){
        $parameter = [
            "foor" => "bar"
        ]; 
        $checklist = App\Checklist::first();
        $this->actingAs(App\User::first());
        $this->patch(route('checklist.update' , ['checklistId' => $checklist->id]), $parameter, []);
        $this->seeStatusCode(500);
    }

    public function testSuccessUpdate(){
        $parameter = [
            "data" => [
                "type" => "checklists",
                "id" => 1,
                "attributes" => [
                    "object_domain" => "contact",
                    "object_id" => "1",
                    "description"=> "Need to verify this guy house.",
                    "is_completed"=> false,
                    "completed_at"=> null,
                    "created_at"=> "2018-01-25T07:50:14+00:00"
                ]
            ]
        ]; 
        $checklist = App\Checklist::first();
        $this->actingAs(App\User::first())->patch(route('checklist.update' , ['checklistId' => $checklist->id]), $parameter, []);
        $this->seeStatusCode(201);
    }

    public function testErrorAuthDelete() {
        $checklist = App\Checklist::first();
        $this->delete(route('checklist.update' , ['checklistId' => $checklist->id]));
        $this->seeStatusCode(401);
    }

    public function testErrorNotFoundDelete() {
        $this->actingAs(App\User::first());
        $this->delete(route('checklist.update' , ['checklistId' =>9999]));
        $this->seeStatusCode(404);
    }

    public function testErrorDelete() {
        $checklist = App\Checklist::first();
        $this->actingAs(App\User::first());
        $this->delete(route('checklist.update' , ['id' => $checklist->id]));
        $this->seeStatusCode(500);
    }

    public function testSuccessDelete() {
        $checklist = App\Checklist::first();
        $this->actingAs(App\User::first());
        $this->delete(route('checklist.update' , ['checklistId' => $checklist->id]));
        $this->seeStatusCode(204);
    }

    public function testErrorAuthSingleChecklist()
    {
        $checklist = App\Checklist::first();
        $this->get(route('checklist.one', ['checklistId' => $checklist->id]));
        $this->seeStatusCode(401);
    }

    public function testErrorNotFoundSingleChecklist()
    {
        $this->actingAs(App\User::first());
        $this->get(route('checklist.one', ['checklistId' => 9999]));
        $this->seeStatusCode(404);
    }

    public function testErrorSingleChecklist()
    {
        $this->actingAs(App\User::first());
        $this->get(route('checklist.one', ['id' => 9999]));
        $this->seeStatusCode(500);
    }

    public function testSuccessSingleChecklist()
    {
        $checklist = App\Checklist::first();
        $this->actingAs(App\User::first());
        $this->get(route('checklist.one', ['checklistId' => $checklist->id]));
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                'type','id',
                'attributes' => [
                    "object_domain", "object_id", "description","is_completed", "due",
                    "urgency","completed_at", "last_update_by", "update_at","created_at"
                ]
            ]
         ]);
    }

    public function testErrorAuthChecklist()
    {
        $this->get(route('checklist.all'));
        $this->seeStatusCode(401);
    }

    public function testSuccessChecklist()
    {
        $this->actingAs(App\User::first());
        $this->get(route('checklist.all'));
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'meta' => [
                'count',
                'total'
            ],
            'data' => [
                '*' => [
                    'type','id',
                    'attributes' => [
                        "object_domain", "object_id", "description","is_completed", "due", "task_id",
                        "urgency","completed_at", "last_update_by", "update_at","created_at"
                    ]
                ]
            ],
            'links' => [
                'first', 'last', 'next', 'prev'
            ]
         ]);
    }

    public function testSuccessChecklistWithItems()
    {
        $this->actingAs(App\User::first());
        $this->get(route('checklist.all', ['include' => 'items']));
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'meta' => [
                'count',
                'total'
            ],
            'data' => [
                '*' => [
                    'type','id',
                    'attributes' => [
                        "object_domain", "object_id", "description","is_completed", "due", "task_id",
                        "urgency","completed_at", "last_update_by", "update_at","created_at",'items'
                    ]
                ]
            ],
            'links' => [
                'first', 'last', 'next', 'prev'
            ]
         ]);
    }

}
