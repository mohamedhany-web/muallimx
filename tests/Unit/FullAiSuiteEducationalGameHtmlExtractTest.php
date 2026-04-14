<?php

namespace Tests\Unit;

use App\Services\FullAiSuiteContextService;
use Tests\TestCase;

class FullAiSuiteEducationalGameHtmlExtractTest extends TestCase
{
    public function test_extracts_from_markdown_html_fence(): void
    {
        $raw = "```html\n<!DOCTYPE html><html><head><title>T</title></head><body>x</body></html>\n```";
        $html = app(FullAiSuiteContextService::class)->extractStandaloneHtmlFromModelResponse($raw);

        $this->assertNotNull($html);
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('</html>', $html);
    }

    public function test_extracts_raw_doctype_document(): void
    {
        $raw = "Some intro\n<!DOCTYPE html><html><head></head><body></body></html>\ntrailing";
        $html = app(FullAiSuiteContextService::class)->extractStandaloneHtmlFromModelResponse($raw);

        $this->assertNotNull($html);
        $this->assertStringStartsWith('<!DOCTYPE html>', $html);
        $this->assertStringEndsWith('</html>', trim($html));
    }

    public function test_returns_null_without_html_document(): void
    {
        $this->assertNull(app(FullAiSuiteContextService::class)->extractStandaloneHtmlFromModelResponse('Just prose.'));
        $this->assertNull(app(FullAiSuiteContextService::class)->extractStandaloneHtmlFromModelResponse('<div>no html root</div>'));
    }
}
