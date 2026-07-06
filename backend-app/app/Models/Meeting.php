<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    protected $fillable = ['group_id', 'title', 'meeting_date', 'meeting_time', 'location_or_link', 'notes'];

    protected function casts(): array
    {
        return ['meeting_date' => 'date'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
