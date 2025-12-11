<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Encrypted Diary'); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
</head>
<body>
    <div class="container">
        <?php if(auth()->check()): ?>
        <nav class="navbar">
            <div class="navbar-brand">
                <a href="<?php echo e(route('diary.index')); ?>">Encrypted Diary</a>
            </div>
            <div class="navbar-menu">
                <a href="<?php echo e(route('diary.index')); ?>" class="navbar-link">내 일기</a>
                <a href="<?php echo e(route('settings.index')); ?>" class="navbar-link">설정</a>
                <span class="navbar-user"><?php echo e(auth()->user()->name); ?></span>
                <form action="<?php echo e(route('logout')); ?>" method="POST" class="logout-form" id="logout-form">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn btn-logout">Logout</button>
                </form>
            </div>
        </nav>
        <?php endif; ?>

        <main class="main-content">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <script src="<?php echo e(asset('js/crypto.js')); ?>"></script>
    <script>
    // Clear encryption keys on logout
    document.addEventListener('DOMContentLoaded', function() {
        const logoutForm = document.getElementById('logout-form');
        if (logoutForm) {
            logoutForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (typeof DiaryEncryption !== 'undefined') {
                    await DiaryEncryption.clearAll();
                    DiaryEncryption.clearDiaryToken();
                }
                logoutForm.submit();
            });
        }
    });
    </script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/resources/views/layouts/app.blade.php ENDPATH**/ ?>