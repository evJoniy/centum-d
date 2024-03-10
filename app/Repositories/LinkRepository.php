<?php

namespace App\Repositories;

use App\Models\Link;

class LinkRepository
{
    private const ORIGINAL = 'original_url';
    private const SHORT = 'shortened_url';

    /**
     * @param string $originalUrl
     * @return Link|null
     */
    public function findByOriginalUrl(string $originalUrl): ?Link
    {
        return Link::where(self::ORIGINAL, $originalUrl)->first();
    }

    /**
     * @param string $shortenedUrl
     * @return Link|null
     */
    public function findByShortenedUrl(string $shortenedUrl): ?Link
    {
        return Link::where(self::SHORT, $shortenedUrl)->first();
    }

    /**
     * @param Link $link
     * @return Link
     */
    public function incrementHits(Link $link): Link
    {
        $link->increment('hits');
        $link->save();

        return $link;
    }
}
