@props(['css'])

<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/favicon-balloon-brand.png') }}?v=7">
<link rel="apple-touch-icon" href="{{ asset('images/favicon-balloon-brand.png') }}?v=7">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="{{ asset('css/'.$css) }}?v={{ filemtime(public_path('css/'.$css)) }}" rel="stylesheet">
