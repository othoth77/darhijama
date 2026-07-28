<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mythos Showcase</title>
</head>
<body>
    <main>
        <h1>Mythos Showcase</h1>

        @forelse ($entries as $entry)
            <article>
                <h2>{{ $entry->title }}</h2>
                @if ($entry->message)
                    <p>{{ $entry->message }}</p>
                @endif
            </article>
        @empty
            <p>No showcase entries yet.</p>
        @endforelse
    </main>
</body>
</html>
