<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Artikel Masuk Review</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1c1917; line-height: 1.6;">
    <p>Halo {{ $article->author?->name ?? 'Tim Redaksi' }},</p>
    <p>
        Artikel berjudul <strong>{{ $article->title }}</strong> baru saja diajukan ke tahap review oleh
        <strong>{{ $actor->name }}</strong>.
    </p>
    <p>Kategori: {{ $article->category?->name ?? '-' }}</p>
    @if ($article->excerpt)
        <p>Ringkasan: {{ $article->excerpt }}</p>
    @endif
    <p>Silakan buka panel admin untuk melakukan review editorial.</p>
</body>
</html>
