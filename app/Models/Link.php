<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_url',
        'shortened_url',
        'max_hits',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime:Y-m-d',
    ];

    public function getOriginalURL()
    {
        return $this->original_url;
    }

    public function getShortenedURL()
    {
        return $this->shortened_url;
    }

    public function getHits()
    {
        return $this->hits;
    }

    public function getMaxHits()
    {
        return $this->max_hits;
    }

    public function getExpiresAt()
    {
        return $this->expires_at;
    }
}
