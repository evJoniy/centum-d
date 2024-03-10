<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Link;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LinkControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function testDisplaysTheForm()
    {
        $response = $this->get(route('link.form'));

        $response->assertStatus(200);
        $response->assertViewIs('link_form');
        $response->assertViewHas('shortenedUrl', '');
    }

    public function testReturnsViewWithShortenLinkData()
    {
        $originalUrl = $this->faker->url();
        $response = $this->post(route('link.shorten'), [
            'original_url' => $originalUrl,
            'max_hits' => 10,
            'lifetime' => 24,
        ]);

        $response->assertStatus(200);
        $link = Link::first();

        $response->assertViewIs('link_result');
        $response->assertViewHas('originalUrl', $originalUrl);
        $response->assertViewHas('shortenedUrl', url("/{$link->shortened_url}"));
        $this->assertNotNull($link);
        $this->assertEquals($originalUrl, $link->original_url);
    }

    public function testInvalidRequest()
    {
        $response = $this->post(route('link.shorten'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['original_url']);
    }

    public function testUpdatesHitCounterAndRedirectsToUrl()
    {
        $link = Link::create([
            'original_url' => $this->faker->url(),
            'shortened_url' => 'abcd1234',
            'max_hits' => 10,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->get("/{$link->shortened_url}");

        $response->assertRedirect($link->original_url);
        $this->assertEquals(1, $link->fresh()->hits);
    }

    public function test404IfShortDoesNotExist()
    {
        $response = $this->get('/invalidshorturl');

        $response->assertStatus(404);
    }
}
