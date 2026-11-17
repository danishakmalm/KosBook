<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'KosBook - Sistem Booking Kamar Kos'); ?></title>
    
    <!-- Meta SEO Dasar -->
    <meta name="description" content="Sistem Booking Kamar Kos Berbasis Web - KosBook. Kemudahan mencari dan memesan kamar kos mahasiswa secara cepat, mudah, dan terpercaya.">
    <meta name="author" content="KosBook Team">

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3.3 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL; ?>/css/style.css">
    <!-- Chatbot AI CSS -->
    <link rel="stylesheet" href="<?= BASE_URL; ?>/css/chatbot.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
