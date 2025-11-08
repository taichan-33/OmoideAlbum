<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // バリデーションルール
use Illuminate\Validation\Rules; // パスワードルール
use Illuminate\View\View; // View を返す型宣言
use Illuminate\Http\RedirectResponse; // Redirect を返す型宣言

class ProfileController extends Controller
{
    /**
     * プロフィール編集フォームを表示
     */
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => Auth::user(), // ログイン中のユーザー情報をビューに渡す
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
                // 自分自身のEmailはユニークチェックから除外する
                Rule::unique('users')->ignore($user->id),
            ],
            // パスワードは任意 (入力された場合のみ更新)
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. ユーザー情報を更新
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // パスワードが入力されている場合のみ、ハッシュ化して更新
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // 3. 保存
        $user->save();

        // 4. リダイレクト (成功メッセージを添えて)
        return redirect()->route('profile.edit')
            ->with('success', 'プロフィールを更新しました！');
    }
}
