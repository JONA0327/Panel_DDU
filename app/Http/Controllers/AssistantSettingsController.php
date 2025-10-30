<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssistantSettingsController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'openai_api_key' => ['nullable', 'string', 'max:255'],
            'enable_drive_calendar' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $settings = $user->assistantSetting()->firstOrCreate([], [
            'enable_drive_calendar' => true,
        ]);

        $settings->fill([
            'enable_drive_calendar' => $request->boolean('enable_drive_calendar'),
        ]);

        if ($request->has('openai_api_key')) {
            $settings->openai_api_key = $validated['openai_api_key'];
        }

        $settings->save();

        return back()->with('status', 'Configuración del asistente actualizada correctamente.');
    }
}
