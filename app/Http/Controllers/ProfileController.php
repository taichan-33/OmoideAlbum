<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;  // Storageファサードを追加
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;  // Inertiaを追加
use Inertia\Response;  // Inertia Response型

class ProfileController extends Controller
{
    /**
     * プロフィール編集フォームを表示
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'badges' => $request->user()->badges,  // バッジ情報を渡す
        ]);
    }

    /**
     * プロフィール情報を更新
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // 1. バリデーション
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],  // 10MB Max
            'show_bot_status' => ['boolean'],
        ]);

        // 2. ユーザー情報を更新
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->show_bot_status = $validated['show_bot_status'] ?? true;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // 3. 画像アップロード処理
        if ($request->hasFile('profile_photo')) {
            // 古い画像があれば削除
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // 新しい画像を保存
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
        }

        // 4. 保存
        $user->save();

        // 5. リダイレクト
        return redirect()->route('profile.edit')->with('success', '設定を更新しました');
    }
}
