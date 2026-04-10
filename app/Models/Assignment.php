<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use AsSource, Filterable, HasFactory;
    protected $casts = [
        'date' => 'datetime',
    ];

    protected $fillable = [
        'asset_id',
        'responsability_card_id',
        'date',
        'observation',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function responsabilityCard()
    {
        return $this->belongsTo(ResponsabilityCard::class);
    }
}
