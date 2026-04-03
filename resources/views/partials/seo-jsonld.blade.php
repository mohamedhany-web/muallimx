{{--
  ╔══════════════════════════════════════════════════════════════════╗
  ║  MuallimX — JSON-LD Structured Data                             ║
  ║  Usage: @include('partials.seo-jsonld', ['type' => 'website'])  ║
  ║  Types: website | course | instructor | faq | breadcrumb        ║
  ╚══════════════════════════════════════════════════════════════════╝
--}}
@php
    $jsonldType = $jsonldType ?? 'website';
    $siteName   = 'MuallimX';
    $siteUrl    = url('/');
    $logoUrl    = asset('images/og-image.jpg');
@endphp

{{-- ══════════════ BASE: WebSite + Organization (تُضاف في كل صفحة) ══════════════ --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "{{ $siteUrl }}/#website",
      "url": "{{ $siteUrl }}",
      "name": "{{ $siteName }}",
      "description": "منصة عربية متخصصة في تأهيل وتطوير المعلمين للعمل أونلاين باحتراف",
      "inLanguage": ["ar", "en"],
      "potentialAction": {
        "@type": "SearchAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": "{{ url('/courses') }}?search={search_term_string}"
        },
        "query-input": "required name=search_term_string"
      }
    },
    {
      "@type": "EducationalOrganization",
      "@id": "{{ $siteUrl }}/#organization",
      "name": "{{ $siteName }}",
      "url": "{{ $siteUrl }}",
      "logo": {
        "@type": "ImageObject",
        "url": "{{ $logoUrl }}",
        "width": 1200,
        "height": 630
      },
      "sameAs": [
        "https://twitter.com/MuallimX",
        "https://www.facebook.com/MuallimX",
        "https://www.linkedin.com/company/muallimx",
        "https://www.youtube.com/@MuallimX"
      ],
      "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "customer support",
        "availableLanguage": ["Arabic", "English"]
      }
    }
  ]
}
</script>

{{-- ══════════════ COURSE PAGE ══════════════ --}}
@if($jsonldType === 'course' && isset($course))
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Course",
  "@id": "{{ url('/course/' . $course->id) }}#course",
  "name": "{{ addslashes($course->title ?? '') }}",
  "description": "{{ addslashes(Str::limit(strip_tags($course->description ?? ''), 300)) }}",
  "url": "{{ url('/course/' . $course->id) }}",
  "provider": {
    "@type": "EducationalOrganization",
    "@id": "{{ $siteUrl }}/#organization",
    "name": "{{ $siteName }}"
  },
  @if($course->thumbnail ?? null)
  "image": "{{ asset('storage/' . str_replace('\\','/', $course->thumbnail)) }}",
  @else
  "image": "{{ $logoUrl }}",
  @endif
  "inLanguage": "ar",
  "courseLanguage": "Arabic",
  @if($course->level ?? null)
  "educationalLevel": "{{ $course->level }}",
  @endif
  @if(($course->price ?? null) !== null)
  "offers": {
    "@type": "Offer",
    "price": "{{ $course->price ?? 0 }}",
    "priceCurrency": "USD",
    "availability": "https://schema.org/InStock",
    "url": "{{ url('/course/' . $course->id) }}"
  },
  @endif
  "hasCourseInstance": {
    "@type": "CourseInstance",
    "courseMode": "online",
    "inLanguage": "ar"
  }
  @if(($course->instructor ?? null) && ($course->instructor->name ?? null))
  ,"instructor": {
    "@type": "Person",
    "name": "{{ addslashes($course->instructor->name) }}"
  }
  @endif
}
</script>

{{-- BreadcrumbList for course --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "الرئيسية", "item": "{{ $siteUrl }}" },
    { "@type": "ListItem", "position": 2, "name": "الكورسات", "item": "{{ url('/courses') }}" },
    { "@type": "ListItem", "position": 3, "name": "{{ addslashes($course->title ?? 'كورس') }}", "item": "{{ url('/course/' . ($course->id ?? '')) }}" }
  ]
}
</script>
@endif

{{-- ══════════════ INSTRUCTOR PROFILE PAGE ══════════════ --}}
@if($jsonldType === 'instructor' && isset($profile))
@php
    $instrName  = $profile->user->name ?? 'مدرب';
    $instrBio   = Str::limit(strip_tags($profile->bio ?? $profile->headline ?? ''), 300);
    $instrImage = ($profile->profile_image ?? null) ? asset('storage/' . $profile->profile_image) : $logoUrl;
    $instrUrl   = route('public.instructors.show', $profile->user ?? $profile);
@endphp
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ProfilePage",
  "@id": "{{ $instrUrl }}#profile",
  "url": "{{ $instrUrl }}",
  "name": "{{ addslashes($instrName) }} - مدرب على MuallimX",
  "mainEntity": {
    "@type": "Person",
    "@id": "{{ $instrUrl }}#person",
    "name": "{{ addslashes($instrName) }}",
    "description": "{{ addslashes($instrBio) }}",
    "image": "{{ $instrImage }}",
    "url": "{{ $instrUrl }}",
    "jobTitle": "{{ addslashes($profile->headline ?? 'مدرب') }}",
    "worksFor": {
      "@type": "EducationalOrganization",
      "@id": "{{ $siteUrl }}/#organization",
      "name": "{{ $siteName }}"
    }
    @php $socials = $profile->social_links ?? []; @endphp
    @if(!empty($socials))
    ,"sameAs": [
      @php $sameAs = array_values(array_filter([$socials['linkedin'] ?? null, $socials['twitter'] ?? null, $socials['youtube'] ?? null, $socials['facebook'] ?? null, $socials['website'] ?? null])); @endphp
      {{ collect($sameAs)->map(fn($s) => '"' . addslashes($s) . '"')->implode(',') }}
    ]
    @endif
  }
}
</script>

{{-- BreadcrumbList for instructor --}}
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "الرئيسية", "item": "{{ $siteUrl }}" },
    { "@type": "ListItem", "position": 2, "name": "المدربون", "item": "{{ url('/instructors') }}" },
    { "@type": "ListItem", "position": 3, "name": "{{ addslashes($instrName) }}", "item": "{{ $instrUrl }}" }
  ]
}
</script>
@endif

{{-- ══════════════ FAQ PAGE ══════════════ --}}
@if($jsonldType === 'faq' && isset($faqs) && $faqs->count())
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    @foreach($faqs as $i => $faq)
    {
      "@type": "Question",
      "name": "{{ addslashes(strip_tags($faq->question ?? '')) }}",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "{{ addslashes(Str::limit(strip_tags($faq->answer ?? ''), 400)) }}"
      }
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>
@endif

{{-- ══════════════ ABOUT / HOME PAGE ══════════════ --}}
@if($jsonldType === 'about')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "AboutPage",
  "url": "{{ url('/about') }}",
  "name": "من نحن - MuallimX",
  "description": "تعرف على منصة MuallimX، رسالتنا وقيمنا في تأهيل المعلمين للعمل أونلاين باحتراف",
  "mainEntity": {
    "@type": "EducationalOrganization",
    "@id": "{{ $siteUrl }}/#organization",
    "name": "MuallimX",
    "url": "{{ $siteUrl }}",
    "foundingDate": "2023",
    "description": "منصة عربية متخصصة في تأهيل وتطوير المعلمين للعمل أونلاين",
    "areaServed": {
      "@type": "Place",
      "name": "العالم العربي"
    },
    "knowsAbout": ["تعليم إلكتروني", "تأهيل المعلمين", "أدوات AI للتعليم", "منصات التدريس الأونلاين"]
  }
}
</script>
@endif
