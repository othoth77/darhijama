@props(['bag' => $errors])

@if ($bag->any())
    <div role="alert" {{ $attributes->class(['rounded-2xl border border-red-400/40 bg-red-400/10 p-4 font-sans text-sm text-red-200']) }}>
        <p class="font-semibold">{{ $title ?? 'Merci de vérifier les champs suivants :' }}</p>
        <ul class="mt-2 list-inside list-disc">
            @foreach ($bag->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
