<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function form()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate(['username' => ['required', 'string'], 'password' => ['required', 'string']]);
        $admin = Admin::where('username', $credentials['username'])->first();

        if ($admin && Hash::check($credentials['password'], $admin->password)) {
            Auth::guard('admin')->login($admin, $request->boolean('remember'));
            $request->session()->regenerate();
            $admin->update(['last_login' => now()]);

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['username' => 'Tên đăng nhập hoặc mật khẩu không đúng.'])->onlyInput('username');
    }

    public function forgotPasswordForm()
    {
        return view('admin.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
        $status = PasswordBroker::broker('admins')->sendResetLink($request->only('email'));

        if ($status === PasswordBroker::RESET_THROTTLED) {
            return back()->withErrors(['email' => 'Bạn vừa yêu cầu gửi mail. Vui lòng chờ trước khi thử lại.']);
        }

        return back()->with('status', 'Nếu email trùng với tài khoản quản trị, liên kết đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra cả hộp thư spam.');
    }

    public function resetPasswordForm(Request $request, string $token)
    {
        return view('admin.reset-password', ['token' => $token, 'email' => $request->string('email')->toString()]);
    }

    public function resetPassword(Request $request)
    {
        $credentials = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $status = PasswordBroker::broker('admins')->reset($credentials, function (Admin $admin, string $password) {
            $admin->forceFill(['password' => $password]);
            $admin->setRememberToken(Str::random(60));
            $admin->save();
            event(new PasswordReset($admin));
        });

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            return back()->withErrors(['email' => 'Liên kết không hợp lệ, đã hết hạn hoặc email không đúng.'])->withInput($request->only('email'));
        }

        return redirect()->route('admin.login')->with('status', 'Đổi mật khẩu thành công. Bạn có thể đăng nhập bằng mật khẩu mới.');
    }

    public function updateEmail(Request $request)
    {
        $admin = $request->user('admin');
        $data = $request->validate([
            'email' => ['required', 'email', 'max:100', Rule::unique('admins', 'email')->ignore($admin)],
            'current_password' => ['required', 'current_password:admin'],
        ]);
        $admin->update(['email' => $data['email']]);

        return back()->with('success', 'Đã cập nhật email bảo mật. Các liên kết đổi mật khẩu sẽ được gửi về email này.');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'Bạn đã đăng xuất an toàn.');
    }
}
