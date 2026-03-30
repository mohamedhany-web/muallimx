<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'portfolio_headline')) {
                $table->string('portfolio_headline')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'portfolio_about')) {
                $table->text('portfolio_about')->nullable()->after('portfolio_headline');
            }
            if (!Schema::hasColumn('users', 'portfolio_skills')) {
                $table->text('portfolio_skills')->nullable()->after('portfolio_about');
            }
            if (!Schema::hasColumn('users', 'portfolio_social_links')) {
                $table->json('portfolio_social_links')->nullable()->after('portfolio_skills');
            }
            if (!Schema::hasColumn('users', 'portfolio_intro_video_url')) {
                $table->string('portfolio_intro_video_url')->nullable()->after('portfolio_social_links');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = ['portfolio_headline', 'portfolio_about', 'portfolio_skills', 'portfolio_social_links', 'portfolio_intro_video_url'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('users', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};

