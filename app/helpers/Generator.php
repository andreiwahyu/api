<?php

namespace App\Helpers;

Class Generator {

    public $model;
    public $request;
    public $offset;
    public $limit;
    public $total;
    public $route;

    public function __construct ($model, $request, $route){
        $this->model = app("App\\$model");
        $this->request = $request;
        $this->route = $route;
        
        if($this->request->has('filter')){
            $this->filter();
        }

        if($this->request->has('sort')){
            $this->sort();
        }

        $this->limit = $request->page['limit'] ?? 10;
        $this->offset = $request->page['offset'] ?? 0;
        $this->total = $this->model->get()->count();
        
    }
    public function data() {
        return $this->model->skip($this->offset)->take($this->limit)->get();
    }

    public function meta()
    {
        return [
            'count' => $this->model->skip($this->offset)->take($this->limit)->get()->count(),
            'total' => $this->total
        ];
    }

    public function paginator(){
        $route = $this->route;
        $total = $this->total;
        $limit = $this->limit;
        $offset = $this->offset;
        $links = [];
        
        $totalPages = ceil( $total / $limit );
        
        $currentPage = ($offset/ $limit)+1;
        if ($currentPage < 1) {
            $currentPage = 1;
        } else if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }

        $additionalLink =  (isset($this->request->filter) ? '&'.http_build_query (array("filter" =>$this->request->filter)) : '') . (isset($this->request->sort) ? '&'.http_build_query (array("sort" =>$this->request->sort)) : '');
        $additionalLink = urldecode($additionalLink);

        $link["first"] = $route . "?page[limit]=".$limit."&page[offset]=0" . $additionalLink;
        $link["last"] = $route . "?page[limit]=".$limit."&page[offset]=". ($total-$limit <= 0 ? 0 : $total-$limit ) . $additionalLink;
        if($currentPage == $totalPages){
            $link["next"] = "null";
        }
        else{
            $link["next"] = $route . "?page[limit]=".$limit."&page[offset]=". $limit*$currentPage . $additionalLink;
        }

        if($currentPage == 1) {
            $link["prev"] = "null";
        }
        else{
            $link["prev"] = $route . "?page[limit]=".$limit."&page[offset]=". $limit*($currentPage-1). $additionalLink;
        }
        
        return $link;
    }

    public function filter(){
        foreach ($this->request->filter as $field => $array) {
            foreach ($array as $key => $value) {
                 if($key == "like"){
                    $this->model = $this->model->where($field, 'like', str_replace('*' , '%' , $value));
                 }
                 else if($key == "!like") {
                    $this->model = $this->model->where($field, 'not like', str_replace('*' , '%' , $value));
                 }
                 else if($key == 'is') {
                    $this->model = $this->model->where($field, '=', $value);
                 }
                 else if($key == '!is'){
                    $this->model = $this->model->where($field, '<>', $value);
                 }
                 else if($key == 'in'){
                    $this->model = $this->model->whereIn($field, explode(',', $value));
                 }
                 else if($key == '!in'){
                    $this->model = $this->model->whereNotIn($field, explode(',', $value));
                 }
            }
        }
    }

    public function sort(){
        if($this->request->sort[0] == '-'){
            $this->model = $this->model->orderBy(str_replace('-' , '' , $this->request->sort),'desc');
        }
        else {
            $this->model = $this->model->orderBy($this->request->sort,'asc');
        }
    }
}