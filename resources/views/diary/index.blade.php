@extends('layouts.app')

@section('title', 'My Diary - Encrypted Diary')

@section('content')
<div class="diary-container">
    <!-- Password unlock modal -->
    <div id="unlock-modal" class="modal">
        <div class="modal-content">
            <div class="unlock-header">
                <div class="unlock-icon">🔐</div>
                <h2>다이어리 암호</h2>
                <p class="unlock-subtitle">서버에 저장되지 않는 나만의 열쇠</p>
            </div>

            <ul class="unlock-info">
                <li><span class="dot dot-blue"></span>로그인 비밀번호와 별개예요</li>
                <li><span class="dot dot-green"></span>이전과 같은 암호 → 이전 일기도 열림</li>
                <li><span class="dot dot-yellow"></span>이전과 다른 암호 → 이전 일기 안 열림</li>
            </ul>

            <div id="unlock-error" class="error-message" style="display: none;"></div>

            <form id="unlock-form">
                <div class="form-group">
                    <label for="unlock-password">Password</label>
                    <input type="password" id="unlock-password" required autocomplete="current-password">
                </div>
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="unlock-remember">
                        <span>이 브라우저에서 기억하기</span>
                    </label>
                    <span class="form-hint">체크하면 암호를 다시 입력하지 않아도 됩니다.</span>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Unlock</button>
            </form>
        </div>
    </div>

    <!-- Main diary interface (hidden until unlocked) -->
    <div id="diary-app" style="display: none;">
        <div class="diary-header">
            <h1>My Diary</h1>
            <button id="new-diary-btn" class="btn btn-primary">New Entry</button>
        </div>

        <!-- Diary list -->
        <div id="diary-list" class="diary-list">
            <div class="loading">Loading...</div>
        </div>

        <!-- Diary editor modal -->
        <div id="editor-modal" class="modal" style="display: none;">
            <div class="modal-content modal-large">
                <div class="modal-header">
                    <h2 id="editor-title">New Entry</h2>
                    <button id="close-editor" class="btn-close">&times;</button>
                </div>

                <form id="diary-form">
                    <input type="hidden" id="diary-id">

                    <div class="form-group">
                        <label for="diary-title">Title</label>
                        <input type="text" id="diary-title" required maxlength="200" placeholder="Enter title...">
                    </div>

                    <div class="form-group">
                        <label for="diary-content">Content</label>
                        <textarea id="diary-content" rows="15" required placeholder="Write your diary entry..."></textarea>
                    </div>

                    <div class="form-group visibility-toggle">
                        <label class="checkbox-label">
                            <input type="checkbox" id="diary-public">
                            <span>공개 일기로 설정</span>
                        </label>
                        <p class="visibility-warning" id="public-warning" style="display: none;">
                            공개 일기는 암호화되지 않으며, 누구나 볼 수 있습니다.
                            <br>
                            <small>공개 주소는 우측 상단 '설정'페이지에서 설정할 수 있어요.</small>
                        </p>
                    </div>

                    <div class="form-actions">
                        <button type="button" id="cancel-editor" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="save-btn">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete confirmation modal -->
        <div id="delete-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <h2>Delete Entry</h2>
                <p>Are you sure you want to delete this diary entry? This action cannot be undone.</p>
                <div class="form-actions">
                    <button id="cancel-delete" class="btn btn-secondary">Cancel</button>
                    <button id="confirm-delete" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="user-data"
     data-user-id="{{ auth()->user()->id }}"
     data-username="{{ auth()->user()->username ?? '' }}"
     data-encryption-salt="{{ auth()->user()->encryption_salt }}"
     style="display: none;"></div>
@endsection

@section('scripts')
<script src="{{ asset('js/diary.js') }}"></script>
@endsection
