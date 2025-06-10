
<!-- resources/views/auth/login.blade.php -->
<x-guest-layout>
    <div class="auth-header">
        <h1>{{ __('Welcome Back') }}</h1>
        <p>{{ __('Please sign in to your account') }}</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="status-message">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <label class="form-label" for="email">{{ __('Email') }}</label>
            <input id="email" 
                   class="form-input @error('email') error @enderror" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
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
            <label class="form-label" for="password">{{ __('Password') }}</label>
            <input id="password" 
                   class="form-input @error('password') error @enderror"
                   type="password"
                   name="password"
                   required 
                   autocomplete="current-password"
                   placeholder="{{ __('Enter your password') }}">
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="checkbox-group">
            <input id="remember_me" type="checkbox" name="remember">
            <label for="remember_me">{{ __('Remember me') }}</label>
        </div>


            <button type="submit" class="btn btn-primary">
                {{ __('Log in') }}
            </button>
        </>

        <div class="auth-links">
            @if (Route::has('register'))
                <a href="{{ route('register') }}">{{ __('Don\'t have an account? Register') }}</a>
            @endif
        </div>
    </form>
</x-guest-layout>

