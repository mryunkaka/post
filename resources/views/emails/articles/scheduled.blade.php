<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Artikel Dijadwalkan Publish</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1c1917; line-height: 1.6;">
    <p>Halo {{ $article->author?->name ?? 'Tim Redaksi' }},</p>
    <p>Artikel Anda <strong>{{ $article->title }}</strong> sudah dijadwalkan untuk publish.</p>
    <p>Waktu publish terjadwal: {{ optional($article->published_at)->format('Y-m-d H:i') ?? '-' }}</p>
    <p>Penjadwalan dilakukan oleh {{ $actor->name }}.</p>
</body>
</html>
