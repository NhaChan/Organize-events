{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
{!! '<'.'?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach($urls as $url)
    <url>
        <loc>{{ $url['location'] }}</loc>
        @if($url['last_modified'])<lastmod>{{ $url['last_modified'] }}</lastmod>@endif
        @foreach($url['images'] as $image)
            <image:image><image:loc>{{ $image }}</image:loc></image:image>
        @endforeach
    </url>
@endforeach
</urlset>
