<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['title','description','is_done','completed_at'];

    protected $casts = [
        'is_done' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function scopeActive($q)   { return $q->where('is_done', false); }
    public function scopeHistory($q)  { return $q->where('is_done', true); }
}
