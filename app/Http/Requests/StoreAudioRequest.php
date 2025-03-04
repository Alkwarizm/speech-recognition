<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAudioRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:mp3,mp4'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
