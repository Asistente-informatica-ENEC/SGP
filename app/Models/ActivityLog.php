<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use AsSource, Filterable, HasFactory;
    protected $fillable = [
        'responsability_card_id',
        'asset_removal_id',
        'asset_id',
    ];

    public function responsabilityCard()
    {
        return $this->belongsTo(ResponsabilityCard::class);
    }

    public function removal()
    {
        return $this->belongsTo(AssetRemoval::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
