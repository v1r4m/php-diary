@extends('layouts.app')

@section('title', 'Register - Encrypted Diary')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Register</h1>

        <div id="error-message" class="error-message" style="display: none;"></div>

        <form id="register-form" class="auth-form">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required autocomplete="name">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8">
                <small class="form-hint">At least 8 characters</small>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="submit-btn">Register</button>
        </form>

        <p class="auth-link">
            Already have an account? <a href="{{ route('login') }}">Login</a>
        </p>

        <div class="security-notice">
            <strong>Important:</strong> Your diary entries will be encrypted using your password.
            If you forget your password, your diary entries cannot be recovered.
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/auth.js') }}"></script>
@endsection
