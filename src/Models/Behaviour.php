<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Majeedfahad\Workflower\Contracts\Workflowable;

class Behaviour extends Model
{
    public const TYPE_FILE = 'file';
    public const TYPE_TEXT = 'text';
    public const TYPE_SELECT = 'select';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_CHECKBOX = 'checkbox';

    protected static ?string $description = null;
    protected static array $parameters = [];
    protected static ?string $name = null;

    protected $table = 'behaviours';

    protected $casts = [
        'parameters' => 'array',
    ];

    protected $fillable = [
        'name',
        'description',
        'class',
        'parameters',
    ];

    public function transitions(): BelongsToMany
    {
        return $this->belongsToMany(Transition::class);
    }

    public static function getInstance(): Model|Behaviour
    {
        return static::query()->where('class', static::class)->firstOrFail();
    }

    public function modelToArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'parameters' => $this->parameters,
        ];
    }
    
    public static function getDescription(): ?string
    {
        return static::$description;
    }

    public static function getParameters(): array
    {
        return static::$parameters;
    }

    public static function getName(): string
    {
        return static::$name ?? class_basename(static::class);
    }

    public function handle(Workflowable $workflowable, Transition $transition, array $meta, array $parameters): ?array
    {
        return null;
    }
}
