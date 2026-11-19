<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Majeedfahad\Workflower\Enums\Color;
use Spatie\Permission\Models\Role;

class Workflow extends Model
{
    protected $guarded = [];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(Transition::class);
    }

    /**
     * Adds a transition (always a new one: several transitions of a workflow
     * may share a name, one per source state). See Transition::define().
     *
     * @param  array<int, Behaviour|class-string<Behaviour>|int>  $behaviours
     * @param  array<int, Role>|null  $roles  null: no permission is created
     */
    public function path(
        string $name,
        string $label,
        State|string|null $from = null,
        State|string|null $to = null,
        bool $start = false,
        array $behaviours = [],
        ?array $roles = null,
        int $order = 0,
        ?string $guard = null,
        Color|string|null $color = null,
    ): Transition {
        return Transition::initiate($this, $name)->define(
            label: $label,
            from: $from,
            to: $to,
            start: $start,
            order: $order,
            behaviours: $behaviours,
            roles: $roles,
            guard: $guard,
            color: $color,
        );
    }

    public function initialTransition(): Transition
    {
        return $this->transitions()->where('start', true)->first();
    }
}
