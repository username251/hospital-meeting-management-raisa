<!-- resources/views/auth/reset-password.blade.php -->
<x-guest-layout>
    <div class="auth-header">
        <h1>{{ __('Reset Password') }}</h1>
        <p>{{ __('Please enter your new password below') }}</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="form-group">
            <label class="form-label" for="email">{{ __('Email') }}</label>
            <input id="email" 
                   class="form-input @error('email') error @enderror" 
                   type="email" 
                   name="email" 
                   value="{{ old('email', $request->email) }}" 
                   required 
                   autofocus 
                   autocomplete="username"
                   placeholder="{{ __('Enter your email') }}">
            @error('email')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label class="form-label" for="password">{{ __('New Password') }}</label>
            <input id="password" 
                   class="form-input @error('password') error @enderror" 
                   type="password" 
                   name="password" 
                   required 
                   autocomplete="new-password"
                   placeholder="{{ __('Enter new password') }}">
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
                   placeholder="{{ __('Confirm new password') }}">
            @error('password_confirmation')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">
            {{ __('Reset Password') }}
        </button>

        <div class="auth-links">
            <a href="{{ route('login') }}">{{ __('Back to Login') }}</a>
        </div>
    </form>
</x-guest-layout>