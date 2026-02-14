<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Property extends Model
{
    use LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'type',
        'address',
        'area',
        'description',
        'owner_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            if (!$property->code) {
                $property->code = 'P-' . str_pad(static::max('id') + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'name', 'type', 'address', 'area', 'description', 'owner_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function contracts(): HasManyThrough
    {
        return $this->hasManyThrough(Contract::class, Unit::class);
    }
}
