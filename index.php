<?php
session_start();
include './config.php';

$sqlCategories = "SELECT * FROM categories ORDER BY id ASC";
$resultCategories = $conn->query($sqlCategories);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $sqlbooks = "SELECT books.*, 
                    categories.name AS category_name,
                    AVG(rents.rating) AS avg_rating, 
                    COUNT(rents.rating) AS total_ratings 
                FROM books 
                LEFT JOIN categories ON books.category_id = categories.id 
                LEFT JOIN rents ON books.id = rents.book_id
                WHERE books.title LIKE ? 
                    OR books.author LIKE ? 
                    OR categories.name LIKE ?
                GROUP BY books.id
                ORDER BY books.id ASC";
    $stmt = $conn->prepare($sqlbooks);
    $searchTerm = '%' . $search . '%';
    $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $resultBooks = $stmt->get_result();
} else {
    $sqlbooks = "SELECT 
                    books.*, 
                    categories.name AS category_name,
                    AVG(rents.rating) AS avg_rating, 
                    COUNT(rents.rating) AS total_ratings
                FROM books 
                LEFT JOIN categories ON books.category_id = categories.id 
                LEFT JOIN rents ON books.id = rents.book_id
                GROUP BY books.id
                ORDER BY books.id ASC";
    $resultBooks = $conn->query($sqlbooks);
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
                <!-- Search Section -->
                <section x-data="{ isSearchFocused: false, searchQuery: '' }">
                    <div class="relative">
                        <form method="GET">
                            <div class="flex items-center">
                                <div class="relative flex-grow">
                                    <i
                                        class="fas fa-search absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"></i>
                                    <input type="text" name="search"
                                        placeholder="Search for books, authors, or categories..."
                                        class="search-input w-[400px] rounded-md border border-gray-300 pl-9 pr-4 py-2 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        x-model="searchQuery" @focus="isSearchFocused = true"
                                        @blur="setTimeout(() => { isSearchFocused = false }, 100)" />
                                </div>
                                <button type="submit"
                                    class="ml-2 bg-indigo-500 text-white px-4 py-2 rounded-md hover:bg-indigo-600">
                                    Search
                                </button>
                            </div>
                        </form>

                        <div x-show="isSearchFocused"
                            class="absolute left-0 right-0 top-full mt-2 rounded-lg border bg-white p-4 shadow-md z-10">
                            <h3 class="mb-2 font-medium">Popular Categories</h3>
                            <div class="flex flex-wrap gap-2">

                                <?php while ($category = $resultCategories->fetch_assoc()): ?>
                                <button
                                    class="rounded-full bg-gray-100 px-4 py-2 text-sm font-medium transition-colors hover:bg-indigo-500 hover:text-white"
                                    @mousedown.prevent="searchQuery = '<?php echo htmlspecialchars($category['name'], ENT_QUOTES); ?>'; isSearchFocused = false">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </button>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </section>
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

    <main>
        <!-- Carousel Section -->
        <section class="carousel-container h-[400px] md:h-[500px] relative overflow-hidden">
            <div class="carousel-item active"
                style="background-image: url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&q=80&w=1200'); background-size: cover; background-position: center;">
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-8 md:p-12 text-white max-w-lg">
                    <h2 class="text-3xl md:text-4xl font-bold mb-2">Discover New Worlds</h2>
                    <p class="text-lg md:text-xl opacity-90 mb-6">Explore our vast collection of fantasy books and
                        immerse yourself in magical adventures.</p>
                    <!-- <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors">
                        Explore Now
                    </button> -->
                </div>
            </div>
            <div class="carousel-item"
                style="background-image: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&q=80&w=1200'); background-size: cover; background-position: center;">
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-8 md:p-12 text-white max-w-lg">
                    <h2 class="text-3xl md:text-4xl font-bold mb-2">Learn and Grow</h2>
                    <p class="text-lg md:text-xl opacity-90 mb-6">Access our educational collection to expand your
                        knowledge and skills.</p>
                    <!-- <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors">
                        Explore Now
                    </button> -->
                </div>
            </div>
            <div class="carousel-item"
                style="background-image: url('https://images.unsplash.com/photo-1526243741027-444d633d7365?auto=format&fit=crop&q=80&w=1200'); background-size: cover; background-position: center;">
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-8 md:p-12 text-white max-w-lg">
                    <h2 class="text-3xl md:text-4xl font-bold mb-2">Stories for Everyone</h2>
                    <p class="text-lg md:text-xl opacity-90 mb-6">Find your next favorite read from our curated
                        selections.</p>
                    <!-- <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors">
                        Explore Now
                    </button> -->
                </div>
            </div>

            <!-- <button
                class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-white/50 backdrop-blur-sm hover:bg-white/80 p-2"
                onclick="prevSlide()">
                <i class="fas fa-chevron-left h-6 w-6"></i>
                <span class="sr-only">Previous slide</span>
            </button>

            <button
                class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-white/50 backdrop-blur-sm hover:bg-white/80 p-2"
                onclick="nextSlide()">
                <i class="fas fa-chevron-right h-6 w-6"></i>
                <span class="sr-only">Next slide</span>
            </button> -->

            <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 space-x-2">
                <button class="h-2 w-2 rounded-full bg-white" onclick="goToSlide(0)">
                    <span class="sr-only">Go to slide 1</span>
                </button>
                <button class="h-2 w-2 rounded-full bg-white/50" onclick="goToSlide(1)">
                    <span class="sr-only">Go to slide 2</span>
                </button>
                <button class="h-2 w-2 rounded-full bg-white/50" onclick="goToSlide(2)">
                    <span class="sr-only">Go to slide 3</span>
                </button>
            </div>
        </section>

        <div class="container px-4 py-12 mx-auto">
            <!-- Recommendations Section -->
            <section class="mb-16">
                <h2 class="text-2xl font-bold mb-6">Recommendations For You</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <?php while ($row = $resultBooks->fetch_assoc()) : ?>
                    <div
                        class="relative flex flex-col overflow-hidden rounded-lg border bg-white shadow-sm transition-all hover: shadow-md">
                        <a href="http://localhost/booklibrary/detail.php?id=<?= htmlspecialchars($row['id']) ?>">
                            <div class="overflow-hidden bg-gray-100">
                                <img src="<?= './uploads/' . htmlspecialchars($row['image']) ?>"
                                    alt="<?= htmlspecialchars($row['title']) ?>"
                                    class="h-[560px] w-full object-cover transition-transform duration-300 hover:scale-105" />
                            </div>
                            <div class="p-4">
                                <h3 class="line-clamp-2 font-semibold leading-tight">
                                    <?= htmlspecialchars($row['title']) ?>
                                </h3>
                                <p class="mt-1 text-sm text-gray-500 line-clamp-2">
                                    <?= htmlspecialchars($row['description']) ?>
                                </p>
                                <div class="flex items-center space-x-1 mt-2">
                                    <?php
                                        $roundedRating = intval($row['avg_rating']);
                                        $totalStars = 5;
                                    ?>
                                    <span class="ml-1 text-sm font-medium">
                                        <?= $roundedRating ?>
                                    </span>
                                    <?php for ($i = 1; $i <= $totalStars; $i++): ?>
                                    <i
                                        class="fas fa-star <?= $i <= $roundedRating ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                    <?php endfor; ?>
                                    <span class="ml-1 text-sm font-medium">
                                        (<?= $row['total_ratings'] ?>)
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endwhile; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>

</html>