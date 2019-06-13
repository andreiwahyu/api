<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ItemsTest extends TestCase
{
    public function testErrorAuthComplete(){
        $parameter = [
            "data" => [
                [
                    "item_id" => App\Item::get()->random()->id
                ],
                [
                    "item_id" => App\Item::get()->random()->id
                ],[
                    "item_id" => App\Item::get()->random()->id
                ],
                [
                    "item_id" => App\Item::get()->random()->id
                ]
              ]
        ];
        $this->post(route('items.complete'), $parameter, []);
        $this->seeStatusCode(401);
    }

    public function testErrorComplete(){
        $parameter = [
            "foo" => 'bar'
        ];
        $this->actingAs(App\User::first());
        $this->post(route('items.complete'), $parameter, []);
        $this->seeStatusCode(500);
    }

    public function testSuccessComplete(){
        $parameter = [
            "data" => [
                [
                    "item_id" => App\Checklist::first()->item->first()->id
                ]
              ]
        ];
        $this->actingAs(App\User::first());
        $this->post(route('items.complete'), $parameter, []);
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                    '*' =>[
                        'id', 'item_id', 'is_completed', 'checklist_id'
                    ],
                ]
            ]
        );
    }

    public function testErrorAuthIncomplete(){
        $parameter = [
            "data" => [
                [
                    "item_id" => App\Item::get()->random()->id
                ],
                [
                    "item_id" => App\Item::get()->random()->id
                ],[
                    "item_id" => App\Item::get()->random()->id
                ],
                [
                    "item_id" => App\Item::get()->random()->id
                ]
              ]
        ];
        $this->post(route('items.incomplete'), $parameter, []);
        $this->seeStatusCode(401);
    }

    public function testErrorIncomplete(){
        $parameter = [
            "foo" => 'bar'
        ];
        $this->actingAs(App\User::first());
        $this->post(route('items.incomplete'), $parameter, []);
        $this->seeStatusCode(500);
    }

    public function testSuccessInomplete(){
        $parameter = [
            "data" => [
                [
                    "item_id" => App\Checklist::first()->item->first()->id
                ]
              ]
        ];
        $this->actingAs(App\User::first());
        $this->post(route('items.incomplete'), $parameter, []);
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                    '*' =>[
                        'id', 'item_id', 'is_completed', 'checklist_id'
                    ],
                ]
            ]
        );
    }

    public function testErrorauthItemList(){
        $this->get(route('items.all', ['checklistId' => App\Checklist::first()->id ]));
        $this->seeStatusCode(401);
    }

    public function testSuccessItemList(){
        $this->actingAs(App\User::first());
        $this->get(route('items.all', ['checklistId' => App\Checklist::first()->id ]));
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            'data' => [
                    'type', 'id', 
                    'attributes' => [
                        "object_domain","object_id","description","is_completed","due",
                        "urgency","completed_at","last_update_by","update_at","created_at",
                        "items" => [
                                '*' => [
                                    "id","description","is_completed","due","urgency","checklist_id",
                                    "assignee_id","checklist_id","completed_at","updated_by","updated_at","created_at"
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    public function testErrorAuthCreate(){
        $parameter = [
            "data" =>  [
              "attribute" => [
                "description" => "Need to verify this guy house.",
                "due" => "2019-01-19 18:34:51",
                "urgency" => "2",
                "assignee_id"=> 123
              ]
            ]
        ];
        $this->post(route('items.store', ['checklistId' => App\Checklist::first()->id]), $parameter, []);
        $this->seeStatusCode(401);
    }


    public function testErrorCreate(){
        $parameter = [
            "foo" => 'bar'
        ];
        $this->actingAs(App\User::first());
        $this->post(route('items.store', ['checklistId' => App\Checklist::first()->id]), $parameter, []);
        $this->seeStatusCode(500);
    }

    public function testErrorNotFoundCreate(){
        $parameter = [
            "data" =>  [
              "attribute" => [
                "description" => "Need to verify this guy house.",
                "due" => "2019-01-19 18:34:51",
                "urgency" => "2",
                "assignee_id"=> 123
              ]
            ]
        ];
        $this->actingAs(App\User::first());
        $this->post(route('items.store', ['checklistId' => 9999]), $parameter, []);
        $this->seeStatusCode(404);
    }


    public function testErrorValueRequiredCreate(){
        $parameter = [
            "data" =>  [
              "attribute" => [
                "due" => "2019-01-19 18:34:51",
                "urgency" => "2",
                "assignee_id"=> 123
              ]
            ]
        ];
        $this->actingAs(App\User::first());
        $this->post(route('items.store', ['checklistId' => App\Checklist::first()->id]), $parameter, []);
        $this->seeStatusCode(400);
    }

    public function testSuccessCreate(){
        $parameter = [
            "data" =>  [
              "attribute" => [
                "description" => "Need to verify this guy house.",
                "due" => "2019-01-19 18:34:51",
                "urgency" => "2",
                "assignee_id"=> 123
              ]
            ]
        ];
        $this->actingAs(App\User::first());
        $this->post(route('items.store', ['checklistId' =>  App\Checklist::first()->id]), $parameter, []);
        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            "data" => [
              "type","id","attributes" => [
                "description","is_completed","completed_at","due","urgency",
                "updated_by","updated_at","created_at"
              ],
              "links" => [
                  'self'
              ]
            ]
        ]);
    }

    public function testErrorAuthOneItem(){
        $this->get(route('items.one', ['checklistId' => App\Checklist::first()->id , 'itemId' => App\Checklist::first()->item->first()->id ]));
        $this->seeStatusCode(401);
    }

    public function testErrorNotFoundOneItem(){
        $this->actingAs(App\User::first());
        $this->get(route('items.one', ['checklistId' => 99999 , 'itemId' => App\Checklist::first()->item->first()->id ]));
        $this->seeStatusCode(404);
    }

    public function testSuccesOneItem(){
        $this->actingAs(App\User::first());
        $this->get(route('items.one', ['checklistId' => App\Checklist::first()->id , 'itemId' => App\Checklist::first()->item->first()->id ]));
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            "data" => [
              "type","id","attributes" => [
                "description","is_completed","completed_at","due","urgency","updated_by",
                "created_by","checklist_id","assignee_id","task_id","deleted_at","created_at",
                "updated_at",
              ],
              "links" => [
                  'self'
              ]
            ]
        ]);
    }

    public function testErrorAuthUpdate(){
        $parameter = [
            "data" => [
              "attribute" => [
                "description" => "Need to verify this guy house.",
                "due" => "2019-01-19 18:34:51",
                "urgency" => "2",
                "assignee_id" => 123
              ]
            ]
        ];
        $this->patch(route('items.update', ['checklistId' => App\Checklist::first()->id , 'itemId' => App\Checklist::first()->item->first()->id ]), $parameter, []);
        $this->seeStatusCode(401);
    }

    public function testErrorNotFoundUpdate(){
        $parameter = [
            "data" => [
              "attribute" => [
                "description" => "Need to verify this guy house.",
                "due" => "2019-01-19 18:34:51",
                "urgency" => "2",
                "assignee_id" => 123
              ]
            ]
        ];
        $this->actingAs(App\User::first());
        $this->patch(route('items.update', ['checklistId' => 9999 , 'itemId' => 9999 ]), $parameter, []);
        $this->seeStatusCode(404);
    }

    public function testErrorUpdate(){
        $parameter = [
            "foo" => "bar"
        ];
        $this->actingAs(App\User::first());
        $this->patch(route('items.update', ['checklistId' => App\Checklist::first()->id , 'itemId' => App\Checklist::first()->item->first()->id ]), $parameter, []);
        $this->seeStatusCode(500);
    }

    public function testSuccessUpdate(){
        $parameter = [
            "data" => [
              "attribute" => [
                "description" => "Need to verify this guy house.",
                "due" => "2019-01-19 18:34:51",
                "urgency" => "2",
                "assignee_id" => 123
              ]
            ]
        ];
        $this->actingAs(App\User::first());
        $this->patch(route('items.update', ['checklistId' => App\Checklist::first()->id , 'itemId' => App\Checklist::first()->item->first()->id ]), $parameter, []);
        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            "data" => [
              "type","id","attributes" => [
                "description","is_completed","completed_at","due","urgency","assignee_id",
                "completed_at","updated_by","updated_at","created_at"
              ],
              "links" => [
                  'self'
              ]
            ]
        ]);
    }

    public function testErrorAuthDelete(){
        $this->delete(route('items.destroy', ['checklistId' => App\Checklist::first()->id , 'itemId' => App\Checklist::first()->item->first()->id ]));
        $this->seeStatusCode(401);
    }

    public function testErrorNotFoundDelete(){
        $this->actingAs(App\User::first());
        $this->delete(route('items.destroy', ['checklistId' => 99999 , 'itemId' => 999999 ]));
        $this->seeStatusCode(404);
    }

    public function testSuccessDelete(){
        $this->actingAs(App\User::first());        
        $this->delete(route('items.destroy', ['checklistId' => App\Checklist::first()->id , 'itemId' => App\Checklist::first()->item->first()->id ]));
        $this->seeStatusCode(204);
    }

    public function testErrorAuthBulk(){
        $parameter = [
            "data" => [
              [
                "id" => "64",
                "action" => "update",
                "attributes" => [
                  "description" => "tes",
                  "due" => "2019-01-19 18:34:51",
                  "urgency" => "2"
                ]
              ],
              [
                "id" => "205",
                "action" => "update",
                "attributes" => [
                  "description" => "{{data.attributes.description}}",
                  "due" => "2019-01-19 18:34:51",
                  "urgency" => "2"
                ]
              ]
            ]
        ];
        $this->post(route('items.bulk', ['checklistId' => App\Checklist::first()->id]), $parameter, []);
        $this->seeStatusCode(401);
    }

    public function testErrorNotFoundBulk(){
        $parameter = [
            "data" => [
              [
                "id" => "64",
                "action" => "update",
                "attributes" => [
                  "description" => "tes",
                  "due" => "2019-01-19 18:34:51",
                  "urgency" => "2"
                ]
              ],
              [
                "id" => "205",
                "action" => "update",
                "attributes" => [
                  "description" => "{{data.attributes.description}}",
                  "due" => "2019-01-19 18:34:51",
                  "urgency" => "2"
                ]
              ]
            ]
        ];
        $this->actingAs(App\User::first());        
        $this->post(route('items.bulk', ['checklistId' => 9999]), $parameter, []);
        $this->seeStatusCode(404);
    }


    public function testErrorBulk(){
        $parameter = [
            "foo" => "bar"
        ];
        $this->actingAs(App\User::first());        
        $this->post(route('items.bulk', ['checklistId' => App\Checklist::first()->id]), $parameter, []);
        $this->seeStatusCode(500);
    }

    public function testsuccessBulk(){
        $parameter = [
            "data" => [
              [
                "id" => "64",
                "action" => "update",
                "attributes" => [
                  "description" => "tes",
                  "due" => "2019-01-19 18:34:51",
                  "urgency" => "2"
                ]
              ],
              [
                "id" => "205",
                "action" => "update",
                "attributes" => [
                  "description" => "{{data.attributes.description}}",
                  "due" => "2019-01-19 18:34:51",
                  "urgency" => "2"
                ]
              ]
            ]
        ];
        $this->actingAs(App\User::first());        
        $this->post(route('items.bulk', ['checklistId' => App\Checklist::first()->id]), $parameter, []);
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            "data" => [
                "*" => [
                    'id', 'action', 'status' 
                ]
            ]
        ]);
    }

    public function testErrorAuthSummaries(){
        $this->get(route('items.summaries'));
        $this->seeStatusCode(401);
    }

    public function testErrorValueRequiredSummaries(){
        $this->actingAs(App\User::first());        
        $this->get(route('items.summaries'));
        $this->seeStatusCode(400);
    }

    public function testSuccessSummaries(){
        $this->actingAs(App\User::first());        
        $this->get(route('items.summaries', ['date' => '2018-01-25T07:50:14+00:00']));
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            "data" => [
                "today","past_due","this_week","past_week",
                "this_month","past_month","total"
            ]
        ]);
    }

}
