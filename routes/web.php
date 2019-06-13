<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => 'auth'], function () use ($router) {
    $router->get('/checklists/templates',[
        'as' => 'templates.all', 
        'uses' => 'TemplateController@getall'
    ]);
    $router->get('/checklists/templates/{templateId}',[
        'as' => 'templates.one', 
        'uses' => 'TemplateController@getone'
    ]);
    $router->post('/checklists/templates',[
        'as' => 'templates.store', 
        'uses' => 'TemplateController@store'
    ]);
    $router->patch('/checklists/templates/{templateId}',[
        'as' => 'templates.update', 
        'uses' => 'TemplateController@update'
    ]);
    $router->delete('/checklists/templates/{templateId}',[
        'as' => 'templates.destroy', 
        'uses' => 'TemplateController@destroy'
    ]);
    $router->post('/checklists/templates/{templateId}/assigns',[
        'as' => 'templates.assigns', 
        'uses' => 'TemplateController@assigns'
    ]);

    $router->get('/checklists/',[
        'as' => 'checklist.all', 
        'uses' => 'ChecklistController@getall'
    ]);
    $router->get('/checklists/{checklistId}',[
        'as' => 'checklist.one', 
        'uses' => 'ChecklistController@getone'
    ]);
    $router->patch('/checklists/{checklistId}',[
        'as' => 'checklist.update', 
        'uses' => 'ChecklistController@update'
    ]);
    $router->delete('/checklists/{checklistId}',[
        'as' => 'checklist.destroy', 
        'uses' => 'ChecklistController@destroy'
    ]);
    $router->post('/checklists/',[
        'as' => 'checklist.store', 
        'uses' => 'ChecklistController@store'
    ]);

    $router->get('/checklists/{checklistId}/items',[
        'as' => 'items.all', 
        'uses' => 'ItemsController@getall'
    ]);
    $router->get('/checklists/{checklistId}/items/{itemId}',[
        'as' => 'items.one', 
        'uses' => 'ItemsController@getone'
    ]);
    $router->post('/checklists/complete', [
        'as' => 'items.complete', 
        'uses' => 'ItemsController@complete'
    ]);
    $router->post('/checklists/incomplete', [
        'as' => 'items.incomplete', 
        'uses' => 'ItemsController@incomplete'
    ]);
    $router->post('/checklists/incomplete', [
        'as' => 'items.incomplete', 
        'uses' => 'ItemsController@incomplete'
    ]);
    $router->post('/checklists/{checklistId}/items', [
        'as' => 'items.store', 
        'uses' => 'ItemsController@store'
    ]);
    $router->patch('/checklists/{checklistId}/items/{itemId}', [
        'as' => 'items.update', 
        'uses' => 'ItemsController@update'
    ]);
    $router->delete('/checklists/{checklistId}/items/{itemId}', [
        'as' => 'items.destroy', 
        'uses' => 'ItemsController@destroy'
    ]);
    $router->post('/checklists/{checklistId}/items/_bulk', [
        'as' => 'items.bulk', 
        'uses' => 'ItemsController@updatebulk'
    ]);
    $router->get('/checklists/items/summaries', [
        'as' => 'items.summaries', 
        'uses' => 'ItemsController@summaries'
    ]);

    $router->get('/history/',[
        'as' => 'history.all', 
        'uses' => 'HistoryController@getall'
    ]);
    $router->get('/history/{historyId}',[
        'as' => 'history.one', 
        'uses' => 'HistoryController@getone'
    ]);
});