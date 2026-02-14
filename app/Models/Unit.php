<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Unit extends Model
{
    use LogsActivity;

    protected $fillable = [
        'code',
        'property_id',
        'type',
        'floor',
        'number',
        'area',
        'bedrooms',
        'bathrooms',
        'status',
        'rent_price',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($unit) {
            if (!$unit->code) {
                $unit->code = 'U-' . str_pad(static::max('id') + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'property_id', 'type', 'floor', 'number', 'area', 'bedrooms', 'bathrooms', 'status', 'rent_price'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}
