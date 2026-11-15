<?php

namespace Majeedfahad\Workflower\Enums;

enum BehaviourParamType: string
{
    case Text = 'text';
    case Number = 'number';
    case File = 'file';
    case FileUUID = 'file_uuid';
    case Select = 'select';
    case Textarea = 'textarea';
    case Checkbox = 'checkbox';
    case Date = 'date';
    case Tel = 'tel';
    case Range = 'range';
    case Array = 'array';

    public function validationRule(): string
    {
        return match($this) {
            self::Text, self::Textarea, self::Select => 'string',
            self::Number, self::Range                => 'numeric',
            self::Date                               => 'date',
            self::Checkbox                           => 'boolean',
            self::File                               => 'file',
            self::FileUUID                           => 'uuid',
            self::Tel                                => 'string',
            self::Array                              => 'array',
        };
    }
}
