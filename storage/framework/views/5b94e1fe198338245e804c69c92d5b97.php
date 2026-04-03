
<?php
    $jsonldType = $jsonldType ?? 'website';
    $siteName   = 'MuallimX';
    $siteUrl    = url('/');
    $logoUrl    = asset('images/og-image.jpg');
?>


<script type="application/ld+json">
{
  "<?php $__contextArgs = [];
if (context()->has($__contextArgs[0])) :
if (isset($value)) { $__contextPrevious[] = $value; }
$value = context()->get($__contextArgs[0]); ?>": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "<?php echo e($siteUrl); ?>/#website",
      "url": "<?php echo e($siteUrl); ?>",
      "name": "<?php echo e($siteName); ?>",
      "description": "منصة عربية متخصصة في تأهيل وتطوير المعلمين للعمل أونلاين باحتراف",
      "inLanguage": ["ar", "en"],
      "potentialAction": {
        "@type": "SearchAction",
        "target": {
          "@type": "EntryPoint",
          "urlTemplate": "<?php echo e(url('/courses')); ?>?search={search_term_string}"
        },
        "query-input": "required name=search_term_string"
      }
    },
    {
      "@type": "EducationalOrganization",
      "@id": "<?php echo e($siteUrl); ?>/#organization",
      "name": "<?php echo e($siteName); ?>",
      "url": "<?php echo e($siteUrl); ?>",
      "logo": {
        "@type": "ImageObject",
        "url": "<?php echo e($logoUrl); ?>",
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


<?php if($jsonldType === 'course' && isset($course)): ?>
<script type="application/ld+json">
{
  "<?php $__contextArgs = [];
if (context()->has($__contextArgs[0])) :
if (isset($value)) { $__contextPrevious[] = $value; }
$value = context()->get($__contextArgs[0]); ?>": "https://schema.org",
  "@type": "Course",
  "@id": "<?php echo e(url('/course/' . $course->id)); ?>#course",
  "name": "<?php echo e(addslashes($course->title ?? '')); ?>",
  "description": "<?php echo e(addslashes(Str::limit(strip_tags($course->description ?? ''), 300))); ?>",
  "url": "<?php echo e(url('/course/' . $course->id)); ?>",
  "provider": {
    "@type": "EducationalOrganization",
    "@id": "<?php echo e($siteUrl); ?>/#organization",
    "name": "<?php echo e($siteName); ?>"
  },
  <?php if($course->thumbnail ?? null): ?>
  "image": "<?php echo e(asset('storage/' . str_replace('\\','/', $course->thumbnail))); ?>",
  <?php else: ?>
  "image": "<?php echo e($logoUrl); ?>",
  <?php endif; ?>
  "inLanguage": "ar",
  "courseLanguage": "Arabic",
  <?php if($course->level ?? null): ?>
  "educationalLevel": "<?php echo e($course->level); ?>",
  <?php endif; ?>
  <?php if(($course->price ?? null) !== null): ?>
  "offers": {
    "@type": "Offer",
    "price": "<?php echo e($course->price ?? 0); ?>",
    "priceCurrency": "USD",
    "availability": "https://schema.org/InStock",
    "url": "<?php echo e(url('/course/' . $course->id)); ?>"
  },
  <?php endif; ?>
  "hasCourseInstance": {
    "@type": "CourseInstance",
    "courseMode": "online",
    "inLanguage": "ar"
  }
  <?php if(($course->instructor ?? null) && ($course->instructor->name ?? null)): ?>
  ,"instructor": {
    "@type": "Person",
    "name": "<?php echo e(addslashes($course->instructor->name)); ?>"
  }
  <?php endif; ?>
}
</script>


<script type="application/ld+json">
{
  "<?php $__contextArgs = [];
if (context()->has($__contextArgs[0])) :
if (isset($value)) { $__contextPrevious[] = $value; }
$value = context()->get($__contextArgs[0]); ?>": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "الرئيسية", "item": "<?php echo e($siteUrl); ?>" },
    { "@type": "ListItem", "position": 2, "name": "الكورسات", "item": "<?php echo e(url('/courses')); ?>" },
    { "@type": "ListItem", "position": 3, "name": "<?php echo e(addslashes($course->title ?? 'كورس')); ?>", "item": "<?php echo e(url('/course/' . ($course->id ?? ''))); ?>" }
  ]
}
</script>
<?php endif; ?>


<?php if($jsonldType === 'instructor' && isset($profile)): ?>
<?php
    $instrName  = $profile->user->name ?? 'مدرب';
    $instrBio   = Str::limit(strip_tags($profile->bio ?? $profile->headline ?? ''), 300);
    $instrImage = ($profile->profile_image ?? null) ? asset('storage/' . $profile->profile_image) : $logoUrl;
    $instrUrl   = route('public.instructors.show', $profile->user ?? $profile);
?>
<script type="application/ld+json">
{
  "<?php $__contextArgs = [];
if (context()->has($__contextArgs[0])) :
if (isset($value)) { $__contextPrevious[] = $value; }
$value = context()->get($__contextArgs[0]); ?>": "https://schema.org",
  "@type": "ProfilePage",
  "@id": "<?php echo e($instrUrl); ?>#profile",
  "url": "<?php echo e($instrUrl); ?>",
  "name": "<?php echo e(addslashes($instrName)); ?> - مدرب على MuallimX",
  "mainEntity": {
    "@type": "Person",
    "@id": "<?php echo e($instrUrl); ?>#person",
    "name": "<?php echo e(addslashes($instrName)); ?>",
    "description": "<?php echo e(addslashes($instrBio)); ?>",
    "image": "<?php echo e($instrImage); ?>",
    "url": "<?php echo e($instrUrl); ?>",
    "jobTitle": "<?php echo e(addslashes($profile->headline ?? 'مدرب')); ?>",
    "worksFor": {
      "@type": "EducationalOrganization",
      "@id": "<?php echo e($siteUrl); ?>/#organization",
      "name": "<?php echo e($siteName); ?>"
    }
    <?php $socials = $profile->social_links ?? []; ?>
    <?php if(!empty($socials)): ?>
    ,"sameAs": [
      <?php $sameAs = array_values(array_filter([$socials['linkedin'] ?? null, $socials['twitter'] ?? null, $socials['youtube'] ?? null, $socials['facebook'] ?? null, $socials['website'] ?? null])); ?>
      <?php echo e(collect($sameAs)->map(fn($s) => '"' . addslashes($s) . '"')->implode(',')); ?>

    ]
    <?php endif; ?>
  }
}
</script>


<script type="application/ld+json">
{
  "<?php $__contextArgs = [];
if (context()->has($__contextArgs[0])) :
if (isset($value)) { $__contextPrevious[] = $value; }
$value = context()->get($__contextArgs[0]); ?>": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "الرئيسية", "item": "<?php echo e($siteUrl); ?>" },
    { "@type": "ListItem", "position": 2, "name": "المدربون", "item": "<?php echo e(url('/instructors')); ?>" },
    { "@type": "ListItem", "position": 3, "name": "<?php echo e(addslashes($instrName)); ?>", "item": "<?php echo e($instrUrl); ?>" }
  ]
}
</script>
<?php endif; ?>


<?php if($jsonldType === 'faq' && isset($faqs) && $faqs->count()): ?>
<script type="application/ld+json">
{
  "<?php $__contextArgs = [];
if (context()->has($__contextArgs[0])) :
if (isset($value)) { $__contextPrevious[] = $value; }
$value = context()->get($__contextArgs[0]); ?>": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    {
      "@type": "Question",
      "name": "<?php echo e(addslashes(strip_tags($faq->question ?? ''))); ?>",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "<?php echo e(addslashes(Str::limit(strip_tags($faq->answer ?? ''), 400))); ?>"
      }
    }<?php echo e(!$loop->last ? ',' : ''); ?>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  ]
}
</script>
<?php endif; ?>


<?php if($jsonldType === 'about'): ?>
<script type="application/ld+json">
{
  "<?php $__contextArgs = [];
if (context()->has($__contextArgs[0])) :
if (isset($value)) { $__contextPrevious[] = $value; }
$value = context()->get($__contextArgs[0]); ?>": "https://schema.org",
  "@type": "AboutPage",
  "url": "<?php echo e(url('/about')); ?>",
  "name": "من نحن - MuallimX",
  "description": "تعرف على منصة MuallimX، رسالتنا وقيمنا في تأهيل المعلمين للعمل أونلاين باحتراف",
  "mainEntity": {
    "@type": "EducationalOrganization",
    "@id": "<?php echo e($siteUrl); ?>/#organization",
    "name": "MuallimX",
    "url": "<?php echo e($siteUrl); ?>",
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
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\partials\seo-jsonld.blade.php ENDPATH**/ ?>