<!-- resources/views/auth/register.blade.php -->
<x-guest-layout>
    <div class="auth-header">
        <h1>{{ __('Create Account') }}</h1>
        <p>{{ __('Join us today and get started') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="form-group">
            <label class="form-label" for="name">{{ __('Name') }}</label>
            <input id="name" 
                   class="form-input @error('name') error @enderror" 
                   type="text" 
                   name="name" 
                   value="{{ old('name') }}" 
                   required 
                   autofocus 
                   autocomplete="name"
                   placeholder="{{ __('Enter your full name') }}">
            @error('name')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="form-group">
            <label class="form-label" for="email">{{ __('Email') }}</label>
            <input id="email" 
                   class="form-input @error('email') error @enderror" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autocomplete="username"
                   placeholder="{{ __('Enter your email') }}">
            @error('email')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label class="form-label" for="password">{{ __('Password') }}</label>
            <input id="password" 
                   class="form-input @error('password') error @enderror"
                   type="password"
                   name="password"
                   required 
                   autocomplete="new-password"
                   placeholder="{{ __('Create a password') }}">
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" 
                   class="form-input @error('password_confirmation') error @enderror"
                   type="password"
                   name="password_confirmation" 
                   required 
                   autocomplete="new-password"
                   placeholder="{{ __('Confirm your password') }}">
            @error('password_confirmation')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex-end">
            <a href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <button type="submit" class="btn btn-primary">
                {{ __('Register') }}
            </button>
        </div>
    </form>
</x-guest-layout>