<?php

return [

    'behaviour' => [
        'invalid_type'    => "يجب أن يحتوي المعامل ':key' على 'type' صالح معرّف في BehaviourParamType.",
        'missing_label'   => "يجب أن يحتوي المعامل ':key' على 'label' معرّف.",
        'missing_name'    => "يجب أن يحتوي المعامل ':key' على 'name' معرّف.",
        'missing_fields'  => "يجب أن يحتوي معامل المصفوفة ':key' على قائمة 'fields' غير فارغة.",
    ],

    'transition' => [
        'not_found_on_workflow' => "اجراء ':transition' غير موجود في سير العمل ':workflow'.",
        'not_found_on_state'    => "اجراء ':transition' غير متاح لحالة ':state'.",
    ],

];
