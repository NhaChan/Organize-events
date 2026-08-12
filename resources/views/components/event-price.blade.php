@php
    $originalPrice = filled($event->original_price) ? (int) $event->original_price : null;
    $salePrice = filled($event->sale_price) ? (int) $event->sale_price : null;
    $currentPrice = $salePrice ?? $originalPrice;
    $discount = $originalPrice && $salePrice && $salePrice < $originalPrice
        ? (int) round((1 - ($salePrice / $originalPrice)) * 100)
        : null;
    $formatPrice = static fn (int $price): string => number_format($price, 0, ',', '.').'₫';
@endphp

<div {{ $attributes->class(['product-price']) }}>
    @if($currentPrice === null)
        <strong class="price-contact">Giá liên hệ</strong>
    @else
        <span class="price-label"></span>
        @if($originalPrice !== null && $salePrice !== null)
            <del>{{ $formatPrice($originalPrice) }}</del>
        @endif
        <strong class="price-current">{{ $formatPrice($currentPrice) }}</strong>
        @if($discount)
            <span class="price-discount">-{{ $discount }}%</span>
        @endif
    @endif
</div>
