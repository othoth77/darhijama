{!! $xmlDeclaration !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($urls as $url)
    <url>
        <loc>{{ $url['location'] }}</loc>
        <priority>{{ $url['priority'] }}</priority>
    </url>
@endforeach
</urlset>
