<?php

namespace Majeedfahad\Workflower\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Majeedfahad\Workflower\Models\Transition;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;

    public function transition(): HasOne
    {
        return $this->hasOne(Transition::class);
    }

    public static function create(array $attributes = [], ?Transition $transition = null)
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Guard::getDefaultName(static::class);

        $permission = static::getPermission(['name' => $attributes['name'], 'guard_name' => $attributes['guard_name']]);

        if ($permission && $transition == null) {
            throw PermissionAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        if(static::permissionForTransitionExist($transition)) {
            throw PermissionAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        $attributes['transition_id'] = $transition->id;

        return static::query()->create($attributes);
    }

    private static function permissionForTransitionExist(Transition $transition): bool
    {
        if(Permission::query()->where('transition_id', $transition->id)->exists()) {
            return true;
        }

        return false;
    }
}
