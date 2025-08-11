<?php namespace Serendipity\Villas\Models;

// Guard against duplicate inclusion on case-insensitive filesystems
if (class_exists(__NAMESPACE__.'\\Inquiry', false)) {
    return;
}

use Model;

class Inquiry extends Model
{
    protected $table = 'ser_inquiries';

    protected $fillable = [
        'villa_id',
        'villa_title',
        'name',
        'email',
        'message',
        'source_url',
    ];

    public $timestamps = true;
}

