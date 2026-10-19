<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeInboxImport extends Model
{
    protected $fillable = [
        'message_id',
        'uid',
        'attachment_filename',
        'attachment_hash',
        'imported_user_id',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'imported_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'imported_user_id');
    }
}
