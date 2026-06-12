<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'client_name', 'status', 'created_by'];

    protected $casts = [
        'status' => 'string',
    ];

    public function artifacts()
    {
        return $this->hasMany(Artifact::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
