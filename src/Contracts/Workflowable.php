<?php

namespace Majeedfahad\Workflower\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface Workflowable
{
    public function status(): MorphOne;

    public function applyTransition(string $transition, array $meta = [], array $parameters = []): void;
    
    public function workflowOwner(): ?Model;
}
