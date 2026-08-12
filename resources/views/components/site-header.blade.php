@props(['settings', 'categories'])

<header class="site-header">
    <div class="site-nav">
        <a class="brand" href="{{ route('home') }}">
            <span class="brand-mark">🎈</span>
            <span>{{ $settings['brand_name'] }}<small>Party & Entertainment</small></span>
        </a>

        <button class="menu-button" type="button" aria-label="Mở menu" aria-expanded="false" aria-controls="main-navigation" data-menu-toggle>☰</button>

        <nav class="main-nav" id="main-navigation" aria-label="Điều hướng chính">
            <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Trang chủ</a>

            @foreach($categories as $index => $category)
                @php
                    $currentCategory = request()->route('category');
                    $categoryIsActive = $currentCategory instanceof \App\Models\Category
                        && ($currentCategory->is($category) || $currentCategory->parent_id === $category->id);
                @endphp
                <div @class(['nav-category', 'active' => $categoryIsActive]) data-category-menu>
                    <div class="nav-category-row">
                        <a class="nav-category-link" href="{{ route('category', $category) }}" @if($currentCategory instanceof \App\Models\Category && $currentCategory->is($category)) aria-current="page" @endif>{{ $category->name }}</a>
                        @if($category->children->isNotEmpty())
                            <button class="nav-category-toggle" type="button" aria-label="Mở mục con của {{ $category->name }}" aria-expanded="false" data-category-toggle>
                                <svg aria-hidden="true" viewBox="0 0 12 8" focusable="false"><path d="M1 1.25 6 6.25l5-5" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                        @endif
                    </div>
                    @if($category->children->isNotEmpty())
                        <div class="nav-category-dropdown">
                            <a class="nav-category-all" href="{{ route('category', $category) }}">Tất cả {{ $category->name }}</a>
                            @foreach($category->children as $child)
                                <a class="{{ $currentCategory instanceof \App\Models\Category && $currentCategory->is($child) ? 'active' : '' }}" href="{{ route('category', $child) }}" @if($currentCategory instanceof \App\Models\Category && $currentCategory->is($child)) aria-current="page" @endif>{{ $child->name }} <span aria-hidden="true">›</span></a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            <a class="{{ request()->routeIs('events', 'event') ? 'active' : '' }}" href="{{ route('events') }}" @if(request()->routeIs('events', 'event')) aria-current="page" @endif>Bài viết & hình ảnh</a>
            <a class="nav-contact" href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}">☎ {{ $settings['phone'] }}</a>
        </nav>
    </div>
</header>

<script>
(() => {
    const mobileToggle = document.querySelector('[data-menu-toggle]');
    const navigation = document.getElementById('main-navigation');
    const categoryMenus = [...document.querySelectorAll('[data-category-menu]')];

    const closeCategories = except => {
        categoryMenus.forEach(menu => {
            if (menu === except) return;
            menu.classList.remove('open');
            menu.querySelector('[data-category-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    };

    mobileToggle?.addEventListener('click', () => {
        const open = navigation.classList.toggle('open');
        mobileToggle.setAttribute('aria-expanded', String(open));
        mobileToggle.setAttribute('aria-label', open ? 'Đóng menu' : 'Mở menu');
        if (! open) closeCategories();
    });

    document.addEventListener('click', event => {
        const toggle = event.target.closest('[data-category-toggle]');
        if (! toggle) {
            if (! event.target.closest('[data-category-menu]')) closeCategories();
            return;
        }
        event.stopPropagation();
        const menu = toggle.closest('[data-category-menu]');
        closeCategories(menu);
        const open = menu.classList.toggle('open');
        toggle.setAttribute('aria-expanded', String(open));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeCategories();
            navigation?.classList.remove('open');
            mobileToggle?.setAttribute('aria-expanded', 'false');
        }
    });
})();
</script>
