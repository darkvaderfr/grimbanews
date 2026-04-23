<?php

namespace Theme\Echo\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ShortcodeAttributeRequest extends Request
{
    public function rules(): array
    {
        return [
            'shortcode' => ['nullable', 'array'],
            'shortcode.*' => ['nullable', 'string'],
        ];
    }
}
