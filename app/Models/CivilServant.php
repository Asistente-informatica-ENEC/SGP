<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CivilServant extends Model
{
    use AsSource, Filterable, HasFactory;
    protected $fillable = [
        'name',
        'sede',
        'nit',
        'position_id',
        'unit',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function responsabilityCards()
    {
        return $this->hasMany(ResponsabilityCard::class);
    }
}
