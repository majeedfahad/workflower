<?php

return [

    'behaviour' => [
        'invalid_type'    => "Parameter ':key' must have a valid 'type' defined in BehaviourParamType enum.",
        'missing_label'   => "Parameter ':key' must have a 'label' defined.",
        'missing_name'    => "Parameter ':key' must have a 'name' defined.",
        'missing_fields'  => "Array parameter ':key' must have a non-empty 'fields' list.",
    ],

    'transition' => [
        'not_found_on_workflow' => "Transition :transition not found on workflow :workflow.",
        'not_found_on_state'    => "Transition :transition not found on state :state.",
    ],

];
