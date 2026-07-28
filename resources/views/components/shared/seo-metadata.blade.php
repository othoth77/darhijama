@props([
    'title',
    'description',
    'canonical' => null,
    'image' => null,
    'type' => 'website',
])

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
@if ($canonical)
    <link rel="canonical" href="{{ $canonical }}">
@endif
<x-shared.open-graph-metadata
    :title="$title"
    :description="$description"
    :url="$canonical"
    :image="$image"
    :type="$type"
/>
