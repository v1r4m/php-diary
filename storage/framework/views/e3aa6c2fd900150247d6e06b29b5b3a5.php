<?php $__env->startSection('title', 'Login - Encrypted Diary'); ?>

<?php $__env->startSection('content'); ?>
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
            Don't have an account? <a href="<?php echo e(route('register')); ?>">Register</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/auth.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/auth/login.blade.php ENDPATH**/ ?>