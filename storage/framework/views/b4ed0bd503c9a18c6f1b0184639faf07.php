<?php $__env->startSection('title', '설정 - Encrypted Diary'); ?>

<?php $__env->startSection('content'); ?>
<div class="settings-container">
    <h1>설정</h1>

    <?php if(session('success')): ?>
        <div class="success-message">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="settings-card">
        <h2>프로필 설정</h2>

        <form action="<?php echo e(route('settings.username')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="form-group">
                <label for="username">사용자명 (Username)</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo e(old('username', $user->username)); ?>"
                    placeholder="예: viram"
                    pattern="[a-zA-Z0-9_-]+"
                    minlength="3"
                    maxlength="30"
                    required
                >
                <span class="form-hint">
                    영문, 숫자, 밑줄(_), 하이픈(-) 사용 가능 (3~30자)
                </span>
                <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <span class="error-text"><?php echo e($message); ?></span>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="form-group">
                <label>공개 프로필 주소</label>
                <div class="profile-url">
                    <code id="profile-url-preview"><?php echo e(url('/@' . ($user->username ?: '[username]'))); ?></code>
                    <?php if($user->username): ?>
                        <a href="<?php echo e(route('public.profile', $user->username)); ?>" target="_blank" class="btn btn-small">
                            프로필 보기
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">저장</button>
            </div>
        </form>
    </div>

    <div class="settings-card">
        <h2>계정 정보</h2>
        <div class="info-row">
            <span class="info-label">이메일</span>
            <span class="info-value"><?php echo e($user->email); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">이름</span>
            <span class="info-value"><?php echo e($user->name); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">가입일</span>
            <span class="info-value"><?php echo e($user->created_at->format('Y년 m월 d일')); ?></span>
        </div>
    </div>

    <div class="settings-card warning-card">
        <h2>보안 안내</h2>
        <p>
            이 서비스는 <strong>제로 지식(Zero-Knowledge)</strong> 암호화를 사용합니다.
        </p>
        <ul>
            <li>비공개 일기는 비밀번호로 암호화되어 저장됩니다.</li>
            <li>서버 관리자도 암호화된 일기 내용을 볼 수 없습니다.</li>
            <li><strong>비밀번호를 잊어버리면 비공개 일기를 복구할 수 없습니다.</strong></li>
            <li>공개 일기는 평문으로 저장되며, 누구나 볼 수 있습니다.</li>
        </ul>
    </div>
</div>

<style>
.settings-container {
    max-width: 600px;
    margin: 0 auto;
    padding-bottom: 40px;
}

.settings-container h1 {
    color: var(--primary-color);
    margin-bottom: 30px;
}

.settings-card {
    background: var(--card-bg);
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: var(--shadow);
}

.settings-card h2 {
    font-size: 1.1rem;
    color: var(--text-color);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
}

.profile-url {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.profile-url code {
    background: #f5f5f5;
    padding: 8px 12px;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.9rem;
}

.btn-small {
    padding: 6px 12px;
    font-size: 0.85rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: var(--text-muted);
}

.info-value {
    font-weight: 500;
}

.warning-card {
    background-color: #fff8e1;
    border: 1px solid #ffecb3;
}

.warning-card h2 {
    color: #f57c00;
}

.warning-card ul {
    margin-left: 20px;
    line-height: 1.8;
}

.warning-card li {
    margin-bottom: 5px;
}

.success-message {
    padding: 15px;
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 6px;
    color: #155724;
    margin-bottom: 20px;
}

.error-text {
    display: block;
    color: var(--danger-color);
    font-size: 0.85rem;
    margin-top: 5px;
}
</style>

<script>
document.getElementById('username').addEventListener('input', function(e) {
    const username = e.target.value || '[username]';
    document.getElementById('profile-url-preview').textContent =
        '<?php echo e(url('/@')); ?>' + '/' + username;
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/resources/views/settings/index.blade.php ENDPATH**/ ?>