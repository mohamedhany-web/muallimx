<?php

namespace Tests\Unit;

use App\Services\YouTubeVideoService;
use PHPUnit\Framework\TestCase;

class YouTubeVideoServiceTest extends TestCase
{
    public function test_extracts_id_from_watch_url(): void
    {
        $this->assertSame(
            'dQw4w9WgXcQ',
            YouTubeVideoService::extractId('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        );
    }

    public function test_extracts_id_from_short_url(): void
    {
        $this->assertSame(
            'dQw4w9WgXcQ',
            YouTubeVideoService::extractId('https://youtu.be/dQw4w9WgXcQ')
        );
    }

    public function test_extracts_id_from_shorts(): void
    {
        $this->assertSame(
            'dQw4w9WgXcQ',
            YouTubeVideoService::extractId('https://www.youtube.com/shorts/dQw4w9WgXcQ')
        );
    }

    public function test_normalize_builds_embed_assets(): void
    {
        $data = YouTubeVideoService::normalizeFromInput('https://youtu.be/dQw4w9WgXcQ');
        $this->assertSame('dQw4w9WgXcQ', $data['youtube_id']);
        $this->assertStringContainsString('dQw4w9WgXcQ', $data['youtube_url']);
        $this->assertStringContainsString('dQw4w9WgXcQ', $data['thumbnail_url']);
    }

    public function test_rejects_invalid_url(): void
    {
        $this->assertNull(YouTubeVideoService::extractId('https://example.com/video'));
        $this->assertFalse(YouTubeVideoService::isValidUrl('not-a-youtube-link'));
    }

    public function test_format_duration(): void
    {
        $this->assertSame('1:05', YouTubeVideoService::formatDuration(65));
        $this->assertSame('1:02:03', YouTubeVideoService::formatDuration(3723));
        $this->assertNull(YouTubeVideoService::formatDuration(null));
    }
}
