{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url><loc>{{ route('home') }}</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>
<url><loc>{{ route('services') }}</loc><changefreq>weekly</changefreq><priority>0.9</priority></url>
<url><loc>{{ route('events') }}</loc><changefreq>daily</changefreq><priority>0.8</priority></url>
@foreach($categories as $category)<url><loc>{{ route('category',$category) }}</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>@endforeach
@foreach($events as $event)<url><loc>{{ route('event',$event) }}</loc><lastmod>{{ $event->updated_at->toAtomString() }}</lastmod><changefreq>monthly</changefreq><priority>0.7</priority></url>@endforeach
</urlset>
