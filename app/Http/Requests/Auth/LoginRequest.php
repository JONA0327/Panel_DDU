<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\UserPanelMiembro;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials with DDU validation.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Paso 1: Verificar si el usuario existe en la tabla users
        $user = User::where('email', $this->input('email'))->first();

        if (!$user) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ]);
        }

        // Paso 2: Verificar la contraseña usando password_verify (compatible con bcryptjs $2b$)
        if (!password_verify($this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ]);
        }

        // Paso 3: Verificar que sea miembro DDU
        if (!UserPanelMiembro::isDduMember($user->email)) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'ddu_access' => 'Acceso restringido: Solo personal autorizado de DDU puede acceder al sistema.',
            ]);
        }

        // Paso 4: Iniciar sesión manualmente ya que las validaciones pasaron
        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
