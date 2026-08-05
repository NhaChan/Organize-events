<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title') · Quản trị SEO</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=4">
    <link rel="alternate icon" type="image/png" sizes="256x256" href="{{ asset('favicon-balloon.png') }}?v=4">
    <link rel="apple-touch-icon" href="{{ asset('favicon-balloon.png') }}?v=4">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
    @include('admin.legacy-style')
    <link href="{{ asset('css/admin-extra.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin-v2.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin-security.css') }}" rel="stylesheet">
</head>
<body>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
<aside class="sidebar" id="admin-sidebar">
    <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
        <span class="sidebar-logo">🎈</span>
        <span class="sidebar-brand-text">Minh Triều Party<small>Quản trị nội dung SEO</small></span>
    </a>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Tổng quan</div>
        <a class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span class="nav-icon">▦</span><span>Dashboard</span></a>

        <div class="nav-section-label">Nội dung SEO</div>
        <a class="nav-item {{ request()->routeIs('admin.events*') ? 'active' : '' }}" href="{{ route('admin.events') }}"><span class="nav-icon">▤</span><span>Bài viết</span></a>
        <a class="nav-item {{ request()->routeIs('admin.categories*') ? 'active' : '' }}" href="{{ route('admin.categories') }}"><span class="nav-icon">◇</span><span>Dịch vụ & danh mục</span></a>

        <div class="nav-section-label">Website</div>
        <a class="nav-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}" href="{{ route('admin.settings') }}"><span class="nav-icon">⚙</span><span>Thông tin liên hệ</span></a>
        <a class="nav-item" href="{{ route('home') }}" target="_blank" rel="noopener"><span class="nav-icon">↗</span><span>Xem website</span></a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-row"><div class="avatar">{{ mb_strtoupper(mb_substr(auth('admin')->user()->username, 0, 1)) }}</div><div class="user-info"><div class="user-name">{{ auth('admin')->user()->full_name ?: auth('admin')->user()->username }}</div><div class="user-role">Quản trị viên</div></div></div>
        <form method="post" action="{{ route('admin.logout') }}" data-confirm="Bạn chắc chắn muốn đăng xuất khỏi trang quản trị?" data-confirm-title="Xác nhận đăng xuất">@csrf<button class="logout-btn">↪ Đăng xuất</button></form>
    </div>
</aside>

<div class="main-wrap">
    <header class="topbar">
        <div class="topbar-heading"><button class="hamburger" id="sidebar-toggle" type="button" aria-label="Mở menu">☰</button><div><div class="topbar-title">@yield('title')</div><div class="topbar-sub">@yield('subtitle', 'Quản lý nội dung và tối ưu hiển thị tìm kiếm')</div></div></div>
        <div class="topbar-right"><a class="btn-primary-custom" href="{{ route('admin.events.create') }}">＋ Đăng bài mới</a></div>
    </header>

    <main class="page-content">
        @if(session('success'))<div class="flash-msg success">✓ {{ session('success') }}</div>@endif
        @if(session('status'))<div class="flash-msg success">✓ {{ session('status') }}</div>@endif
        @if($errors->any())<div class="flash-msg error">{{ implode(' · ', $errors->all()) }}</div>@endif
        @yield('content')
    </main>
</div>

<div class="confirm-modal" id="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirm-title" hidden>
    <div class="confirm-backdrop" data-modal-close></div>
    <div class="confirm-dialog">
        <div class="confirm-icon">?</div><h2 id="confirm-title">Xác nhận thao tác</h2><p id="confirm-message">Bạn có chắc chắn muốn tiếp tục?</p>
        <div class="confirm-actions"><button type="button" class="confirm-cancel" data-modal-close>Hủy</button><button type="button" class="confirm-accept" id="confirm-accept">Đồng ý</button></div>
    </div>
</div>

<script>
const sidebar=document.getElementById('admin-sidebar');const overlay=document.getElementById('sidebar-overlay');const closeMenu=()=>{sidebar.classList.remove('open');overlay.classList.remove('show')};document.getElementById('sidebar-toggle').addEventListener('click',()=>{sidebar.classList.toggle('open');overlay.classList.toggle('show')});overlay.addEventListener('click',closeMenu);
const confirmModal=document.getElementById('confirm-modal');const confirmTitle=document.getElementById('confirm-title');const confirmMessage=document.getElementById('confirm-message');const confirmAccept=document.getElementById('confirm-accept');let pendingForm=null;let pendingSubmitter=null;const closeConfirm=()=>{confirmModal.hidden=true;document.body.classList.remove('modal-open');pendingForm=null;pendingSubmitter=null};document.addEventListener('submit',event=>{const form=event.target;if(!form.dataset.confirm||form.dataset.confirmed==='1'){delete form.dataset.confirmed;return}event.preventDefault();pendingForm=form;pendingSubmitter=event.submitter;confirmTitle.textContent=form.dataset.confirmTitle||'Xác nhận thao tác';confirmMessage.textContent=form.dataset.confirm;confirmModal.hidden=false;document.body.classList.add('modal-open');confirmAccept.focus()});confirmAccept.addEventListener('click',()=>{if(!pendingForm)return;const form=pendingForm;const submitter=pendingSubmitter;form.dataset.confirmed='1';confirmModal.hidden=true;document.body.classList.remove('modal-open');submitter?form.requestSubmit(submitter):form.requestSubmit()});document.querySelectorAll('[data-modal-close]').forEach(button=>button.addEventListener('click',closeConfirm));document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!confirmModal.hidden)closeConfirm()});
</script>
</body>
</html>
