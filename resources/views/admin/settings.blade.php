@extends('layouts.admin')

@section('title', 'Cấu hình website & bảo mật')
@section('subtitle', 'Quản lý thông tin liên hệ và email khôi phục tài khoản')

@section('content')
<div class="admin-page-head"><div><span class="admin-kicker">CẤU HÌNH</span><h1>Website và tài khoản</h1><p>Thông tin công khai được tách riêng với email bảo mật của quản trị viên.</p></div></div>

<div class="admin-grid">
<section class="section-card settings-card">
    <div class="section-header"><div><h2 class="section-title">Thông tin hiển thị trên website</h2><p class="section-note">Khách hàng dùng các thông tin này để gọi điện hoặc liên hệ mạng xã hội.</p></div></div>
    <form method="post" action="{{ route('admin.settings.save') }}" data-confirm="Xác nhận cập nhật thông tin công khai trên website?" data-confirm-title="Cập nhật website">
        @csrf
        <div class="form-row"><div class="form-group"><label class="form-label">Tên thương hiệu *</label><input class="form-control-custom" name="brand_name" value="{{ old('brand_name', $settings['brand_name']) }}" required></div><div class="form-group"><label class="form-label">Số điện thoại *</label><input class="form-control-custom" name="phone" value="{{ old('phone', $settings['phone']) }}" required></div></div>
        <div class="form-group"><label class="form-label">Câu giới thiệu</label><input class="form-control-custom" name="tagline" value="{{ old('tagline', $settings['tagline']) }}"></div>
        <div class="form-row"><div class="form-group"><label class="form-label">Facebook cá nhân</label><input type="url" class="form-control-custom" name="facebook" value="{{ old('facebook', $settings['facebook']) }}"></div><div class="form-group"><label class="form-label">Fanpage</label><input type="url" class="form-control-custom" name="fanpage" value="{{ old('fanpage', $settings['fanpage']) }}"></div></div>
        <div class="form-group"><label class="form-label">Địa chỉ / khu vực phục vụ</label><input class="form-control-custom" name="address" value="{{ old('address', $settings['address']) }}"></div>
        <div class="form-group"><label class="form-label">Giới thiệu ngắn</label><textarea class="form-control-custom textarea" name="about" rows="5">{{ old('about', $settings['about']) }}</textarea></div>
        @include('admin.partials.seo-settings')
        <button class="btn-primary-custom">Lưu thông tin</button>
    </form>
</section>

<aside>
<!-- <section class="section-card security-card">
    <div class="form-card-icon">📧</div><h2 class="section-title">Email bảo mật</h2><p class="form-help">Email này không hiển thị ngoài website. Link đặt lại mật khẩu chỉ được gửi về đây.</p>
    @if(Str::endsWith(auth('admin')->user()->email, '.local'))<div class="security-warning">Email hiện tại là email mẫu, chưa thể nhận thư thật. Hãy đổi thành Gmail hoặc email doanh nghiệp.</div>@endif
    <form method="post" action="{{ route('admin.account.email.update') }}" data-confirm="Bạn chắc chắn muốn thay đổi email nhận liên kết khôi phục tài khoản?" data-confirm-title="Đổi email bảo mật">
        @csrf
        <div class="form-group"><label class="form-label">Email nhận thư</label><input type="email" class="form-control-custom" name="email" value="{{ old('email', auth('admin')->user()->email) }}" required autocomplete="email"></div>
        <div class="form-group"><label class="form-label">Mật khẩu hiện tại để xác nhận</label><input type="password" class="form-control-custom" name="current_password" required autocomplete="current-password"></div>
        <button class="btn-primary-custom">Cập nhật email</button>
    </form>
</section> -->

<section class="section-card security-card">
    <div class="form-card-icon">🔐</div><h2 class="section-title">Đổi mật khẩu qua email</h2><p class="form-help">Hệ thống gửi liên kết bảo mật có hiệu lực 30 phút. Không đổi mật khẩu trực tiếp trong trang admin.</p>
    <form method="post" action="{{ route('admin.password.email') }}" data-confirm="Gửi liên kết đặt lại mật khẩu đến {{ auth('admin')->user()->email }}?" data-confirm-title="Gửi email bảo mật">
        @csrf<input type="hidden" name="email" value="{{ auth('admin')->user()->email }}">
        <button class="btn-security-mail">✉ Gửi link đổi mật khẩu</button>
    </form>
</section>
</aside>
</div>
@endsection
