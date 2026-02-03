/**
 * Diary page functionality
 * Handles CRUD operations with client-side encryption
 */

(function() {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // State
    let diaries = [];
    let currentDiaryId = null;
    let deleteTargetId = null;

    // User data (read from data attributes instead of inline script for CSP compliance)
    let USER_DATA = null;

    // DOM Elements
    let unlockModal, diaryApp, diaryList, editorModal, deleteModal;
    let unlockForm, unlockPassword, unlockError, unlockRemember;
    let diaryForm, diaryIdInput, diaryTitleInput, diaryContentInput, editorTitle;
    let diaryPublicCheckbox, publicWarning;

    /**
     * Initialize DOM element references
     */
    function initElements() {
        unlockModal = document.getElementById('unlock-modal');
        diaryApp = document.getElementById('diary-app');
        diaryList = document.getElementById('diary-list');
        editorModal = document.getElementById('editor-modal');
        deleteModal = document.getElementById('delete-modal');

        unlockForm = document.getElementById('unlock-form');
        unlockPassword = document.getElementById('unlock-password');
        unlockError = document.getElementById('unlock-error');
        unlockRemember = document.getElementById('unlock-remember');

        diaryForm = document.getElementById('diary-form');
        diaryIdInput = document.getElementById('diary-id');
        diaryTitleInput = document.getElementById('diary-title');
        diaryContentInput = document.getElementById('diary-content');
        editorTitle = document.getElementById('editor-title');

        diaryPublicCheckbox = document.getElementById('diary-public');
        publicWarning = document.getElementById('public-warning');

        // Read user data from data attributes (CSP-compliant)
        const userDataEl = document.getElementById('user-data');
        if (userDataEl) {
            USER_DATA = {
                id: parseInt(userDataEl.dataset.userId, 10),
                username: userDataEl.dataset.username || '',
                encryptionSalt: userDataEl.dataset.encryptionSalt
            };
        }
    }

    /**
     * Make API request
     */
    async function apiRequest(url, options = {}) {
        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            ...options.headers
        };

        const response = await fetch(url, {
            ...options,
            headers
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'API request failed');
        }

        return data;
    }

    /**
     * Show unlock modal error
     */
    function showUnlockError(message) {
        unlockError.textContent = message;
        unlockError.style.display = 'block';
    }

    /**
     * Handle unlock form submission
     */
    async function handleUnlock(event) {
        event.preventDefault();

        const password = unlockPassword.value;
        const salt = USER_DATA.encryptionSalt;
        const remember = unlockRemember.checked;

        try {
            // Initialize encryption with password
            const success = await DiaryEncryption.initialize(password, salt, remember);

            if (!success) {
                showUnlockError('Failed to initialize encryption');
                return;
            }

            // Hide unlock modal and show diary app
            unlockModal.style.display = 'none';
            diaryApp.style.display = 'block';

            // Load diaries
            await loadDiaries();

        } catch (error) {
            showUnlockError(error.message);
        }
    }

    /**
     * Load and display all diaries
     */
    async function loadDiaries() {
        diaryList.innerHTML = '<div class="loading">Loading...</div>';

        try {
            const data = await apiRequest('/api/diary');
            diaries = data.diaries;

            if (diaries.length === 0) {
                diaryList.innerHTML = `
                    <div class="empty-state">
                        <h3>No diary entries yet</h3>
                        <p>Click "New Entry" to write your first diary entry.</p>
                    </div>
                `;
                return;
            }

            // Decrypt and render diaries
            await renderDiaries();

        } catch (error) {
            diaryList.innerHTML = `<div class="error-message">${escapeHtml(error.message)}</div>`;
        }
    }

    /**
     * Render diary list
     */
    async function renderDiaries() {
        const fragment = document.createDocumentFragment();

        for (const diary of diaries) {
            if (diary.is_encrypted === false) {
                // Public diary - plaintext
                diary._decryptedTitle = diary.title;
                diary._decryptedContent = diary.body_ciphertext;
                diary._isPublic = true;

                const card = createDiaryCard(diary, diary.title, diary.body_ciphertext);
                fragment.appendChild(card);
            } else {
                // Private diary - encrypted
                try {
                    const decrypted = await DiaryEncryption.decryptDiaryEntry(diary.body_ciphertext, diary.iv);

                    diary._decryptedTitle = decrypted.title;
                    diary._decryptedContent = decrypted.content;
                    diary._isPublic = false;

                    const card = createDiaryCard(diary, decrypted.title, decrypted.content);
                    fragment.appendChild(card);

                } catch (error) {
                    console.error('Failed to decrypt diary:', diary.id, error);
                    const card = createEncryptedCard(diary);
                    fragment.appendChild(card);
                }
            }
        }

        diaryList.innerHTML = '';
        diaryList.appendChild(fragment);
    }

    /**
     * Create diary card element
     */
    function createDiaryCard(diary, title, content) {
        const card = document.createElement('div');
        card.className = 'diary-card';
        card.dataset.id = diary.id;

        const date = new Date(diary.created_at).toLocaleDateString('ko-KR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        // Truncate content for preview
        const preview = content.length > 150 ? content.substring(0, 150) + '...' : content;

        const header = document.createElement('div');
        header.className = 'diary-card-header';

        const titleWrapper = document.createElement('div');

        const titleEl = document.createElement('h3');
        titleEl.className = 'diary-card-title';
        titleEl.style.display = 'inline';
        titleEl.textContent = title;

        titleWrapper.appendChild(titleEl);

        // Add public/private badge
        const badge = document.createElement('span');
        badge.className = 'diary-card-badge ' + (diary._isPublic ? 'badge-public' : 'badge-private');
        badge.textContent = diary._isPublic ? '공개' : '비공개';
        titleWrapper.appendChild(badge);

        const dateEl = document.createElement('span');
        dateEl.className = 'diary-card-date';
        dateEl.textContent = date;

        header.appendChild(titleWrapper);
        header.appendChild(dateEl);

        const previewEl = document.createElement('p');
        previewEl.className = 'diary-card-preview';
        previewEl.textContent = preview;

        const actions = document.createElement('div');
        actions.className = 'diary-card-actions';

        const editBtn = document.createElement('button');
        editBtn.className = 'btn btn-secondary';
        editBtn.textContent = 'Edit';
        editBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            openEditor(diary);
        });

        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn btn-danger';
        deleteBtn.textContent = 'Delete';
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            confirmDelete(diary.id);
        });

        actions.appendChild(editBtn);
        actions.appendChild(deleteBtn);

        card.appendChild(header);
        card.appendChild(previewEl);
        card.appendChild(actions);

        // Click to view full content
        card.addEventListener('click', () => openEditor(diary));

        return card;
    }

    /**
     * Create card for encrypted (unreadable) entry
     */
    function createEncryptedCard(diary) {
        const card = document.createElement('div');
        card.className = 'diary-card';
        card.style.opacity = '0.6';

        const date = new Date(diary.created_at).toLocaleDateString('ko-KR');

        card.innerHTML = `
            <div class="diary-card-header">
                <h3 class="diary-card-title">[Unable to decrypt]</h3>
                <span class="diary-card-date">${escapeHtml(date)}</span>
            </div>
            <p class="diary-card-preview">This entry could not be decrypted. The password may have changed.</p>
        `;

        return card;
    }

    /**
     * Open diary editor (new or edit)
     */
    function openEditor(diary = null) {
        if (diary) {
            editorTitle.textContent = 'Edit Entry';
            diaryIdInput.value = diary.id;
            diaryTitleInput.value = diary._decryptedTitle || '';
            diaryContentInput.value = diary._decryptedContent || '';
            diaryPublicCheckbox.checked = diary._isPublic || false;
            currentDiaryId = diary.id;
        } else {
            editorTitle.textContent = 'New Entry';
            diaryIdInput.value = '';
            diaryTitleInput.value = '';
            diaryContentInput.value = '';
            diaryPublicCheckbox.checked = false;
            currentDiaryId = null;
        }

        // Show/hide warning based on checkbox state
        publicWarning.style.display = diaryPublicCheckbox.checked ? 'block' : 'none';

        editorModal.style.display = 'flex';
        diaryTitleInput.focus();
    }

    /**
     * Close diary editor
     */
    function closeEditor() {
        editorModal.style.display = 'none';
        currentDiaryId = null;
    }

    /**
     * Handle diary form submission
     */
    async function handleSave(event) {
        event.preventDefault();

        const title = diaryTitleInput.value.trim();
        const content = diaryContentInput.value.trim();
        const id = diaryIdInput.value;
        const isPublic = diaryPublicCheckbox.checked;

        if (!title || !content) {
            alert('Title and content are required');
            return;
        }

        const saveBtn = document.getElementById('save-btn');
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';

        try {
            let payload;

            if (isPublic) {
                // Public diary - send plaintext
                payload = {
                    is_public: true,
                    title: title,
                    content: content
                };
            } else {
                // Private diary - encrypt
                const encrypted = await DiaryEncryption.encryptDiaryEntry(title, content);
                payload = {
                    is_public: false,
                    title: '[encrypted]',
                    body_ciphertext: encrypted.ciphertext,
                    salt: encrypted.salt,
                    iv: encrypted.iv
                };
            }

            let data;
            if (id) {
                // Update existing diary
                data = await apiRequest(`/api/diary/${id}`, {
                    method: 'PUT',
                    body: JSON.stringify(payload)
                });
            } else {
                // Create new diary
                data = await apiRequest('/api/diary', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });
            }

            closeEditor();
            await loadDiaries();

        } catch (error) {
            alert('Failed to save: ' + error.message);
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save';
        }
    }

    /**
     * Show delete confirmation modal
     */
    function confirmDelete(id) {
        deleteTargetId = id;
        deleteModal.style.display = 'flex';
    }

    /**
     * Close delete modal
     */
    function closeDeleteModal() {
        deleteModal.style.display = 'none';
        deleteTargetId = null;
    }

    /**
     * Handle delete confirmation
     */
    async function handleDelete() {
        if (!deleteTargetId) return;

        const confirmBtn = document.getElementById('confirm-delete');
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Deleting...';

        try {
            await apiRequest(`/api/diary/${deleteTargetId}`, {
                method: 'DELETE'
            });

            closeDeleteModal();
            await loadDiaries();

        } catch (error) {
            alert('Failed to delete: ' + error.message);
        } finally {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Delete';
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    /**
     * Initialize the diary page
     */
    async function init() {
        initElements();

        // Try to restore saved key from IndexedDB
        if (DiaryEncryption.isKeySaved()) {
            const restored = await DiaryEncryption.tryRestoreKey();
            if (restored) {
                unlockModal.style.display = 'none';
                diaryApp.style.display = 'block';
                loadDiaries();
                setupEventListeners();
                return;
            }
        }

        // Check if encryption is already initialized (from login/register)
        if (DiaryEncryption.isInitialized()) {
            unlockModal.style.display = 'none';
            diaryApp.style.display = 'block';
            loadDiaries();
        } else {
            // Show unlock modal
            unlockModal.style.display = 'flex';
            diaryApp.style.display = 'none';
        }

        setupEventListeners();
    }

    /**
     * Setup event listeners (called once)
     */
    let _listenersSetup = false;
    function setupEventListeners() {
        if (_listenersSetup) return;
        _listenersSetup = true;

        unlockForm.addEventListener('submit', handleUnlock);
        diaryForm.addEventListener('submit', handleSave);

        document.getElementById('new-diary-btn').addEventListener('click', () => openEditor());
        document.getElementById('close-editor').addEventListener('click', closeEditor);
        document.getElementById('cancel-editor').addEventListener('click', closeEditor);

        document.getElementById('cancel-delete').addEventListener('click', closeDeleteModal);
        document.getElementById('confirm-delete').addEventListener('click', handleDelete);

        // Public checkbox toggle
        diaryPublicCheckbox.addEventListener('change', () => {
            publicWarning.style.display = diaryPublicCheckbox.checked ? 'block' : 'none';
        });

        // Close modals on backdrop click
        editorModal.addEventListener('click', (e) => {
            if (e.target === editorModal) closeEditor();
        });

        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) closeDeleteModal();
        });
    }

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', init);
})();
