<?php

namespace Tests\Unit\Repositories;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Repositories\LinkRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Link;

class LinkRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected LinkRepository $linkRepository;
    protected string $originalUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->linkRepository = new LinkRepository();
        $this->originalUrl = $this->faker->url();
    }

    public function testFindLinkByOriginalUrlIfExists()
    {
        $link = Link::create([
            'original_url' => $this->originalUrl,
            'shortened_url' => 'abc123',
            'hits' => 0,
        ]);

        $result = $this->linkRepository->findByOriginalUrl($this->originalUrl);
        $this->assertNotNull($result);
        $this->assertEquals($this->originalUrl, $result->getOriginalURL());
    }

    public function testFailToFindLinkByOriginalUrlIfNotExists()
    {
        $result = $this->linkRepository->findByOriginalUrl($this->originalUrl);
        $this->assertNull($result);
    }

    public function testFindLinkByShortenedUrlIfExists()
    {
        $shortenedUrl = 'abc123';
        Link::create([
            'original_url' => $this->originalUrl,
            'shortened_url' => $shortenedUrl,
            'hits' => 0,
        ]);

        $result = $this->linkRepository->findByShortenedUrl($shortenedUrl);
        $this->assertNotNull($result);
        $this->assertEquals($shortenedUrl, $result->getShortenedURL());
    }

    public function testFindLinkByShortenedUrlIfNotExists()
    {
        $result = $this->linkRepository->findByShortenedUrl($this->faker->text);
        $this->assertNull($result);
    }

    public function testIncrementHits()
    {
        $link = Link::create([
            'original_url' => $this->originalUrl,
            'shortened_url' => 'abc123',
            'hits' => 0,
        ]);

        $result = $this->linkRepository->incrementHits($link);
        $this->assertEquals(1, $result->getHits());
    }
}
