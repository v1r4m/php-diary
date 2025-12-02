@extends('layouts.app')

@section('title', '설정 - Encrypted Diary')

@section('content')
<div class="settings-container">
    <h1>설정</h1>

    @if(session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif

    <div class="settings-card">
        <h2>프로필 설정</h2>

        <form action="{{ route('settings.username') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="username">사용자명 (Username)</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username', $user->username) }}"
                    placeholder="예: viram"
                    pattern="[a-zA-Z0-9_-]+"
                    minlength="3"
                    maxlength="30"
                    required
                >
                <span class="form-hint">
                    영문, 숫자, 밑줄(_), 하이픈(-) 사용 가능 (3~30자)
                </span>
                @error('username')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label>공개 프로필 주소</label>
                <div class="profile-url">
                    <code id="profile-url-preview">{{ url('/@' . ($user->username ?: '[username]')) }}</code>
                    @if($user->username)
                        <a href="{{ route('public.profile', $user->username) }}" target="_blank" class="btn btn-small">
                            프로필 보기
                        </a>
                    @endif
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
            <span class="info-value">{{ $user->email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">이름</span>
            <span class="info-value">{{ $user->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">가입일</span>
            <span class="info-value">{{ $user->created_at->format('Y년 m월 d일') }}</span>
        </div>
    </div>

    <div class="settings-card">
        <h2>암호화 키 관리</h2>
        <p>현재 브라우저에 저장된 암호화 키 상태:</p>
        <div class="info-row">
            <span class="info-label">키 저장 상태</span>
            <span class="info-value" id="key-status">확인 중...</span>
        </div>
        <div id="clear-key-section" style="display: none; margin-top: 15px;">
            <button type="button" class="btn btn-danger" id="clear-key-btn">저장된 키 삭제</button>
            <span class="form-hint">삭제하면 다음 접속 시 비밀번호를 다시 입력해야 합니다.</span>
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
        '{{ url('/@') }}' + '/' + username;
});

// Check key status
document.addEventListener('DOMContentLoaded', function() {
    const keyStatus = document.getElementById('key-status');
    const clearKeySection = document.getElementById('clear-key-section');
    const clearKeyBtn = document.getElementById('clear-key-btn');

    if (DiaryEncryption.isKeySaved()) {
        keyStatus.textContent = '저장됨';
        keyStatus.style.color = '#28a745';
        clearKeySection.style.display = 'block';
    } else {
        keyStatus.textContent = '저장되지 않음';
        keyStatus.style.color = '#6c757d';
    }

    clearKeyBtn.addEventListener('click', async function() {
        if (confirm('저장된 암호화 키를 삭제하시겠습니까?\n다음 접속 시 비밀번호를 다시 입력해야 합니다.')) {
            await DiaryEncryption.clearAll();
            keyStatus.textContent = '저장되지 않음';
            keyStatus.style.color = '#6c757d';
            clearKeySection.style.display = 'none';
            alert('암호화 키가 삭제되었습니다.');
        }
    });
});
</script>
@endsection
