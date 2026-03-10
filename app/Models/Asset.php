<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use AsSource, Filterable, HasFactory;
    protected $fillable = [
        'sicoin',
        'description',
        'value',
        'state',
        'category',
        'date',
    ];

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function removal()
    {
        return $this->hasOne(AssetRemoval::class);
    }
}
