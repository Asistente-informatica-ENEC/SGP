<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResponsabilityCard extends Model
{
    use AsSource, Filterable, HasFactory;

    protected $casts = [
        'assign_date' => 'datetime',
        'update_date' => 'datetime',
    ];

    protected $fillable = [
        'civil_servant_id',
        'assign_name',
        'role',
        'assignment_code',
        'assign_date',
        'update_date',
    ];

    public function civilServant()
    {
        return $this->belongsTo(CivilServant::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
