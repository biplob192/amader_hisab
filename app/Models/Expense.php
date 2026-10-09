<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'expense_category_id', 'amount', 'returned_amount', 'paid_by', 'spent_on', 'notes'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'returned_amount' => 'decimal:2', 'spent_on' => 'date'];
    }

    protected function netAmount(): Attribute
    {
        return Attribute::get(fn () => (float) $this->amount - (float) $this->returned_amount);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }
}
