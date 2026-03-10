<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetRemoval extends Model
{
    use AsSource, Filterable, HasFactory;
    protected $fillable = [
        'asset_id',
        'description',
        'date',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
