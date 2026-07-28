<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="دار الحجامة — رعاية مهنية ومواعيد منظمة للحجامة في تونس.">
    <title>دار الحجامة | رعاية مهنية في تونس</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <main>
        <section class="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-6 py-16 lg:px-8">
            <p class="mb-4 text-sm font-semibold tracking-widest text-emerald-700">DAR HIJAMA</p>
            <h1 class="max-w-3xl text-4xl font-bold leading-tight sm:text-6xl">
                رعاية مهنية، متابعة واضحة، ومواعيد منظمة
            </h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-stone-600">
                دار الحجامة منصة مستقلة لإدارة خدمات الحجامة والمرضى والمواعيد
                مع احترام الخصوصية وجودة المتابعة.
            </p>

            <div class="mt-10 flex flex-wrap gap-4">
                <a
                    href="{{ route('filament.admin.auth.login') }}"
                    class="rounded-lg bg-emerald-700 px-6 py-3 font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
                >
                    دخول فريق العمل
                </a>
                <a
                    href="tel:+21621821921"
                    class="rounded-lg border border-stone-300 bg-white px-6 py-3 font-semibold text-stone-800 transition hover:border-emerald-700 hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
                >
                    اتصل بنا
                </a>
            </div>

            <div class="mt-16 grid gap-6 sm:grid-cols-3">
                <article class="rounded-xl border border-stone-200 bg-white p-6">
                    <h2 class="font-bold">إدارة المواعيد</h2>
                    <p class="mt-2 text-sm leading-6 text-stone-600">تنظيم المواعيد والمتابعة من الحجز حتى اكتمال الجلسة.</p>
                </article>
                <article class="rounded-xl border border-stone-200 bg-white p-6">
                    <h2 class="font-bold">ملفات المرضى</h2>
                    <p class="mt-2 text-sm leading-6 text-stone-600">حفظ المعلومات الضرورية ضمن صلاحيات واضحة وآمنة.</p>
                </article>
                <article class="rounded-xl border border-stone-200 bg-white p-6">
                    <h2 class="font-bold">متابعة مهنية</h2>
                    <p class="mt-2 text-sm leading-6 text-stone-600">توثيق الجلسات والتنبيهات والمتابعة التشغيلية للفريق.</p>
                </article>
            </div>
        </section>
    </main>
</body>
</html>
