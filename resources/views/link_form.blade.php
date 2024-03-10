<!DOCTYPE html>
<html>
<head>
    <title>Link Shortener</title>
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">
</head>
<body>

@if ($shortenedUrl)
    <p>Shortened URL: <a href="{{ $shortenedUrl }}">{{ $shortenedUrl }}</a></p>
@endif

<form method="POST" action="/shorten">
    @csrf
    <input type="text" name="original_url" placeholder="Enter URL here" required>
    <input type="number" name="max_hits" placeholder="Limit of hits (0 for unlimited)" min="0">
    <input type="number" name="lifetime" placeholder="Lifetime in hours (default 24h)" min="1">
    <button type="submit">Shorten</button>
</form>

</body>
</html>
