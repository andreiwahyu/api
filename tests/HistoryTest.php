<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class HistoryTest extends TestCase
{
    public function testErrorAuthHistoriesList()
    {
        $this->get(route('history.all'));
        $this->seeStatusCode(401);
    }

    public function testSuccessHistoriesList()
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
                    'attributes',
                    'links' => [
                        'self'
                    ]
                ]
            ],
            'links' => [
                'first', 'last', 'next', 'prev'
            ]
         ]);
    }


    public function testErrorAuthHistory()
    {
        $this->get(route('history.one'));
        $this->seeStatusCode(401);
    }

    public function testSuccessHistory()
    {
        $this->actingAs(App\User::first());
        $this->get(route('history.one', [ 'historyId' => App\History::get()->random() ]));
        $this->seeStatusCode(200);
        $this->seeJsonStructure([
        'data' => [
                'type','id',
                'attributes' => [
                    "loggable_type", "loggable_id", "action", "kwuid", "value", "updated_at", "created_at"
                ],
                'links' => [
                    'self'
                ]
            ]
        ]);
    }
}