<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Electronic invoice (NFe) links ("linksnfe"). Kept as its own table
     * rather than a plain column on refuelings because a single NFe link
     * can be shared by multiple refueling line items from the same invoice.
     */
    public function up(): void
    {
        Schema::create('nfe_links', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfe_links');
    }
};
