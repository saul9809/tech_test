<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Module extends Model
{
    protected $fillable = [
        'project_id', 'domain', 'name', 'status',
        'objective', 'inputs', 'data_structure', 'logic_rules',
        'outputs', 'responsibility', 'failure_scenarios',
        'audit_trail_requirements', 'dependencies', 'version_note',
    ];

    protected $casts = [
        'status' => 'string',
        'inputs' => 'array',
        'data_structure' => 'array',
        'outputs' => 'array',
        'dependencies' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
