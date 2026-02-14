<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Owner extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'identity_number',
        'address',
        'notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'email', 'identity_number', 'address'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
