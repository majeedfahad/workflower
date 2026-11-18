<?php

namespace Majeedfahad\Workflower\Enums;

/**
 * What a workflow state means on a progress track.
 */
enum ProgressRole: string
{
    /** The state is the step: several states may be one step. */
    case STEP = 'step';

    /** Past the step but not yet the next one, drawn between them. */
    case BETWEEN = 'between';

    /** The record has no progress (canceled, rejected...): no bar at all. */
    case HIDDEN = 'hidden';
}
