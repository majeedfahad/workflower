<?php

namespace Majeedfahad\Workflower\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface Workflowable
{
    public function status(): MorphOne;

    public function workflowOwner();



}
