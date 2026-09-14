<!DOCTYPE html>
<html lang="{{ $invitation->locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @php
        $pageTitle = "{$invitation->groom_name} & {$invitation->bride_name} — Notre Jour";
        $pageDescription = trim($invitation->message) !== ''
            ? \Illuminate\Support\Str::limit(strip_tags($invitation->message), 160)
            : "Vous êtes invités au mariage de {$invitation->groom_name} & {$invitation->bride_name}. Retrouvez toutes les informations : date, lieu, programme et RSVP.";
    @endphp

    <x-shared.seo-metadata
        :title="$pageTitle"
        :description="$pageDescription"
        :canonical="$publicUrl"
        :image="$heroUrl"
    />
    @php
        $structuredData = [
            (chr(64).'context') => 'https://schema.org',
            '@type' => 'Event',
            'name' => "Mariage de {$invitation->groom_name} & {$invitation->bride_name}",
            'startDate' => $invitation->wedding_date->toIso8601String(),
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => array_filter([
                '@type' => 'Place',
                'name' => $invitation->venue_name,
                'address' => $invitation->venue_address,
            ]),
            'description' => $pageDescription,
            'image' => $heroUrl ? [$heroUrl] : [],
            'url' => $publicUrl,
        ];
    @endphp
    <x-shared.structured-data :data="$structuredData" />

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:600,700|inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-ivory text-brand-charcoal antialiased selection:bg-brand-rose/20">

    @include('invitations::public.partials.hero')

    <main id="main-content">
        @include('invitations::public.partials.details')
        @include('invitations::public.partials.map')
        @include('invitations::public.partials.program')
        @include('invitations::public.partials.gallery')
        @include('invitations::public.partials.media-extra')
        @include('invitations::public.partials.rsvp')
        @include('invitations::public.partials.share')
    </main>

    @include('invitations::public.partials.footer')

</body>
</html>
