<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cash movements ("lançamentos"), the heart of the livro caixa.
     * Legacy lc_movimento kept dia/mes/ano as separate columns alongside
     * datamov, purely redundant (dia/mes/ano are always derivable from
     * datamov); dropped here in favor of a single movement_date. The
     * legacy data_lancamento (insertion audit timestamp, distinct from
     * the movement's own date) is preserved as created_at.
     */
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('page_number');
            $table->enum('type', ['credit', 'debit']);
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('movement_date');
            $table->timestamps();

            $table->index(['account_id', 'movement_date']);
            $table->index(['account_id', 'book_id', 'page_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
