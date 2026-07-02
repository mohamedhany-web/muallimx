<?php

namespace App\Console\Commands;

use App\Services\MuallimxAiClient;
use Illuminate\Console\Command;

class TestMuallimxAiCommand extends Command
{
    protected $signature = 'muallimx-ai:test
                            {prompt? : النص المرسل للنموذج}
                            {--model= : تجاوز معرّف النموذج مؤقتاً}';

    protected $description = 'اختبار اتصال Muallimx AI (Gemini) ببرومبت بسيط';

    public function handle(MuallimxAiClient $client): int
    {
        if ($model = $this->option('model')) {
            config(['muallimx_ai.model' => $model]);
        }

        $this->line('configured: '.($client->isConfigured() ? 'yes' : 'no'));
        $this->line('model: '.config('muallimx_ai.model'));
        $this->line('base_url: '.config('muallimx_ai.base_url'));

        if (! $client->isConfigured()) {
            $this->error('AI غير مفعّل. راجع GEMINI_API_KEY و GEMINI_ENABLED في .env ثم php artisan config:clear');

            return self::FAILURE;
        }

        $prompt = (string) ($this->argument('prompt') ?: 'قل مرحباً في جملة واحدة بالعربية فقط.');

        $this->info('prompt: '.$prompt);

        try {
            $out = $client->generateFromPrompt($prompt);
            $this->newLine();
            $this->info('status: OK');
            $this->line(mb_substr($out, 0, 2000));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('status: ERROR');
            $this->line($e->getMessage());
            $this->warn('user: '.$client->userFacingErrorMessage($e));

            return self::FAILURE;
        }
    }
}
