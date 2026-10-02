<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('returned_amount', 12, 2)->default(0);
            $table->string('paid_by', 32);
            $table->date('spent_on');
            $table->text('notes')->nullable()->comment('Expense notes');
            $table->timestamps();
            $table->index(['user_id', 'spent_on']);
            $table->index(['user_id', 'expense_category_id', 'spent_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
