<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $table = 'checklist';

    protected $guarded = ['id'];

    protected $dates = ['due','completed_at'];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    public function item()
    {
        return $this->hasMany("App\Item");
    }

    public function template(){
        return $this->belongsTo("App\Template");
    }
}
