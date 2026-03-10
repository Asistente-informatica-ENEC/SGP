<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Position extends Model
{
    use AsSource, Filterable, HasFactory;

    protected $fillable = ['name'];

    public function civilServants()
    {
        return $this->hasMany(CivilServant::class);
    }
}
