<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Behaviour extends Model
{
    protected $table = 'behaviours';

    public function transitions(): BelongsToMany
    {
        return $this->belongsToMany(Transition::class);
    }

    public static function getInstance(): Model|Behaviour
    {
        return static::query()->where('class', static::class)->firstOrFail();
    }
}
