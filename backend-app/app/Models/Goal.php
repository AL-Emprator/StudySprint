<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    protected $fillable = ['group_id', 'title', 'description', 'due_date', 'status'];

    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
