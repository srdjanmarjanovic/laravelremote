<?php

use App\Enums\ListingType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->string('listing_type')->default(ListingType::Regular->value)->after('status');
        });

        // Migrate existing data
        DB::table('positions')
            ->where('is_featured', false)
            ->update(['listing_type' => ListingType::Regular->value]);

        DB::table('positions')
            ->where('is_featured', true)
            ->update(['listing_type' => ListingType::Featured->value]);

        Schema::table('positions', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropColumn('is_featured');
            $table->index('listing_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('status');
        });

        // Migrate data back
        DB::table('positions')
            ->whereIn('listing_type', [ListingType::Featured->value, ListingType::Top->value])
            ->update(['is_featured' => true]);

        DB::table('positions')
            ->where('listing_type', ListingType::Regular->value)
            ->update(['is_featured' => false]);

        Schema::table('positions', function (Blueprint $table) {
            $table->dropIndex(['listing_type']);
            $table->dropColumn('listing_type');
            $table->index('is_featured');
        });
    }
};
