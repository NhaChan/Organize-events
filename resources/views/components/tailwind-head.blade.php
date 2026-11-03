@props(['css'])

@php($faviconVersion = filemtime(public_path('images/favicon-mt.png')))
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/favicon-mt.png') }}?v={{ $faviconVersion }}">
<link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v={{ filemtime(public_path('favicon.ico')) }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}?v={{ filemtime(public_path('images/apple-touch-icon.png')) }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="{{ asset('css/'.$css) }}?v={{ filemtime(public_path('css/'.$css)) }}" rel="stylesheet">
