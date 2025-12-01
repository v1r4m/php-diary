@extends('layouts.app')

@section('title', 'My Diary - Encrypted Diary')

@section('content')
<div class="diary-container">
    <!-- Password unlock modal -->
    <div id="unlock-modal" class="modal">
        <div class="modal-content">
            <h2>Unlock Your Diary</h2>
            <p>Enter your password to decrypt your diary entries.</p>

            <div id="unlock-error" class="error-message" style="display: none;"></div>

            <form id="unlock-form">
                <div class="form-group">
                    <label for="unlock-password">Password</label>
                    <input type="password" id="unlock-password" required autocomplete="current-password">
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
     data-encryption-salt="{{ auth()->user()->encryption_salt }}"
     data-has-diary-token="{{ auth()->user()->diary_token_hash ? 'true' : 'false' }}"
     style="display: none;"></div>
@endsection

@section('scripts')
<script src="{{ asset('js/diary.js') }}"></script>
@endsection
