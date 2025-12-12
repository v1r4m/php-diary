<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Diary;
use Illuminate\Http\Request;

class PublicDiaryController extends Controller
{
    /**
     * Show user's public diary list as calendar
     */
    public function index(string $username, Request $request)
    {
        $user = User::where('username', $username)->firstOrFail();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $diaries = $user->publicDiaries()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get()
            ->keyBy(fn($diary) => $diary->created_at->format('Y-m-d'));

        return view('public.profile', [
            'user' => $user,
            'diaries' => $diaries,
            'year' => (int) $year,
            'month' => (int) $month,
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
