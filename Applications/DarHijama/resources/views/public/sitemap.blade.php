{!! $xmlDeclaration !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        <priority>{{ $url['priority'] }}</priority>
        @isset($url['lastmod'])
        <lastmod>{{ $url['lastmod'] }}</lastmod>
        @endisset
    </url>
@endforeach
</urlset>
