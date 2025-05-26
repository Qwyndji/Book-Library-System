<?php
session_start();
include './config.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

$dataById = null;

if ($id) {
    $rentQuery = "
        SELECT 
            books.*, 
            categories.name AS category_name, 
            AVG(rents.rating) AS avg_rating, 
            COUNT(rents.rating) AS total_ratings
        FROM books 
        LEFT JOIN categories ON books.category_id = categories.id
        LEFT JOIN rents ON books.id = rents.book_id
        WHERE books.id = ?
        GROUP BY books.id
    ";
    
    $stmt = $conn->prepare($rentQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $dataById = $result->fetch_assoc();
    } else {
        die("Data tidak ditemukan.");
    }
}

// Query untuk daftar buku di bawah
$sqlAll = "SELECT books.*, categories.name AS category_name, rents.rating as book_rating
FROM books 
LEFT JOIN categories ON books.category_id = categories.id
LEFT JOIN rents ON books.id = rents.book_id
ORDER BY books.id ASC";
$resultAll = $conn->query($sqlAll);

if (!$id || !$dataById) {
    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BookLibrary - Perpustakaan Online</title>
    <meta name="description" content="Situs perpustakaan online dengan koleksi buku terlengkap" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .carousel-container {
        position: relative;
        overflow: hidden;
    }

    .carousel-item {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
    }

    .carousel-item.active {
        opacity: 1;
    }

    .search-dropdown {
        display: none;
        transition: all 0.2s ease;
    }

    .search-input:focus+.search-dropdown,
    .search-dropdown:hover {
        display: block;
    }
    </style>
</head>

<body class="bg-white text-gray-900">
    <!-- Navbar -->
    <header class="sticky top-0 z-50 w-full border-b bg-white/95 backdrop-blur">
        <div class="container mx-auto flex h-16 items-center px-4">
            <div class="mr-4 hidden md:flex">
                <a href="/" class="flex items-center space-x-2">
                    <span class="text-xl font-bold">BookLibrary</span>
                </a>
            </div>
            <nav class="flex flex-1 items-center justify-between space-x-2 md:justify-end">
                <div class="hidden items-center space-x-4 md:flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="http://localhost/booklibrary/dashboard/rent.php"
                        class="relative px-3 py-2 text-sm font-medium transition-colors hover:text-blue-500">
                        <i class="fas fa-th-large mr-2 h-4 w-4 inline-block"></i>
                        Dashboard
                    </a>
                    <?php endif; ?>
                    <!-- <a href="#" class="relative px-3 py-2 text-sm font-medium transition-colors hover: text-blue-500">
                        <i class="fas fa-cog mr-2 h-4 w-4 inline-block"></i>
                        Admin
                    </a> -->
                    <!-- <a href="#" class="relative px-3 py-2 text-sm font-medium transition-colors hover: text-blue-500">
                        <i class="fas fa-search mr-2 h-4 w-4 inline-block"></i>
                        Search
                    </a> -->
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                    <button
                        class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white text-sm font-medium shadow-sm hover:bg-gray-50">
                        <a href="http://localhost/booklibrary/auth/login.php" class="px-4 py-2">
                            Login / Signup
                        </a>
                    </button>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 animate-fade">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <header class="mb-8 flex justify-between items-center">
                <h1 class="text-2xl md:text-3xl font-bold">Detail Book</h1>
                <a href="http://localhost/booklibrary/index.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Home</span>
                </a>
            </header>

            <div class="flex justify-center mt-8">
            </div>

            <!-- Book Detail Section -->
            <section class="bg-white rounded-lg border shadow-sm p-6 mb-8">
                <div class="flex flex-col md:flex-row gap-8">
                    <!-- Book Cover -->
                    <div class="w-full md:w-1/3">
                        <div class="book-cover-detail">
                            <img src="./uploads/<?= htmlspecialchars($dataById['image']) ?>"
                                alt="<?= htmlspecialchars($dataById['title']) ?>" class="h-full w-full object-cover" />
                        </div>
                    </div>

                    <!-- Book Information -->
                    <div class="w-full md:w-2/3 book-info">
                        <h2 class="text-2xl font-bold"><?= htmlspecialchars($dataById['title']) ?></h2>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                            <?php
                                $roundedRating = intval($dataById['avg_rating']);
                                $totalStars = 5;
                            ?>

                            <div class="book-rating">
                                <span class="ml-1 text-sm font-medium">
                                    <?= $roundedRating ?>
                                </span>
                                <?php for ($i = 1; $i <= $totalStars; $i++): ?>
                                <i
                                    class="fas fa-star <?= $i <= $roundedRating ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                <?php endfor; ?>
                                <span class="ml-1 text-sm font-medium">
                                    (<?= $dataById['total_ratings'] ?>)
                                </span>
                            </div>
                            <span
                                class="inline-block bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full"><?= htmlspecialchars($dataById['category_name']) ?></span>
                        </div>

                        <div class="mt-4 space-y-2">
                            <p><span class="font-medium">Author:</span> <?= htmlspecialchars($dataById['author']) ?>
                            </p>
                            <!-- <p><span class="font-medium">Tahun Terbit:</span> 2019</p> -->
                            <p><span class="font-medium">Penerbit:</span>
                                <?= htmlspecialchars($dataById['publisher']) ?></p>
                            <!-- <p><span class="font-medium">Halaman:</span> 336</p> -->
                            <p><span class="font-medium">ISBN:</span> <?= htmlspecialchars($dataById['isbn']) ?></p>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">Description</h3>
                            <p class="text-gray-700" style="white-space: pre-line;">
                                <?= htmlspecialchars(str_replace(['<br>', '<br/>', '<br />'], "\n", $dataById['description'])) ?>
                            </p>
                        </div>

                        <button type="submit"
                            class="mt-6 w-full flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-hidden">
                            <a href='http://localhost/booklibrary/dashboard/rent/form.php?book=<?= $dataById['id'] ?>'
                                class='w-full'>
                                <span>Rent</span>
                            </a>
                        </button>

                        <!-- <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-2">Tersedia di:</h3>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    class="inline-block bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">
                                    Perpustakaan Pusat
                                </span>
                                <span
                                    class="inline-block bg-gray-100 text-gray-800 text-sm font-medium px-3 py-1 rounded-full">
                                    Perpustakaan Fakultas Psikologi
                                </span>
                            </div>
                        </div> -->
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="bg-gray-100 py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="mb-4 md:mb-0">
                    <h3 class="font-bold text-lg mb-2">BookLibrary</h3>
                    <p class="text-sm text-gray-600">Your online library solution.</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-8">
                    <div>
                        <!-- <h4 class="font-semibold mb-3">Resources</h4>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-sm text-gray-600 hover:text-blue-500">Blog</a></li>
                            <li><a href="#" class="text-sm text-gray-600 hover:text-blue-500">Help Center</a></li>
                            <li><a href="#" class="text-sm text-gray-600 hover:text-blue-500">Contact Us</a></li>
                        </ul> -->
                    </div>
                    <div>
                        <h4 class="font-semibold mb-3">Legal</h4>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-sm text-gray-600 hover:text-blue-500">Terms of Service</a></li>
                            <li><a href="#" class="text-sm text-gray-600 hover:text-blue-500">Privacy Policy</a></li>
                            <li><a href="#" class="text-sm text-gray-600 hover:text-blue-500">Cookie Policy</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-3">Follow Us</h4>
                        <div class="flex space-x-3">
                            <a href="#" class="text-gray-600 hover:text-blue-500">
                                <i class="fab fa-facebook h-5 w-5"></i>
                                <span class="sr-only">Facebook</span>
                            </a>
                            <a href="#" class="text-gray-600 hover:text-blue-500">
                                <i class="fab fa-twitter h-5 w-5"></i>
                                <span class="sr-only">Twitter</span>
                            </a>
                            <a href="#" class="text-gray-600 hover:text-blue-500">
                                <i class="fab fa-instagram h-5 w-5"></i>
                                <span class="sr-only">Instagram</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-8 border-t border-gray-200 pt-4">
                <p class="text-center text-sm text-gray-500">© 2025 BookLibrary. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    // Carousel functionality
    let currentSlide = 0;
    const slides = document.querySelectorAll('.carousel-item');
    const dots = document.querySelectorAll('.absolute.bottom-4 button');

    function showSlide(n) {
        // Hide all slides
        slides.forEach(slide => {
            slide.classList.remove('active');
        });

        // Remove active state from all dots
        dots.forEach(dot => {
            dot.classList.replace('bg-white', 'bg-white/50');
        });

        // Show the current slide
        slides[n].classList.add('active');

        // Highlight the current dot
        dots[n].classList.replace('bg-white/50', 'bg-white');

        // Update current slide index
        currentSlide = n;
    }

    function nextSlide() {
        showSlide((currentSlide + 1) % slides.length);
    }

    function prevSlide() {
        showSlide((currentSlide - 1 + slides.length) % slides.length);
    }

    function goToSlide(n) {
        showSlide(n);
    }

    // Auto cycle through slides
    setInterval(nextSlide, 5000);
    </script>

    <!-- Required for building and rendering -->
    <script src="https://cdn.gpteng.co/gptengineer.js" type="module"></script>
</body>

</html>