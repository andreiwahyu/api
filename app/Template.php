<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'template';

    protected $guarded = ['id'];

    public function checklist()
    {
        return $this->hasMany("App\Checklist");
    }

}
