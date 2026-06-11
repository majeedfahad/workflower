<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Majeedfahad\Workflower\Contracts\Workflowable;
use Majeedfahad\Workflower\Enums\BehaviourParamType;

class Behaviour extends Model
{
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

    public function validateParameters(array $parameters): ?array
    {
        $behaviourParams = $this->parameters;

        if($behaviourParams === []) {
            return [];
        }

        $rules = [];
        $messages = [];
        $attributes = [];

        foreach ($behaviourParams as $field) {
            $type = $field['type'] instanceof BehaviourParamType
                ? $field['type']
                : BehaviourParamType::from($field['type']);
            $typeRule = $type->validationRule();
            $rules[$field['name']] = [$field['required'] ? 'required' : 'nullable', $typeRule, ...$this->toLaravelRules($field['rules'])];
            $attributes[$field['name']] = isset($field['attribute']) && $field['attribute'] ? $field['attribute'] : $field['label'];
        }

        return validator($parameters, $rules, $messages, $attributes)->validate();
    }

    protected function toLaravelRules(array $rules): array
    {
        $laravelRules = [];

        foreach ($rules as $key => $value) {
            $laravelRules[] = is_int($key) ? $value : "{$key}:{$value}";
        }

        return $laravelRules;
    }
}
