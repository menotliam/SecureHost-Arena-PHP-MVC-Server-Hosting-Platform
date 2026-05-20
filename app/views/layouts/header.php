<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AOS (Animate On Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Swiper.js (Carousel) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #0a0a0a; color: #fff; }
    </style>
</head>
<body class="bg-gray-100">
<header class="bg-blue-600 text-white p-4">
    <div class="container mx-auto flex justify-between">
        <h1 class="text-xl font-bold">Cloud Arena</h1>
        <nav>
            <a href="<?php echo URLROOT; ?>" class="px-2">Home</a>
            <a href="<?php echo URLROOT; ?>/pages/about" class="px-2">About</a>
            <a href="<?php echo URLROOT; ?>/products" class="px-2">Products</a>
            <a href="<?php echo URLROOT; ?>/contact" class="px-2">Contact</a>
        </nav>
    </div>
</header>
<main class="container mx-auto py-8">
