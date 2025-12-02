<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Show settings page
     */
    public function index()
    {
        return view('settings.index', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update username
     */
    public function updateUsername(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
        ], [
            'username.required' => '사용자명을 입력해주세요.',
            'username.min' => '사용자명은 최소 3자 이상이어야 합니다.',
            'username.max' => '사용자명은 최대 30자까지 가능합니다.',
            'username.regex' => '사용자명은 영문, 숫자, 밑줄(_), 하이픈(-)만 사용할 수 있습니다.',
            'username.unique' => '이미 사용 중인 사용자명입니다.',
        ]);

        $user->username = $validated['username'];
        $user->save();

        return redirect()->route('settings.index')->with('success', '사용자명이 변경되었습니다.');
    }
}
