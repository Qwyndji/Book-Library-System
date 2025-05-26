<?php
include './config.php';

$sqlTop = "SELECT * FROM books ORDER BY id ASC LIMIT 2";
$resultTop = $conn->query($sqlTop);

// Query untuk daftar buku di bawah
$sqlAll = "SELECT * FROM books ORDER BY id ASC";
$resultAll = $conn->query($sqlAll);
?>

<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>
        Karya Buku Tereliye - Slider 3 per Slide + Kategori Buku Lainnya
    </title>
    <script src="https://cdn.tailwindcss.com">
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <style>
    body {
        font-family: Arial, sans-serif;
    }
    </style>
</head>

<body>
    <header class="sticky top-0 z-50 w-full bg-white border-b shadow-sm">
        <div class="container grid grid-cols-3 items-center justify-between h-16 px-4 mx-auto sm:px-6">
            <div class="flex items-center">
                <i data-lucide="layout-dashboard" class="h-6 w-6 text-indigo-600 "></i>
                <span class="ml-2 text-xl font-semibold">Book & Go</span>
            </div>

            <div class="flex items-center justify-center space-x-2">
                <a href="#books" class="rounded-md hover:bg-indigo-900 px-3 py-2 text-sm font-medium hover:text-white"
                    aria-current="page">Procuts</a>
            </div>

            <div class="flex items-center justify-end space-x-2">
                <a href="http://localhost/booklibrary/auth/login.php"
                    class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-medium text-white"
                    aria-current="page">Login</a>
                <!-- <a href="http://localhost/booklibrary/auth/login.php"
                    class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white"
                    aria-current="page">Register</a> -->
            </div>
        </div>
    </header>

    <div class="bg-white">
        <div class="pt-6">
            <!-- Image gallery -->
            <div class="mx-auto mt-6 max-w-2xl sm:px-6 lg:grid lg:max-w-7xl lg:grid-cols-3 lg:gap-x-8 lg:px-8">
                <div class="size-full rounded-lg object-cover flex flex-col justify-center">
                    <h1 class="text-7xl font-bold tracking-tight text-gray-900 mb-6">New & Trending</h1>
                    <p class="text-base text-gray-900">
                        Explore the latest releases and bestselling books everyone’s talking about.
                    </p>
                </div>

                <!-- <div class="hidden lg:grid lg:grid-cols-1 lg:gap-y-8">
                    <img src="https://tailwindcss.com/plus-assets/img/ecommerce-images/product-page-02-tertiary-product-shot-01.jpg"
                        alt="Model wearing plain black basic tee." class="h-[256px] w-[384px] rounded-lg object-cover">
                    <img src="https://tailwindcss.com/plus-assets/img/ecommerce-images/product-page-02-tertiary-product-shot-02.jpg"
                        alt="Model wearing plain gray basic tee." class="h-[256px] w-[384px] rounded-lg object-cover">
                </div> -->
                <!-- <img src="https://tailwindcss.com/plus-assets/img/ecommerce-images/product-page-02-featured-product-shot.jpg"
                    alt="Model wearing plain white basic tee." class="h-[544px] w-[384px] object-cover sm:rounded-lg">
                </img> -->
                <?php while ($row = $resultTop->fetch_assoc()) : ?>
                <img src="<?= './uploads/' . htmlspecialchars($row['image']) ?>"
                    alt="<?= htmlspecialchars($row['title']) ?>"
                    class="h-[544px] w-[384px] object-cover sm:rounded-lg" />
                <?php endwhile; ?>

            </div>
        </div>


        <div id="books">
            <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:max-w-7xl lg:px-8">
                <h2 class="sr-only">Products</h2>
                <h2 class="text-4xl font-bold tracking-tight text-gray-900 mb-10">Featured Books</h2>

                <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
                    <?php while ($row = $resultAll->fetch_assoc()) : ?>
                    <div class="group">
                        <img src="<?= './uploads/' . htmlspecialchars($row['image']) ?>"
                            alt="<?= htmlspecialchars($row['title']) ?>"
                            class="h-[450px] w-full rounded-lg bg-gray-200 object-cover group-hover:opacity-75 xl:aspect-7/8">
                        <h3 class="mt-4 text-sm text-gray-700"><?= htmlspecialchars($row['title']) ?></h3>
                        <p class="mt-1 text-lg font-medium text-gray-900"><?= htmlspecialchars($row['author']) ?></p>
                        <button type="submit"
                            class="mt-2 w-full flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-hidden">
                            <a href='http://localhost/booklibrary/dashboard/rent.php' class='w-full'>
                                <span>Rent</span>
                            </a>
                        </button>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <script>
        lucide.createIcons();
        </script>
</body>

</html>