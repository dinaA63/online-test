<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            if (!Schema::hasColumn('answers', 'review_score')) {
                $table->decimal('review_score', 8, 2)->nullable()->after('is_correct');
            }

            if (!Schema::hasColumn('answers', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('review_score')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('answers', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            if (Schema::hasColumn('answers', 'reviewed_by')) {
                $table->dropConstrainedForeignId('reviewed_by');
            }
            if (Schema::hasColumn('answers', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
            if (Schema::hasColumn('answers', 'review_score')) {
                $table->dropColumn('review_score');
            }
        });
    }
};
