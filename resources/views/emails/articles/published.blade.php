<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Artikel Dipublish</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1c1917; line-height: 1.6;">
    <p>Halo {{ $article->author?->name ?? 'Tim Redaksi' }},</p>
    <p>Artikel Anda <strong>{{ $article->title }}</strong> sudah dipublish.</p>
    <p>Waktu publish: {{ optional($article->published_at)->format('Y-m-d H:i') ?? '-' }}</p>
    @if ($article->publicUrl())
        <p>Tautan publik: <a href="{{ $article->publicUrl() }}">{{ $article->publicUrl() }}</a></p>
    @endif
    @if ($actor)
        <p>Aksi publish dilakukan oleh {{ $actor->name }}.</p>
    @endif
</body>
</html>
