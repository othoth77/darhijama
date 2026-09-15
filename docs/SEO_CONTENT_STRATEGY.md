# Dar Hijama — SEO & Content Strategy

**Date:** 2026-09-14
**Scope:** SEO-first homepage revision + Articles CMS (`feature/darhijama-seo-articles-20260914`)
**Design system in use:** Piste 1 (`othoth77/othdesign` @ `639070f`) — unchanged by this phase.

This document records the package research, the architecture decisions that
followed from it, the slug strategy, and a starting keyword/content-cluster
framework. It is a starting point for whoever writes the next articles, not
a finished content calendar.

---

## 1. Package research — compatibility matrix

Constraint for the whole matrix: **PHP `^8.3`, Laravel `^12.0`, Filament
`^3.3`** (this repository's actual `composer.json`, unchanged by this
phase). A package fails the matrix if it would force upgrading any of
those three — that upgrade was explicitly out of scope unless proven
necessary, and none of the SEO/sitemap work below required it.

| Package | Latest release checked | Requires | Verdict | Why |
|---|---|---|---|---|
| `spatie/laravel-sitemap` | 8.2.0 (2026-06-24) | `php: ^8.4` | ❌ Rejected | Needs PHP 8.4; this stack is 8.3. The 7.x line that *does* support PHP 8.3 does not support Laravel 12 (`illuminate/support: ^12.0\|^13.0` only appears from 8.x). No version satisfies both constraints. |
| `ralphjsmit/laravel-seo` | 1.8.2 | `php: ^8.2` | ⚠️ Compatible alone, not used | The core package itself would install fine, but its only Filament integration (`ralphjsmit/laravel-filament-seo`, latest 2.2.1) requires `filament/filament: ^4.0\|^5.0` — not 3.3. Using the core package without its Filament layer would mean hand-building the same admin form fields this repo already needed anyway, for no net simplification. |
| `ralphjsmit/laravel-filament-seo` | 2.2.1 | `filament/filament: ^4.0\|^5.0` | ❌ Rejected | Exactly the Filament-version trap the design brief warned about. Confirmed via its own `composer.json`, not assumed. |
| `rankbeam/laravel-seo-filament` | current | targets Filament 4/5 | ❌ Rejected | Same reason — named explicitly in the brief as the "do not upgrade Filament just for this" case. |
| `lara-zeus/laravel-seo` | 2.0.0 (2026-04-16) | `php: ^8.2`, `illuminate/support: ^12.0\|^13.0` | ⚠️ Compatible alone, not used | Same shape as RalphJSmit's package: the core is fine, but `lara-zeus/core` (needed for the Filament plugin wrapper) requires `filament/filament: ^5.6`. |
| `mews/purifier` (+ `ezyang/htmlpurifier`) | 3.4.4 | `php: ^7.2\|^8.0`, Laravel up to `^13.0` | ✅ **Added** | The one new dependency this phase actually installs. Solves a real, named risk (RichEditor output is admin-authored HTML rendered back with `{!! !!}`) that a hand-rolled sanitizer would likely get wrong in some edge case HTMLPurifier already handles. 20.8M installs, actively maintained (last release 2026-04, still receiving updates as of 2026-09), MIT-compatible license, zero conflicts with the existing dependency tree (`composer why-not` clean). |

**Net result: zero new SEO/sitemap packages.** Sitemap, robots, meta tags,
canonical, Open Graph, JSON-LD, and the Filament SEO tab are all native
implementations — see §2. The single dependency added (`mews/purifier`) is
a security control, not an SEO feature package, and was evaluated the same
way (compatibility, maintenance, license) before being added.

`composer audit` after adding it reports pre-existing advisories in
`league/commonmark` and `livewire/livewire` — both were already in
`composer.lock` before this phase (confirmed: `git diff composer.lock`
shows their versions unchanged; only `mews/purifier` and
`ezyang/htmlpurifier` were added). Out of scope for this phase per "no
Filament/Laravel upgrade unless proven necessary" — flagged separately.

## 2. What was built natively, and why

| Concern | Native implementation | Reused from |
|---|---|---|
| Sitemap (`/sitemap.xml`) | `Applications\DarHijama\Http\Controllers\SeoController::sitemap()` + a Blade XML view | The exact pattern already used (and already fixed for its Blade-compiler XML-declaration bug) by `Modules/Landing/Http/Controllers/PublicSiteController` |
| Robots (`/robots.txt`) | `SeoController::robots()` | Same pattern as `Modules/Landing`'s `robots()` |
| SEO fallback resolution | `Applications\DarHijama\Application\Services\Seo\ArticleSeoResolver` | New, ~200 lines, single responsibility |
| SEO diagnostics (Admin) | Same resolver's `diagnostics()` method, rendered in a Filament `Placeholder` | New |
| Rich content editor | Filament's built-in `RichEditor` | 100% reused, zero new editor |
| Featured images | Filament's built-in `FileUpload` on the `public` disk | 100% reused; `Mythos\Core\Media` (the app's fuller media-library service) was reviewed but not adopted for this — a plain disk path was sufficient for a `featured_image` column and kept the schema simple |
| Structured data | Plain PHP arrays serialized with `json_encode` | New — no schema-builder package needed for two JSON-LD shapes (`BlogPosting`/`Article` + `BreadcrumbList` on articles, `MedicalBusiness` on the homepage) |

**Domain-scoped sitemap/robots, not the shared ones:** `Modules/Landing`'s
`/sitemap.xml` and `/robots.txt` are shared across every application built
on this codebase (this app and Notre Jour are separate deployments of the
same source). Adding Dar-Hijama-specific article URLs there would have
leaked into Notre Jour's sitemap. Instead, `Applications/DarHijama/routes/web.php`
registers its own `/sitemap.xml` and `/robots.txt` domain-scoped to
`config('applications.dar-hijama.public_hosts.primary')`. Route registration
order (`Mythos\Core\Applications\Registry\MythosApplicationRegistry::boot()`,
called from the 2nd provider in `bootstrap/providers.php`, well before
`LaravelModulesServiceProvider` which is last) means this app's routes are
added to the router before the shared module's, so darhijama.tn requests
match the domain-scoped route first while every other domain still falls
through to the shared one, untouched.

## 3. Slug strategy

Tested directly against this app's Laravel version before deciding:

```
Str::slug('الحجامة المنزلية في تونس الكبرى')
  → "alhgam-almnzly-fy-tons-alkbr"
```

Laravel's `Str::slug()` transliterates Arabic natively — it does **not**
produce an empty string, and it does **not** require forcing a French/Latin
title. **Decision: use the transliterated slug (the default `Str::slug()`
behaviour), not a raw-Arabic-UTF8 slug.** Reasoning:

- A raw-Arabic slug in the URL still *works* technically (browsers percent-encode
  it), but it renders as `%D8%A7%D9%84...` in many places that copy/paste
  or display a raw URL (chat apps, some browser address bars, server logs),
  which is worse for trust and for sharing than a readable transliteration.
- The transliteration is deterministic and stable across edits to
  unrelated fields, which matters for canonical URLs and the 301-redirect
  system (§4).
- It is what `ArticleResource`'s create form generates automatically from
  the title (editable afterwards, `Applications/DarHijama/Filament/Resources/ArticleResource.php`).

## 4. Redirect system

`Article::updating()` (in `Applications/DarHijama/Domain/Article.php`) writes
a row to `dar_hijama_article_redirects` automatically whenever `slug`
changes on an existing, already-persisted article. `ArticleController::show()`
looks up that table before returning 404, and issues a real HTTP 301 to the
current slug. Nothing about this requires the editor to remember to do
anything — a slug edit in the SEO tab can never produce a broken link.

## 5. Local SEO — area served

"تونس الكبرى" (Grand Tunis) is used as a real, checkable geographic
definition — its four governorates (تونس / أريانة / بن عروس / منوبة) — in
the homepage's `MedicalBusiness` JSON-LD `areaServed`. This is a
geographic fact, not a coverage claim about the business, and no other
LocalBusiness fields (`address`, `openingHours`, ratings) were invented —
per the brief, those stay absent until real values exist.

City-specific landing pages (`/hijama-tunis`, `/hijama-ariana`, …) were
explicitly **not** created — the brief calls these out as doorway pages
without independent content, and none exist yet to justify them.

## 6. Keyword research framework (starting point, not final)

No Search Console or Keyword Planner access was available in this
environment — the clusters below are built from the seed terms already
given in the design brief, organised by search intent, for a human (or a
future session with GSC/GKP access) to validate and prioritise before
writing to any of them.

| Intent | Example queries | Maps to |
|---|---|---|
| Commercial / local | "حجامة تونس", "مركز حجامة تونس", "موعد حجامة تونس", "الحجامة المنزلية تونس الكبرى" | Homepage (already optimised — see §7), not an article target |
| Informational | "ما هي الحجامة", "الحجامة الجافة والرطبة", "قبل الحجامة", "بعد الحجامة", "أسئلة الحجامة" | Pillar + supporting articles (§8) |
| Transactional / how-to | "كيف أحجز حجامة منزلية", "حجامة في المنزل" | Supporting article + strong internal link to the WhatsApp CTA |

**Rule applied throughout:** no keyword is targeted by more than one
article (per the brief's explicit warning against repeating the same
keyword across dozens of posts) — the pillar owns the head term, each
supporting piece owns one distinct sub-intent.

## 7. Homepage — what it now targets

- **H1:** "الحجامة المنزلية في تونس الكبرى" (was: "رعاية مهنية، متابعة واضحة، ومواعيد منظمة" — removed, it targeted no real query).
- **Title:** `الحجامة المنزلية في تونس الكبرى | دار الحجامة`.
- Copy shortened throughout (hero subtext to one line, service card
  descriptions to a single sentence each, process steps to 2–4 words) per
  the "reduce excess text" objective — see the `git diff` on
  `Applications/DarHijama/resources/views/public/home.blade.php`.

## 8. Article topic cluster (proposed, not commissioned)

```
Pillar (homepage — already live, not an article):
  الحجامة المنزلية في تونس الكبرى

Supporting articles (draft only — see §9, none published by this phase):
  ما هي الحجامة؟
  كيف يتم الاستعداد لجلسة الحجامة؟
  ماذا يحدث بعد جلسة الحجامة؟
  أسئلة شائعة حول الحجامة المنزلية
  كيف تحجز جلسة حجامة منزلية في تونس الكبرى؟
```

Each of these, when actually written, must satisfy the YMYL rules in §9
before being set to `published` — this phase does not clear that bar for
any of them by itself.

## 9. Medical / YMYL content rule (unchanged from the brief, restated for whoever writes next)

Dar Hijama is health-adjacent. Every article that makes any factual claim
about the practice of hijama must, before its status is set to
`published`:

- Cite a real source where the claim is not general/uncontested knowledge.
- Name a real author (the `author_id` field already requires an existing
  `User`, never a fabricated name).
- Carry `datePublished`/`dateModified` — both already automatic (Article's
  `published_at`/`updated_at`, surfaced in the `BlogPosting` JSON-LD).
- Contain no treatment guarantees, no invented statistics, no fabricated
  testimonials — the Filament SEO diagnostics tab does not (and cannot)
  check this; it is an editorial judgment call before publishing.

**No article was published by this phase.** One draft example
("كيف تحجز جلسة حجامة منزلية في تونس الكبرى؟", pure logistics, no medical
claim) was created and immediately used only to verify the CMS end-to-end
in an isolated staging database — see the deployment report — and was not
carried into production.
