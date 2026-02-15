{{-- مشغل الفيديو - عرض رابط الفيديو فقط (iframe / video بالتحكم الأصلي) --}}
<div class="relative w-full h-full bg-black" id="video-container"
     x-data="videoPlayer()"
     x-init="init()">
    <div class="video-player-area absolute inset-0 w-full h-full" id="video-player">
        <div x-show="!currentLessonVideoUrl" class="absolute inset-0 flex items-center justify-center text-white p-8">
            <div class="text-center">
                <i class="fas fa-play-circle text-5xl mb-4 opacity-70"></i>
                <p>اختر محاضرة أو درساً لعرض الفيديو</p>
            </div>
        </div>
        <div x-show="currentLessonVideoUrl" class="video-display-wrapper absolute inset-0 w-full h-full" id="video-surface">
            {{-- يُملأ من loadVideo() بسيط: iframe أو video فقط --}}
        </div>
    </div>
</div>
