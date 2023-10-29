<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Behaviour extends Model
{
    public const TYPE_FILE = 'file';
    public const TYPE_TEXT = 'text';

    protected $table = 'behaviours';

    public function transitions(): BelongsToMany
    {
        return $this->belongsToMany(Transition::class);
    }

    public static function getInstance(): Model|Behaviour
    {
        return static::query()->where('class', static::class)->firstOrFail();
    }

    public function getParametersAttribute($value): array|null
    {
        return json_decode($value, true);
    }

    public function modelToArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'parameters' => $this->parameters,
        ];
    }
}
