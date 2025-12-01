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
     * Returns encrypted data - client must decrypt.
     */
    public function list(Request $request)
    {
        $user = Auth::user();

        $diaries = $user->diaries()
            ->select(['id', 'title', 'body_ciphertext', 'salt', 'iv', 'created_at', 'updated_at'])
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
     * Receives encrypted data from client.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'body_ciphertext' => ['required', 'string'],
            'salt' => ['required', 'string'],
            'iv' => ['required', 'string'],
        ]);

        $user = Auth::user();

        $diary = $user->diaries()->create([
            'title' => $validated['title'],
            'body_ciphertext' => $validated['body_ciphertext'],
            'salt' => $validated['salt'],
            'iv' => $validated['iv'],
        ]);

        return response()->json([
            'success' => true,
            'diary' => [
                'id' => $diary->id,
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
     * Receives encrypted data from client.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:500'],
            'body_ciphertext' => ['required', 'string'],
            'salt' => ['required', 'string'],
            'iv' => ['required', 'string'],
        ]);

        $user = Auth::user();

        $diary = $user->diaries()->find($id);

        if (!$diary) {
            return response()->json([
                'success' => false,
                'message' => 'Diary not found.',
            ], 404);
        }

        $diary->update([
            'title' => $validated['title'],
            'body_ciphertext' => $validated['body_ciphertext'],
            'salt' => $validated['salt'],
            'iv' => $validated['iv'],
        ]);

        return response()->json([
            'success' => true,
            'diary' => [
                'id' => $diary->id,
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
