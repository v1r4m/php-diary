<?php

namespace App\Http\Controllers;

use App\Models\Diary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiaryController extends Controller
{
    /**
     * Display the diary index page.
     */
    public function index()
    {
        $user = Auth::user();

        // Generate encryption_salt if missing (for users created before this feature)
        if (empty($user->encryption_salt)) {
            $user->encryption_salt = \App\Models\User::generateEncryptionSalt();
            $user->save();
        }

        return view('diary.index');
    }

    /**
     * Get list of diaries for the authenticated user (API).
     * Returns encrypted data (client must decrypt) or plaintext for public entries.
     */
    public function list(Request $request)
    {
        $user = Auth::user();

        $diaries = $user->diaries()
            ->select(['id', 'is_encrypted', 'title', 'body_ciphertext', 'salt', 'iv', 'created_at', 'updated_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'diaries' => $diaries,
        ]);
    }

    /**
     * Get a single diary entry (API).
     */
    public function show(Request $request, int $id)
    {
        $user = Auth::user();

        $diary = $user->diaries()->find($id);

        if (!$diary) {
            return response()->json([
                'success' => false,
                'message' => 'Diary not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'diary' => [
                'id' => $diary->id,
                'is_encrypted' => $diary->is_encrypted,
                'title' => $diary->title,
                'body_ciphertext' => $diary->body_ciphertext,
                'salt' => $diary->salt,
                'iv' => $diary->iv,
                'created_at' => $diary->created_at,
                'updated_at' => $diary->updated_at,
            ],
        ]);
    }

    /**
     * Store a new diary entry (API).
     * Receives encrypted data from client, or plaintext for public entries.
     */
    public function store(Request $request)
    {
        $isPublic = $request->boolean('is_public', false);

        if ($isPublic) {
            // Public diary - plaintext
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:500'],
                'content' => ['required', 'string'],
            ]);

            $user = Auth::user();

            $diary = $user->diaries()->create([
                'is_encrypted' => false,
                'title' => $validated['title'],
                'body_ciphertext' => $validated['content'], // Plaintext stored here
                'salt' => null,
                'iv' => null,
            ]);
        } else {
            // Private diary - encrypted
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:500'],
                'body_ciphertext' => ['required', 'string'],
                'salt' => ['required', 'string'],
                'iv' => ['required', 'string'],
            ]);

            $user = Auth::user();

            $diary = $user->diaries()->create([
                'is_encrypted' => true,
                'title' => $validated['title'],
                'body_ciphertext' => $validated['body_ciphertext'],
                'salt' => $validated['salt'],
                'iv' => $validated['iv'],
            ]);
        }

        return response()->json([
            'success' => true,
            'diary' => [
                'id' => $diary->id,
                'is_encrypted' => $diary->is_encrypted,
                'title' => $diary->title,
                'body_ciphertext' => $diary->body_ciphertext,
                'salt' => $diary->salt,
                'iv' => $diary->iv,
                'created_at' => $diary->created_at,
                'updated_at' => $diary->updated_at,
            ],
        ], 201);
    }

    /**
     * Update an existing diary entry (API).
     * Receives encrypted data from client, or plaintext for public entries.
     */
    public function update(Request $request, int $id)
    {
        $user = Auth::user();

        $diary = $user->diaries()->find($id);

        if (!$diary) {
            return response()->json([
                'success' => false,
                'message' => 'Diary not found.',
            ], 404);
        }

        $isPublic = $request->boolean('is_public', false);

        if ($isPublic) {
            // Public diary - plaintext
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:500'],
                'content' => ['required', 'string'],
            ]);

            $diary->update([
                'is_encrypted' => false,
                'title' => $validated['title'],
                'body_ciphertext' => $validated['content'],
                'salt' => null,
                'iv' => null,
            ]);
        } else {
            // Private diary - encrypted
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:500'],
                'body_ciphertext' => ['required', 'string'],
                'salt' => ['required', 'string'],
                'iv' => ['required', 'string'],
            ]);

            $diary->update([
                'is_encrypted' => true,
                'title' => $validated['title'],
                'body_ciphertext' => $validated['body_ciphertext'],
                'salt' => $validated['salt'],
                'iv' => $validated['iv'],
            ]);
        }

        return response()->json([
            'success' => true,
            'diary' => [
                'id' => $diary->id,
                'is_encrypted' => $diary->is_encrypted,
                'title' => $diary->title,
                'body_ciphertext' => $diary->body_ciphertext,
                'salt' => $diary->salt,
                'iv' => $diary->iv,
                'created_at' => $diary->created_at,
                'updated_at' => $diary->updated_at,
            ],
        ]);
    }

    /**
     * Delete a diary entry (API).
     */
    public function destroy(Request $request, int $id)
    {
        $user = Auth::user();

        $diary = $user->diaries()->find($id);

        if (!$diary) {
            return response()->json([
                'success' => false,
                'message' => 'Diary not found.',
            ], 404);
        }

        $diary->delete();

        return response()->json([
            'success' => true,
            'message' => 'Diary deleted successfully.',
        ]);
    }
}
