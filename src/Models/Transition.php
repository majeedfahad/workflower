<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Majeedfahad\Workflower\Contracts\Workflowable;
use Majeedfahad\Workflower\Enums\Color;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Soft deleted: a deleted transition can no longer be applied or listed, but
 * its logs keep pointing at it, and its permission and behaviours are kept so
 * restoring it brings it back as it was.
 */
class Transition extends Model
{
    use SoftDeletes;

    protected $table = 'transitions';

    protected $guarded = [];

    protected $casts = [
        'color' => Color::class,
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function fromState(): BelongsTo
    {
        return $this->belongsTo(State::class, 'from_state_id');
    }

    public function toState(): BelongsTo
    {
        return $this->belongsTo(State::class, 'to_state_id');
    }

    public function behaviours(): BelongsToMany
    {
        return $this->belongsToMany(Behaviour::class);
    }

    /** The permission that guards who can apply the transition. */
    public function permission(): HasOne
    {
        return $this->hasOne(Permission::class);
    }

    public function getNextTransitionsAttribute()
    {
        return $this->toState?->transitions->map->name;
    }

    public static function initiate(Workflow $workflow, string $name): self
    {
        $transition = new self();
        $transition->name = $name;
        $transition->workflow()->associate($workflow);

        return $transition;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;

        return $this;
    }

    /** Null clears it. Not saved. */
    public function setColor(Color|string|null $color): self
    {
        $this->color = is_string($color) ? Color::from($color) : $color;

        return $this;
    }

    /** Null clears it: the transition can then be applied from any state. */
    public function from(State|string|null $state = null): self
    {
        if (is_null($state)) {
            $this->fromState()->dissociate();

            return $this;
        }

        $this->fromState()->associate($this->resolveState($state));

        return $this;
    }

    public function to(State|string|null $state = null): self
    {
        if (is_null($state)) {
            return $this;
        }

        $this->toState()->associate($this->resolveState($state));

        return $this;
    }

    public function order(int $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function start(bool $start = true): self
    {
        $this->start = $start;

        return $this;
    }

    public function end(): self
    {
        $this->end = true;

        return $this;
    }

    public function addBehaviour(Behaviour $behaviour): self
    {
        $this->behaviours()->syncWithoutDetaching([$behaviour->getKey()]);

        return $this;
    }

    /**
     * @param  array<int, Behaviour|class-string<Behaviour>|int>  $behaviours
     */
    public function syncBehaviours(array $behaviours): self
    {
        $this->behaviours()->sync(array_map(fn ($behaviour) => match (true) {
            $behaviour instanceof Behaviour => $behaviour->getKey(),
            is_string($behaviour) && ! is_numeric($behaviour) => Behaviour::query()->where('class', $behaviour)->firstOrFail()->getKey(),
            default => (int) $behaviour,
        }, $behaviours));

        $this->unsetRelation('behaviours');

        return $this;
    }

    /**
     * The transition's permission, created with its name if it has none.
     * Looked up by transition rather than by name: one name is shared by
     * every transition that does the same thing.
     */
    public function ensurePermission(string $guard = 'web'): Permission
    {
        $permission = Permission::query()->firstOrCreate(
            ['transition_id' => $this->getKey()],
            ['name' => $this->name, 'guard_name' => $guard],
        );

        $this->setRelation('permission', $permission);

        return $permission;
    }

    /**
     * Lets the roles apply the transition. Nothing is revoked.
     *
     * The permission is attached directly instead of with givePermissionTo(),
     * which reloads every permission of the role (and shared roles hold one
     * per transition of every owner); Spatie's cache is only dropped, to be
     * rebuilt on the next check.
     */
    public function grantTo(Role ...$roles): self
    {
        if ($roles === []) {
            return $this;
        }

        $permission = $this->ensurePermission($roles[0]->guard_name ?? 'web');

        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching([$permission->getKey()]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $this;
    }

    /**
     * Exactly these roles can apply the transition: the others lose it. No
     * role leaves it to the system. The permission is created if missing,
     * on the roles' guard; a role on another guard than it is refused.
     */
    public function syncRoles(Role ...$roles): self
    {
        $permission = $this->ensurePermission($this->permission?->guard_name ?? $roles[0]->guard_name ?? 'web');

        foreach ($roles as $role) {
            if ($role->guard_name !== $permission->guard_name) {
                throw new InvalidArgumentException("Role [{$role->name}] is on guard [{$role->guard_name}], the transition's permission on [{$permission->guard_name}].");
            }
        }

        $permission->roles()->sync(array_map(fn (Role $role) => $role->getKey(), $roles));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $this;
    }

    /**
     * Sets the whole transition and saves it; what is passed as null is left
     * as it is (except `from`, where null means "from any state", and is
     * forced by a start transition, which has no source state). A color is
     * cleared with setColor(null).
     *
     * @param  array<int, Behaviour|class-string<Behaviour>|int>|null  $behaviours  synced
     * @param  array<int, Role>|null  $roles  granted the permission, which is created
     *                                          (even for an empty array) if missing
     */
    public function define(
        ?string $label = null,
        State|string|null $from = null,
        State|string|null $to = null,
        ?bool $start = null,
        ?int $order = null,
        ?array $behaviours = null,
        ?array $roles = null,
        ?string $guard = null,
        Color|string|null $color = null,
    ): self {
        if (! is_null($label)) {
            $this->setLabel($label);
        }

        if (! is_null($color)) {
            $this->setColor($color);
        }

        if (! is_null($start)) {
            $this->start($start);
        }

        $this->from($this->start ? null : $from)->to($to);

        if (! is_null($order)) {
            $this->order($order);
        }

        if (is_null($this->to_state_id)) {
            throw new InvalidArgumentException("Transition [{$this->name}] needs a target state.");
        }

        DB::transaction(function () use ($behaviours, $roles, $guard) {
            $this->save();

            if (! is_null($behaviours)) {
                $this->syncBehaviours($behaviours);
            }

            if (! is_null($roles)) {
                $this->ensurePermission($guard ?? $roles[0]->guard_name ?? 'web');
                $this->grantTo(...$roles);
            }
        });

        return $this;
    }

    /** A state of the transition's own workflow, by name or model. */
    protected function resolveState(State|string $state): State
    {
        if (is_string($state)) {
            return $this->workflow->states()->where('name', $state)->firstOrFail();
        }

        if ((int) $state->workflow_id !== (int) $this->workflow_id) {
            throw new InvalidArgumentException("State [{$state->name}] is not in the transition's workflow.");
        }

        return $state;
    }

    public function removeBehaviour(Behaviour $behaviour): self
    {
        $this->behaviours()->detach($behaviour);

        return $this;
    }

    public function hasBehaviours(): bool
    {
        return $this->behaviours()->exists();
    }

    public function runBehaviours(Workflowable $workflowable, array $meta, array $parameters): array
    {
        $this->behaviours->each(function (Behaviour $behaviour) use ($workflowable, &$meta, $parameters) {
            $class = $behaviour->class::getInstance();
            $validated = $class->validateParameters($parameters);
            $result = $class->handle($workflowable, $this, $meta, $validated) ?? [];
            $meta = array_merge($meta, $this->serializableMeta($result));
        });

        return $meta;
    }

    private function serializableMeta(array $meta): array
    {
        return array_filter($meta, function ($value) {
            return is_null($value) || is_scalar($value) || (is_array($value) && $value === json_decode(json_encode($value), true));
        });
    }

    public function doesntHaveFromState(): bool
    {
        return is_null($this->fromState);
    }

    public function getRequirements(): array
    {
        return $this->behaviours->reduce(function ($carry, Behaviour $behaviour) {
            $requirements = $behaviour->parameters ?? [];
            return array_merge($carry, $requirements);
        }, []);
    }

    public function hasRequirements(): bool
    {
        return !empty($this->getRequirements());
    }
}
