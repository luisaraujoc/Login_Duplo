<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Livros" — physical cashbook volumes. The legacy schema had an
     * lc_livros catalog that was never actually linked by a foreign key
     * from lc_movimento.idlivro; movements just carried a raw integer.
     * Here the relationship is made real, scoped per account, matching
     * how idlivro was actually used in practice (MAX(idlivro) per idconta).
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('label')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
