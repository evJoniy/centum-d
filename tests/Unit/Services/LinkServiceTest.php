<?php

namespace Tests\Unit\Services;

use App\Models\Link;
use App\Repositories\LinkRepository;
use App\Services\LinkService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class LinkServiceTest extends TestCase
{
    use WithFaker;

    protected $linkRepository;
    protected LinkService $linkService;
    protected string $originalUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->linkRepository = $this->createMock(LinkRepository::class);
        $this->linkService = new LinkService($this->linkRepository);
        $this->originalUrl = $this->faker->url();
    }

    public function testCreatesNewLink()
    {
        $maxHits = 10;
        $lifetime = 24;

        $this->linkRepository->expects($this->once())
            ->method('findByOriginalUrl')
            ->with($this->originalUrl)
            ->willReturn(null);

        $result = $this->linkService->findOrCreate($this->originalUrl, $maxHits, $lifetime);

        $this->assertInstanceOf(Link::class, $result);
        $this->assertEquals($this->originalUrl, $result->getOriginalURL());

        $this->assertIsString($result->getShortenedURL());
        $this->assertNotEmpty($result->getShortenedURL());
    }

    public function testRetrievesLinkByShortAndCaches()
    {
        Cache::flush();

        $shortenedUrl = 'abcd1234';
        $link = new Link([
            'shortened_url' => $shortenedUrl,
            'original_url' => $this->originalUrl
        ]);

        $this->linkRepository->expects($this->once())
            ->method('findByShortenedUrl')
            ->with($shortenedUrl)
            ->willReturn($link);

        $result = $this->linkService->findByShortenedUrl($shortenedUrl);
        $this->assertInstanceOf(Link::class, $result);

        $cachedResult = $this->linkService->findByShortenedUrl($shortenedUrl);
        $this->assertInstanceOf(Link::class, $cachedResult);

        $this->assertEquals($result->getShortenedURL(), $cachedResult->getShortenedURL());
    }

    public function testIncrementsHitsCounterAndUpdatesCache()
    {
        $shortenedUrl = 'abcd1234';
        $link = new Link([
            'shortened_url' => $shortenedUrl,
            'hits' => 0,
            'expires_at' => now()->addDay(),
        ]);

        $this->linkRepository->expects($this->once())
            ->method('incrementHits')
            ->with($link)
            ->willReturnCallback(function ($link) {
                $link->hits++;
                return $link;
            });

        Cache::shouldReceive('has')
            ->with("link:{$shortenedUrl}")
            ->andReturnTrue();

        Cache::shouldReceive('get')
            ->with("link:{$shortenedUrl}")
            ->andReturn($link);

        Cache::shouldReceive('put')
            ->once()
            ->with("link:{$shortenedUrl}", $link, Mockery::type('DateTime'))
            ->andReturnTrue();

        $this->linkService->incrementHits($link);

        $this->assertEquals(1, $link->getHits());
    }

    public function testRemovesInvalidLinkFromCache()
    {
        $shortenedUrl = 'abcd1234';
        $link = new Link([
            'shortened_url' => $shortenedUrl,
            'hits' => 10,
            'max_hits' => 10,
            'expires_at' => now()->subDay(),
        ]);

        $this->linkRepository->expects($this->once())
            ->method('incrementHits')
            ->willReturn($link);

        Cache::shouldReceive('has')
            ->with("links:{$shortenedUrl}")
            ->andReturnTrue();

        Cache::shouldReceive('forget')
            ->with("links:{$shortenedUrl}")
            ->once()
            ->andReturnTrue();

        $this->linkService->incrementHits($link);
    }
}
