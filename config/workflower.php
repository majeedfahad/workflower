<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Workflow Models
    |--------------------------------------------------------------------------
    |
    | You may override the default models used by Workflower.
    |
    */

    'models' => [
        'workflow'       => \Majeedfahad\Workflower\Models\Workflow::class,
        'state'          => \Majeedfahad\Workflower\Models\State::class,
        'status'         => \Majeedfahad\Workflower\Models\Status::class,
        'transition'     => \Majeedfahad\Workflower\Models\Transition::class,
        'behaviour'      => \Majeedfahad\Workflower\Models\Behaviour::class,
        'transition_log' => \Majeedfahad\Workflower\Models\TransitionLog::class,
        'permission'     => \Majeedfahad\Workflower\Models\Permission::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Override the default database table names used by Workflower.
    |
    */

    'tables' => [
        'workflows'       => 'workflows',
        'states'          => 'states',
        'statuses'        => 'statuses',
        'transitions'     => 'transitions',
        'behaviours'      => 'behaviours',
        'transition_logs' => 'transition_logs',
        'permissions'     => 'permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Behaviour Validation
    |--------------------------------------------------------------------------
    |
    | Configure default validation behaviour for behaviour parameters.
    |
    */

    'behaviour' => [
        'validation' => [
            'required_by_default' => true,

            'rules_by_type' => [
                'text'     => ['max' => 255],
                'number'   => [],
                'date'     => [],
                'textarea' => ['max' => 2000],
                'select'   => [],
                'checkbox' => [],
                'file'     => ['mimes' => env('WORKFLOW_FILE_MIMES', 'pdf,doc,docx,jpg,jpeg,png,gif,webp'), 'max' => (int) env('WORKFLOW_FILE_MAX_SIZE', 8192)],
                'tel'      => ['regex' => '/^05\d{8}$/'],
            ],
        ],
    ],

];
