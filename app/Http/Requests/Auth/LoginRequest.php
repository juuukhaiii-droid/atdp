<?php

namespace App\Http\Requests\Auth;

use App\Models\Employee;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        app()->setLocale('km');
        if ($this->has('identifier') && is_string($this->input('identifier'))) {
            $identifier = trim($this->input('identifier'));
            $emailLogin = str_contains($identifier, '@');
            $this->merge([
                'identifier' => $identifier,
                'login_type' => $emailLogin ? 'admin' : 'employee',
                'email' => $emailLogin ? $identifier : null,
                'full_name' => $emailLogin ? null : $identifier,
                'pin' => $emailLogin ? null : $this->input('password'),
            ]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function isEmployeeLogin(): bool
    {
        return $this->input('login_type') === 'employee';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->has('identifier')) {
            return [
                'identifier' => $this->isEmployeeLogin()
                    ? ['required', 'string', 'max:255']
                    : ['required', 'email:filter', 'max:255'],
                'password' => $this->isEmployeeLogin()
                    ? ['required', 'digits:4']
                    : ['required', 'string'],
            ];
        }

        if ($this->isEmployeeLogin()) {
            return [
                'full_name' => ['required', 'string'],
                'pin' => ['required', 'digits:4'],
            ];
        }

        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function messages(): array
    {
        return [
            'identifier.email' => 'អ្នកគ្រប់គ្រងប្រើអ៊ីមែលពេញ និងពាក្យសម្ងាត់។ បុគ្គលិកប្រើឈ្មោះពេញ និង PIN។',
            'password.digits' => 'បុគ្គលិកប្រើឈ្មោះពេញ និង PIN ៤ ខ្ទង់។ អ្នកគ្រប់គ្រងប្រើអ៊ីមែល និងពាក្យសម្ងាត់។',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if ($this->isEmployeeLogin()) {
            $this->authenticateEmployee();
            return;
        }

        if (! Auth::attempt(array_merge($this->only('email', 'password'), ['role' => 'admin']), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Match a name + PIN against active employees. PIN alone can't disambiguate
     * two employees sharing a name, so every candidate with that name is
     * checked against the hashed PIN rather than picking the first match.
     *
     * @throws ValidationException
     */
    protected function authenticateEmployee(): void
    {
        $name = trim((string) $this->input('full_name'));
        $pin = (string) $this->input('pin');

        $matches = Employee::whereRaw('LOWER(full_name) = ?', [Str::lower($name)])
            ->where('status', 'active')
            ->whereNotNull('user_id')
            ->with('user')
            ->get()
            ->filter(fn (Employee $candidate) => $candidate->user?->role === 'employee' && Hash::check($pin, $candidate->pin));

        // Never sign into the wrong account when both the name and PIN match.
        $employee = $matches->count() === 1 ? $matches->first() : null;

        if (! $employee) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'full_name' => trans('auth.failed'),
            ]);
        }

        Auth::login($employee->user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            ($this->isEmployeeLogin() ? 'full_name' : 'email') => trans('auth.throttle', [
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
        $identifier = $this->isEmployeeLogin()
            ? $this->string('full_name')
            : $this->string('email');

        return Str::transliterate(Str::lower($identifier).'|'.$this->ip());
    }
}
