<?php

namespace Majeedfahad\Workflower\Enums;

/**
 * How a state or a transition is shown: a semantic tone, not a hex, so each
 * app maps it to its own palette (the names are Filament's colors). No color
 * is the default, gray-like look.
 */
enum Color: string
{
    case PRIMARY = 'primary';

    case SUCCESS = 'success';

    case WARNING = 'warning';

    case DANGER = 'danger';

    case INFO = 'info';

    case GRAY = 'gray';
}
