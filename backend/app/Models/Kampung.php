<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kampung extends Model
{
    protected $guarded = ['id'];

    public function waliMurids()
    {
        return $this->hasMany(WaliMurid::class, 'kampung_id');
    }

    public function murids()
    {
        return $this->hasMany(Murid::class, 'kampung_id');
    }
}

