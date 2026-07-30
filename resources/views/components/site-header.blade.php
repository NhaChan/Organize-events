@props(['settings', 'categories'])

<header class="site-header">
    <div class="site-nav">
        <a class="brand" href="{{ route('home') }}">
            <span class="brand-mark">🎈</span>
            <span>{{ $settings['brand_name'] }}<small>Party & Entertainment</small></span>
        </a>

        <button class="menu-button" type="button" aria-label="Mở menu" aria-expanded="false" aria-controls="main-navigation" data-menu-toggle>☰</button>

        <nav class="main-nav" id="main-navigation" aria-label="Điều hướng chính">
            <a href="{{ route('home') }}">Trang chủ</a>

            <div class="nav-services" data-service-menu>
                <div class="nav-service-row">
                    <a class="nav-service-link" href="{{ route('services') }}">Dịch vụ</a>
                    <button class="nav-service-toggle" type="button" aria-label="Mở danh sách dịch vụ" aria-expanded="false" aria-controls="service-mega-menu" data-service-toggle>
                        <span aria-hidden="true">⌄</span>
                    </button>
                </div>

                <div class="mega-menu" id="service-mega-menu">
                    <div class="mega-panel">
                        <div class="mega-heading">
                            <div>
                                <span>KHÁM PHÁ DỊCH VỤ</span>
                                <strong>Chọn nhanh dịch vụ bạn quan tâm</strong>
                            </div>
                            <a href="{{ route('services') }}">Xem tất cả <span aria-hidden="true">→</span></a>
                        </div>

                        <div class="mega-grid">
                            @forelse($categories as $index => $category)
                                <section class="mega-group tone-{{ ($index % 5) + 1 }}">
                                    <a class="mega-group-title" href="{{ route('category', $category) }}">
                                        <span class="mega-group-mark" aria-hidden="true"></span>
                                        <span>{{ $category->name }}</span>
                                        <b aria-hidden="true">→</b>
                                    </a>

                                    @if($category->children->isNotEmpty())
                                        <div class="mega-links">
                                            @foreach($category->children as $child)
                                                <a href="{{ route('category', $child) }}">
                                                    <span>{{ $child->name }}</span><b aria-hidden="true">›</b>
                                                </a>
                                            @endforeach
                                        </div>
                                    @elseif($category->description)
                                        <p>{{ Str::limit($category->description, 80) }}</p>
                                    @else
                                        <a class="mega-direct" href="{{ route('category', $category) }}">Xem thông tin và hình ảnh</a>
                                    @endif
                                </section>
                            @empty
                                <p class="mega-empty">Các nhóm dịch vụ đang được cập nhật.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ route('events') }}">Bài viết & hình ảnh</a>
            <a class="nav-contact" href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}">☎ {{ $settings['phone'] }}</a>
        </nav>
    </div>
</header>

<script>
(() => {
    const mobileToggle = document.querySelector('[data-menu-toggle]');
    const navigation = document.getElementById('main-navigation');
    const serviceMenu = document.querySelector('[data-service-menu]');
    const serviceToggle = document.querySelector('[data-service-toggle]');

    const closeServices = () => {
        serviceMenu?.classList.remove('open');
        serviceToggle?.setAttribute('aria-expanded', 'false');
    };

    mobileToggle?.addEventListener('click', () => {
        const open = navigation.classList.toggle('open');
        mobileToggle.setAttribute('aria-expanded', String(open));
        mobileToggle.setAttribute('aria-label', open ? 'Đóng menu' : 'Mở menu');
        if (! open) closeServices();
    });

    serviceToggle?.addEventListener('click', (event) => {
        event.stopPropagation();
        const open = serviceMenu.classList.toggle('open');
        serviceToggle.setAttribute('aria-expanded', String(open));
    });

    document.addEventListener('click', (event) => {
        if (! serviceMenu?.contains(event.target)) closeServices();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeServices();
            navigation?.classList.remove('open');
            mobileToggle?.setAttribute('aria-expanded', 'false');
        }
    });
})();
</script>
