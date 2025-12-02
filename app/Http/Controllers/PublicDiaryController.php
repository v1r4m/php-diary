<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Diary;
use Illuminate\Http\Request;

class PublicDiaryController extends Controller
{
    /**
     * Show user's public diary list
     */
    public function index(string $username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $diaries = $user->publicDiaries()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('public.profile', [
            'user' => $user,
            'diaries' => $diaries,
        ]);
    }

    /**
     * Show a single public diary entry
     */
    public function show(string $username, int $diaryId)
    {
        $user = User::where('username', $username)->firstOrFail();

        $diary = Diary::where('id', $diaryId)
            ->where('user_id', $user->id)
            ->where('is_encrypted', false)
            ->firstOrFail();

        return view('public.diary', [
            'user' => $user,
            'diary' => $diary,
        ]);
    }
}
