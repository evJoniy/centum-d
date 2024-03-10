<!DOCTYPE html>
<html>
<head>
    <title>Link Shortener</title>
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
</head>
@if ($shortenedUrl)
    <p>Original URL: <a href="{{ $originalUrl }}">{{ $originalUrl }}</a></p>
    <p>Shortened URL: <a href="{{ $shortenedUrl }}">{{ $shortenedUrl }}</a></p>
    <p>Hit limit: {{ $hitLimit == 0 ? 'Unlimited' : $hitLimit }}</p>
    <p>Expires at: {{ $expiresAt }}</p>
@endif
</html>
