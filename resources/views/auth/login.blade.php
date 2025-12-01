@extends('layouts.app')

@section('title', 'Login - Encrypted Diary')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Login</h1>

        <div id="error-message" class="error-message" style="display: none;"></div>

        <form id="login-form" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" id="remember" name="remember">
                    Remember me
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block" id="submit-btn">Login</button>
        </form>

        <p class="auth-link">
            Don't have an account? <a href="{{ route('register') }}">Register</a>
        </p>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/auth.js') }}"></script>
@endsection
