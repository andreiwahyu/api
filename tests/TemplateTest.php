<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TemplateTest extends TestCase
{
    public function testErrorAuthCreate(){
        $parameter = [
            "data" =>  [
                "attributes" => [
                "name" => "foo template",
                "checklist" => [
                    "description" => "my checklist",
                    "due_interval" => 3,
                    "due_unit" => "hour"
                ],
                "items" => [
                    [
                        "description"=> "my foo item",
                        "urgency" => 2,
                        "due_interval" => 40,
                        "due_unit" =>  "minute"
                    ],
                    [
                        "description" => "my bar item",
                        "urgency" => 3,
                        "due_interval" => 30,
                        "due_unit" => "minute"
                    ]
                ]
                ]
            ]
        ];  
        $this->post(route('templates.store'), $parameter, []);
        $this->seeStatusCode(401);
    }

    public function testErrorCreate(){
        $parameter = [
            "forr" =>  "bar"
        ];  
        $this->actingAs(App\User::first());
        $this->post(route('templates.store'), $parameter, []);
        $this->seeStatusCode(500);
    }

    public function testErrorValueRequiredCreate(){
        $parameter = [
            "data" =>  [
                "attributes" => [
                "checklist" => [
                    "description" => "my checklist",
                    "due_interval" => 3,
                    "due_unit" => "hour"
                ],
                "items" => [
                    [
                        "description"=> "my foo item",
                        "urgency" => 2,
                        "due_interval" => 40,
                        "due_unit" =>  "minute"
                    ],
                    [
                        "description" => "my bar item",
                        "urgency" => 3,
                        "due_interval" => 30,
                        "due_unit" => "minute"
                    ]
                ]
                ]
            ]
        ];  
        $this->actingAs(App\User::first());
        $this->post(route('templates.store'), $parameter, []);
        $this->seeStatusCode(400);
    }


    public function testSuccessCreate(){
        $parameter = [
            "data" =>  [
                "attributes" => [
                "name" => "foo template",
                "checklist" => [
                    "description" => "my checklist",
                    "due_interval" => 3,
                    "due_unit" => "hour"
                ],
                "items" => [
                    [
                        "description"=> "my foo item",
                        "urgency" => 2,
                        "due_interval" => 40,
                        "due_unit" =>  "minute"
                    ],
                    [
                        "description" => "my bar item",
                        "urgency" => 3,
                        "due_interval" => 30,
                        "due_unit" => "minute"
                    ]
                ]
                ]
            ]
        ];  
        $this->actingAs(App\User::first());
        $this->post(route('templates.store'), $parameter, []);
        $this->seeStatusCode(201);
        $this->seeJsonStructure([
                "data" => [
                  "id","attributes" => [
                    "name",
                    "checklist" => [
                        "description", "due_interval", "due_unit"
                    ],
                    "items" => [
                        '*' => [
                            'description', 'urgency', 'due_interval', 'due_unit'
                        ]
                    ]
                  ]
                ] 
         ]);
    }

    public function testErrorAuthTemplate(){
        $this->get(route('templates.one', ['templateId' => App\Template::get()->random()->id]));
        $this->seeStatusCode(401);
    }

    public function testErrorTemplate(){
        $this->actingAs(App\User::first());
        $this->get(route('templates.one', ['id' => App\Template::get()->random()->id]));
        $this->seeStatusCode(500);
    }

    public function testSuccessTemplate(){
        $this->actingAs(App\User::first());
        $this->get(route('templates.one', ['templateId' => App\Template::get()->random()->id]));
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
            "data" => [
              "id","attributes" => [
                "name",
                "checklist" => [
                    "description", "due_interval", "due_unit"
                ],
                "items" => [
                    '*' => [
                        'description', 'urgency', 'due_interval', 'due_unit'
                    ]
                ]
                ],
                'links' => [
                    'self'
                ]
            ] 
        ]);

    }

    public function testErrorAuthUpdate(){
        $parameter = [
            "data" =>  [
                "attributes" => [
                "name" => "foo template",
                "checklist" => [
                    "description" => "my checklist",
                    "due_interval" => 3,
                    "due_unit" => "hour"
                ],
                "items" => [
                    [
                        "description"=> "my foo item",
                        "urgency" => 2,
                        "due_interval" => 40,
                        "due_unit" =>  "minute"
                    ],
                    [
                        "description" => "my bar item",
                        "urgency" => 3,
                        "due_interval" => 30,
                        "due_unit" => "minute"
                    ]
                ]
                ]
            ]
        ];
        $this->patch(route('templates.update', ['templateId' => App\Template::get()->random()->id]), $parameter, []);
        $this->seeStatusCode(401);
    }

    public function testErrorNotFoundUpdate(){
        $parameter = [
            "data" =>  [
                "attributes" => [
                "name" => "foo template",
                "checklist" => [
                    "description" => "my checklist",
                    "due_interval" => 3,
                    "due_unit" => "hour"
                ],
                "items" => [
                    [
                        "description"=> "my foo item",
                        "urgency" => 2,
                        "due_interval" => 40,
                        "due_unit" =>  "minute"
                    ],
                    [
                        "description" => "my bar item",
                        "urgency" => 3,
                        "due_interval" => 30,
                        "due_unit" => "minute"
                    ]
                ]
                ]
            ]
        ];
        $this->actingAs(App\User::first());
        $this->patch(route('templates.update', ['templateId' => 9999]));
        $this->seeStatusCode(404);
    }

    public function testErrorUpdate(){
        $parameter = [
            'foo' => 'bar'
        ];
        $this->actingAs(App\User::first());
        $this->patch(route('templates.update', ['templateId' => App\Template::get()->random()->id]), $parameter, []);
        $this->seeStatusCode(500);
    }

    public function testUpdate(){
        $parameter = [
            "data" =>  [
                "attributes" => [
                "name" => "hai template",
                "checklist" => [
                    "description" => "my checklist",
                    "due_interval" => 3,
                    "due_unit" => "hour"
                ],
                "items" => [
                    [
                        "description"=> "my foo item",
                        "urgency" => 2,
                        "due_interval" => 40,
                        "due_unit" =>  "minute"
                    ],
                    [
                        "description" => "my bar item",
                        "urgency" => 3,
                        "due_interval" => 30,
                        "due_unit" => "minute"
                    ]
                ]
                ]
            ]
        ];
        $this->actingAs(App\User::first());
        $this->patch(route('templates.update', ['templateId' => App\Template::get()->random()->id]), $parameter, []);
        $this->seeStatusCode(201);
        $this->seeJsonStructure([
            "data" => [
              "id","attributes" => [
                "name",
                "checklist" => [
                    "description", "due_interval", "due_unit"
                ],
                "items" => [
                    '*' => [
                        'description', 'urgency', 'due_interval', 'due_unit'
                    ]
                ]
              ]
            ] 
        ]);
    }

    public function testErrorAuthDelete() {
        $template = App\Template::first();
        $this->delete(route('templates.destroy' , ['templateId' => $template->id]));
        $this->seeStatusCode(401);
    }

    public function testErrorNotFoundDelete() {
        $this->actingAs(App\User::first());
        $this->delete(route('templates.destroy' , ['templateId' =>9999]));
        $this->seeStatusCode(404);
    }

    public function testErrorDelete() {
        $template = App\Template::first();
        $this->actingAs(App\User::first());
        $this->delete(route('templates.destroy' , ['id' => $template->id]));
        $this->seeStatusCode(500);
    }

    public function testSuccessDelete() {
        $template = App\Template::first();
        $this->actingAs(App\User::first());
        $this->delete(route('templates.destroy' , ['templateId' => $template->id]));
        $this->seeStatusCode(204);
    }

    public function testErrorAuthAssigns(){
        $parameter = [
            "data" => [
                "attributes" => [
                    "object_id" => 1,
                    "object_domain" => "deals"
                    ]
                ,
                [
                "attributes" => [
                    "object_id" => 2,
                    "object_domain" => "deals"
                    ]
                ],
                [
                "attributes" => [
                    "object_id" => 3,
                    "object_domain" => "deals"
                    ]
                ]
                
            ]
        ];
        $template = App\Template::first();
        $this->post(route('templates.assigns' , ['templateId' => $template->id]), $parameter, []);
        $this->seeStatusCode(401);        
    }

    public function testErrorNotFoundAssigns(){
        $parameter = [
            "data" => [
                "attributes" => [
                    "object_id" => 1,
                    "object_domain" => "deals"
                    ]
                ,
                [
                "attributes" => [
                    "object_id" => 2,
                    "object_domain" => "deals"
                    ]
                ],
                [
                "attributes" => [
                    "object_id" => 3,
                    "object_domain" => "deals"
                    ]
                ]
                
            ]
        ];
        $this->actingAs(App\User::first());
        $this->post(route('templates.assigns' , ['templateId' => 9999]), $parameter, []);
        $this->seeStatusCode(404);        
    }

    public function testErrorAssigns(){
        $parameter = [
            "data" => "asdads"
        ];
        $template = App\Template::first();
        $this->actingAs(App\User::first());
        $this->post(route('templates.assigns' , ['templateId' => $template->id]), $parameter, []);
        $this->seeStatusCode(500);        
    }

    public function testSuccessAssigns(){
        $template = App\Template::first();

        $parameter = [
            "data" => [
                [
                    "attributes" => [
                        "object_id" => $template->checklist->first()->object_id,
                        "object_domain" => $template->checklist->first()->object_domain
                    ]
                ],
                [
                    'attributes' => [
                        "object_id" => $template->checklist->first()->object_id,
                        "object_domain" => $template->checklist->first()->object_domain
                    ]
                ],
            ]
        ];
        $this->actingAs(App\User::first());
        $this->post(route('templates.assigns' , ['templateId' => $template->id]), $parameter, []);
        $this->seeStatusCode(201);        
        
        $this->seeJsonStructure([
            "meta" => [
                "count", "total"
            ],
            "data" => [
                '*' => [
                    "type","id","attributes" => [
                            "object_domain","object_id","description","is_completed","due",
                            "urgency","completed_at","updated_by","created_by",
                            "created_at","updated_at"
                        ],
                        "links" => [
                            "self"
                        ],
                        "relationships" => [
                            "items" =>[
                                'links' =>[
                                    'self', 'related'
                                ],
                                'data' => [
                                    '*' =>[
                                        'type', 'id'
                                    ]
                                ]
                            ]
                        ],  
                    ]
                ],
            "included" => [
                '*' => [
                    "type","id",
                    "attributes"=> [
                        "description","is_completed","completed_at","due",
                        "urgency","updated_by","user_id","checklist_id",
                        "deleted_at","created_at","updated_at"
                    ],
                    "links" => [
                        "self"
                    ]
                ]
            ]
        ]);
    }
}