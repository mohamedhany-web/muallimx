<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Smoke checks for advanced participant PiP (JS + Blade integration).
 */
class ClassroomParticipantPipTest extends TestCase
{
    public function test_livekit_room_js_exposes_participant_pip_api(): void
    {
        $path = public_path('js/classroom-livekit-room.js');
        $this->assertFileExists($path);

        $js = file_get_contents($path);

        foreach ([
            'toggleParticipantPip',
            'openParticipantPip',
            'closeParticipantPip',
            'isParticipantPipOpen',
            'scheduleParticipantPipRefresh',
            'openParticipantDocumentPip',
            'openCanvasPictureInPicture',
            'participantFloatMarkup',
            'mx-participant-float',
            'mx-pip-invite',
        ] as $needle) {
            $this->assertStringContainsString($needle, $js, "Missing PiP symbol: {$needle}");
        }

        $this->assertStringNotContainsString('togglePipPanel', $js);
        $this->assertStringNotContainsString('openParticipantPopout', $js);
    }

    public function test_livekit_blades_reference_participant_pip_controls(): void
    {
        $host = file_get_contents(resource_path('views/student/classroom/room-livekit.blade.php'));
        $guest = file_get_contents(resource_path('views/classroom/join-livekit.blade.php'));

        foreach ([$host, $guest] as $blade) {
            $this->assertStringContainsString('id="mx-ml-btn-pip"', $blade);
            $this->assertStringContainsString('id="mx-sf-people"', $blade);
            $this->assertStringContainsString('aria-pressed="false"', $blade);
            $this->assertStringContainsString('نافذة المشاركين العائمة', $blade);
        }
    }

    public function test_participant_pip_css_is_present(): void
    {
        $css = file_get_contents(public_path('css/classroom-livekit.css'));

        foreach ([
            '#mx-participant-float',
            '.mx-pfloat-grid',
            '.mx-pfloat-toolbar',
            '.mx-pip-invite',
        ] as $selector) {
            $this->assertStringContainsString($selector, $css, "Missing CSS: {$selector}");
        }
    }
}
