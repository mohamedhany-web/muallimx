<?php

namespace Tests\Feature;

use Tests\TestCase;

class ClassroomSubscriptionServicesMenuTest extends TestCase
{
    public function test_livekit_host_room_contains_subscription_services_menu(): void
    {
        $blade = file_get_contents(
            resource_path('views/student/classroom/room-livekit.blade.php')
        );

        $this->assertStringContainsString(
            '@if(!empty($subscriptionFeatureMenuItems))',
            $blade
        );
        $this->assertStringContainsString('id="pkg-features-dd-btn"', $blade);
        $this->assertStringContainsString('id="pkg-features-dd-panel"', $blade);
        $this->assertStringContainsString(
            '@foreach($subscriptionFeatureMenuItems as $item)',
            $blade
        );
        $this->assertStringContainsString('target="_blank"', $blade);
        $this->assertStringContainsString(
            'const setPackageFeaturesOpen',
            $blade
        );
    }
}
