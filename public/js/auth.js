/**
 * Authentication handling for login and registration
 * Note: Diary encryption password is set separately in the diary unlock modal
 */

(function() {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    /**
     * Show error message
     */
    function showError(message) {
        const errorEl = document.getElementById('error-message');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }
    }

    /**
     * Hide error message
     */
    function hideError() {
        const errorEl = document.getElementById('error-message');
        if (errorEl) {
            errorEl.style.display = 'none';
        }
    }

    /**
     * Set button loading state
     */
    function setLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.dataset.originalText = button.textContent;
            button.textContent = 'Please wait...';
        } else {
            button.disabled = false;
            button.textContent = button.dataset.originalText || 'Submit';
        }
    }

    /**
     * Handle login form submission
     */
    async function handleLogin(event) {
        event.preventDefault();
        hideError();

        const form = event.target;
        const submitBtn = document.getElementById('submit-btn');
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const remember = document.getElementById('remember').checked;

        setLoading(submitBtn, true);

        try {
            // Authenticate with server
            const response = await fetch('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email, password, remember })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Login failed');
            }

            // Redirect to diary page
            // Diary password will be set separately in the unlock modal
            window.location.href = data.redirect;

        } catch (error) {
            showError(error.message);
            setLoading(submitBtn, false);
        }
    }

    /**
     * Handle registration form submission
     */
    async function handleRegister(event) {
        event.preventDefault();
        hideError();

        const form = event.target;
        const submitBtn = document.getElementById('submit-btn');
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;

        // Client-side validation
        if (password !== passwordConfirmation) {
            showError('Passwords do not match');
            return;
        }

        if (password.length < 8) {
            showError('Password must be at least 8 characters');
            return;
        }

        setLoading(submitBtn, true);

        try {
            // Register with server
            const response = await fetch('/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name,
                    email,
                    password,
                    password_confirmation: passwordConfirmation
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                // Handle validation errors
                if (data.errors) {
                    const firstError = Object.values(data.errors)[0];
                    throw new Error(Array.isArray(firstError) ? firstError[0] : firstError);
                }
                throw new Error(data.message || 'Registration failed');
            }

            // Redirect to diary page
            // Diary password will be set separately in the unlock modal
            window.location.href = data.redirect;

        } catch (error) {
            showError(error.message);
            setLoading(submitBtn, false);
        }
    }

    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');

        if (loginForm) {
            loginForm.addEventListener('submit', handleLogin);
        }

        if (registerForm) {
            registerForm.addEventListener('submit', handleRegister);
        }
    });
})();
