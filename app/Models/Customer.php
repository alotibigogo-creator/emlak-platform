<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Customer extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'id_number',
        'nationality',
        'address',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'email', 'id_number', 'nationality', 'address'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
