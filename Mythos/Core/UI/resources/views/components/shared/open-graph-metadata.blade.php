@props([
    'title',
    'description',
    'url' => null,
    'image' => null,
    'type' => 'website',
])

<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
@if ($url)
    <meta property="og:url" content="{{ $url }}">
@endif
@if ($image)
    <meta property="og:image" content="{{ $image }}">
@endif
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
@if ($image)
    <meta name="twitter:image" content="{{ $image }}">
@endif
