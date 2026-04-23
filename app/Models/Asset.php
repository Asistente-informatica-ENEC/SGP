<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use AsSource, Filterable, HasFactory;
    protected $casts = [
        'date' => 'date',
    ];

    protected $fillable = [
        'sicoin',
        'description',
        'inventory_book',
        'folio_number',
        'value',
        'state',
        'category',
        'date',
    ];

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    public function latestAssignment()
    {
        return $this->hasOne(Assignment::class)->latestOfMany();
    }

    public function removal()
    {
        return $this->hasOne(AssetRemoval::class);
    }

    public function scopeDisponible($query)
    {
        return $query->where('state', 'DISPONIBLE');
    }

    /**
     * @param string $value
     */
    public function setStateAttribute($value)
    {
        $this->attributes['state'] = strtoupper($value);
    }
}
