<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'client_name', 'status', 'created_by'];

    protected $casts = [
        'status' => 'string',
    ];

    public function artifacts(): HasMany
    {
        return $this->hasMany(Artifact::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
