<?php

namespace App\Services;

use App\Models\Link;
use App\Repositories\LinkRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LinkService
{
    public function __construct(
        protected LinkRepository $linkRepository
    ) {
    }

    /**
     * Create a new Link by originalUrl if not exists
     *
     * @param string $originalUrl
     * @param int|null $maxHits
     * @param float|null $lifetime
     * @return Link
     */
    public function findOrCreate(string $originalUrl, ?int $maxHits = 0, ?float $lifetime = 24): Link
    {
        $existingLink = $this->linkRepository->findByOriginalUrl($originalUrl);
        if ($existingLink) {
            return $existingLink;
        }

        $shortenedUrl = $this->generateUniqueShortenedUrl();

        $link = Link::firstOrCreate(
            ['original_url' => $originalUrl],
            [
                'shortened_url' => $shortenedUrl,
                'max_hits' => $maxHits,
                'expires_at' => Carbon::now()->addHours($lifetime)->toDateTimeString(),
            ]
        );

        // Caching the link to optimize future accesses
        Cache::put('link:' . $shortenedUrl, $link);

        return $link;
    }

    /**
     * Get Link by $shortenedUrl from Cache, if found - refresh to get hits up-to-date
     *
     * @param string $shortenedUrl
     * @return Link|null
     */
    public function findByShortenedUrl(string $shortenedUrl): ?Link
    {
        $link = Cache::rememberForever("links:{$shortenedUrl}", function () use ($shortenedUrl) {
            $link = $this->linkRepository->findByShortenedUrl($shortenedUrl);
            return $link && $this->isLinkValid($link) ? $link : null;
        });

        return $link?->refresh();
    }

    /**
     * Increment amount of Link hits
     *
     * @param Link $link
     * @return void
     */
    public function incrementHits(Link $link): void
    {
        $link = $this->linkRepository->incrementHits($link);

        if ($this->isLinkValid($link)) {
            $cacheKey = "link:{$link->getShortenedURL()}";

            if (Cache::has($cacheKey)) {
                $cachedLink = Cache::get($cacheKey);
                $cachedLink->hits = $link->getHits();
                Cache::put(
                    $cacheKey,
                    $cachedLink,
                    $cachedLink->expires_at
                );
            }
        } else {
            // Remove the link from the cache if it's expired or has exceeded its max hits.
            Cache::forget("links:{$link->getShortenedURL()}");
        }
    }

    /**
     * Generate unique short url of 8 symbols in 5 tries, fallback to 10 symbols
     *
     * @param int $retryLimit
     * @return string
     */
    private function generateUniqueShortenedUrl(int $retryLimit = 5): string
    {
        for ($i = 0; $i < $retryLimit; $i++) {
            $shortenedUrl = Str::random(8);
            if (!$this->linkRepository->findByShortenedUrl($shortenedUrl)) {
                return $shortenedUrl;
            }
        }

        return Str::random(10); // Fallback strategy
    }

    /**
     * Check if Link is not expired and not exceeded its max hits
     *
     * @param Link $link
     * @return bool
     */
    private function isLinkValid(Link $link): bool
    {
        $expired = $link->getExpiresAt() && $link->getExpiresAt()->isPast();
        $maxHitsExceeded = $link->getMaxHits() && $link->getHits() >= $link->getMaxHits();
        return !$expired && !$maxHitsExceeded;
    }
}
