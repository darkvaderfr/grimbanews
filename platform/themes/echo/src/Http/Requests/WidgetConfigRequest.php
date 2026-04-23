<?php

namespace Theme\Echo\Http\Requests;

use Botble\Support\Http\Requests\Request;

class WidgetConfigRequest extends Request
{
    public function rules(): array
    {
        return [
            'config' => ['nullable', 'array'],
            'config.*' => ['sometimes'],
            'sidebar' => ['nullable', 'string'],
        ];
    }
}
