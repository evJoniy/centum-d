<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShortenLinkRequest;
use App\Services\LinkService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class LinkController extends Controller
{
    public function __construct(
        protected LinkService $linkService
    ) {
    }

    public function form(): View
    {
        return view('link_form', ['shortenedUrl' => '']);
    }

    public function shorten(ShortenLinkRequest $request): View
    {
        $link = $this->linkService->findOrCreate(
            $request->input('original_url'),
            $request->input('max_hits') ?? 0,
            $request->input('lifetime') ?? 24,
        );

        return view('link_result', [
            'originalUrl' => $link->getOriginalURL(),
            'shortenedUrl' => url("/{$link->getShortenedURL()}"),
            'hitLimit' => $link->getMaxHits(),
            'expiresAt' => $link->getExpiresAt(),
        ]);
    }

    public function redirect($shortenedUrl): RedirectResponse
    {
        $link = $this->linkService->findByShortenedUrl($shortenedUrl);

        if (!$link) {
            abort(404);
        }

        $this->linkService->incrementHits($link);
        return redirect($link->getOriginalURL());
    }
}
