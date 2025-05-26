<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$role_id = $_SESSION['role_id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

$id = $_GET['id'] ?? null;
$bookFromUrl = $_GET['book'] ?? null;

$editing = false;
$rentData = null;

// Periksa apakah ada 'id' di URL (untuk edit rent)
if ($id) {
    $editing = true;
    $rentQuery = "SELECT * FROM rents WHERE id = ?";
    $stmt = $conn->prepare($rentQuery);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $rentData = $result->fetch_assoc();
        $oldProofImage = $rentData['return_proof_image'] ?? '';
    } else {
        die("Data rent tidak ditemukan.");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = intval($_POST['user_id']);
    $bookId = intval($_POST['book_id']);
    $rentDate = $_POST['rent_date'];
    $dueDate = $_POST['due_date'];
    $returnDate = $_POST['return_date'];
    $lateDate = $_POST['late_date'];
    $rating = intval($_POST['rating']);
    $comment = $_POST['comment'];
    $returnProofImage = '';

    if (isset($_FILES['return_proof_image']) && $_FILES['return_proof_image']['error'] === UPLOAD_ERR_OK) {
        // Jika dalam mode edit, tidak perlu menambahkan gambar, tapi jika bukan edit maka wajib upload
        if (!$editing || (isset($_FILES['return_proof_image']) && $_FILES['return_proof_image']['error'] === UPLOAD_ERR_OK)) {
            $uploadDir = __DIR__ . '/../../uploads';
    
            // Membuat folder upload jika tidak ada
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
    
            // Memproses file gambar
            $fileTmpPath = $_FILES['return_proof_image']['tmp_name'];
            $fileName    = preg_replace("/[^A-Z0-9._-]/i", "_", basename($_FILES['return_proof_image']['name']));
            $fileExt     = pathinfo($fileName, PATHINFO_EXTENSION);
            $safeFileName = uniqid('rent_', true) . '.' . $fileExt;
            $destPath = $uploadDir . DIRECTORY_SEPARATOR . $safeFileName;
    
            // Menyimpan gambar ke direktori tujuan
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $returnProofImage = $safeFileName;
            } else {
                die("❌ Gagal memindahkan file ke: $destPath");
            }
        }
    } 
    // elseif (!$editing) {
    //     // Jika bukan dalam mode edit dan tidak ada gambar yang di-upload, beri pesan error
    //     die("❌ Gambar wajib di-upload.");
    // } 
    else {
        $returnProofImage = $rentData['return_proof_image'] ?? ''; 
    }

    // Cek apakah ada rent_id untuk update
    if (isset($_POST['rent_id']) && $_POST['rent_id'] != '') {
        // UPDATE
        $rentId = intval($_POST['rent_id']);
        $sql = "UPDATE rents SET user_id=?, book_id=?, rent_date=?, due_date=?, return_date=?, late_date=?, return_proof_image=?, rating=?, comment=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssssisi", $userId, $bookId, $rentDate, $dueDate, $returnDate, $lateDate, $returnProofImage, $rating, $comment, $rentId);

        if ($returnProofImage !== $oldProofImage && !empty($returnProofImage)) {
            $conn->query("UPDATE books SET stock = stock + 1 WHERE id = $bookId");
        }
    } else {
        // INSERT
        $sql = "INSERT INTO rents (user_id, book_id, rent_date, due_date, return_date, late_date, return_proof_image, rating, comment) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisssssis", $userId, $bookId, $rentDate, $dueDate, $returnDate, $lateDate, $returnProofImage, $rating, $comment);

        $conn->query("UPDATE books SET stock = stock - 1 WHERE id = $bookId AND stock > 0");
    }

    if ($stmt->execute()) {
        header("Location: ../rent.php");
        exit;
    } else {
        echo "Failed to save rent: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-50 ">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside
            class="fixed top-0 left-0 z-30 h-full w-64 bg-white border-r border-gray-200 transform transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:z-auto">
            <div class="flex justify-between items-center h-16 px-4 border-b border-gray-200 ">
                <div class="flex items-center"> <span class="text-xl font-bold">BookLibrary</span>
                </div> <button onclick="toggleSidebar()" class="lg:hidden p-1 rounded-md hover:bg-gray-100 "> <i
                        data-lucide="x" class="h-5 w-5"></i> </button>
            </div>
            <nav class="px-3 py-4">
                <div class="mb-4 px-4 text-xs font-semibold text-gray-500 uppercase">Main</div>
                <ul>
                    <?php if ($role_id != 2): ?>
                    <li class="mb-2">
                        <a href="http://localhost/booklibrary/dashboard/book.php"
                            class="w-full flex items-center gap-2 px-4 py-2.5 rounded-md hover:bg-gray-100">
                            <i data-lucide="book" class="h-5 w-5"></i> <span class="font-medium">Book</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="mb-2">
                        <a href="http://localhost/booklibrary/dashboard/rent.php"
                            class="w-full flex items-center gap-2 px-4 py-2.5 rounded-md hover:bg-gray-100 bg-indigo-100 text-indigo-700">
                            <i data-lucide="timer" class="h-5 w-5"></i> <span class="font-medium">Rent</span>
                        </a>
                    </li>

                    <?php if ($role_id != 2): ?>
                    <li class="mb-2">
                        <a href="http://localhost/booklibrary/dashboard/report.php"
                            class="w-full flex items-center gap-2 px-4 py-2.5 rounded-md hover:bg-gray-100 ">
                            <i data-lucide="list-checks" class="h-5 w-5"></i> <span class="font-medium">Report</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="http://localhost/booklibrary/dashboard/category.php"
                            class="w-full flex items-center gap-2 px-4 py-2.5 rounded-md hover:bg-gray-100 ">
                            <i data-lucide="layout-list" class="h-5 w-5"></i> <span class="font-medium">Category</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="mb-2">
                        <a href="http://localhost/booklibrary/index.php"
                            class="w-full flex items-center gap-2 px-4 py-2.5 rounded-md hover:bg-gray-100 ">
                            <i data-lucide="home" class="h-5 w-5"></i> <span class="font-medium">Home</span>
                        </a>
                    </li>
                </ul>

                <div class="mt-8 mb-4 px-4 text-xs font-semibold text-gray-500 uppercase">Settings</div>
                <ul>
                    <li class="mb-2">
                        <a href="http://localhost/booklibrary/auth/logout.php"
                            class="w-full flex items-center gap-2 px-4 py-2.5 rounded-md hover:bg-red-100 hover:text-red-700 ">
                            <i data-lucide="log-out" class="h-5 w-5"></i> <span class="font-medium">Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header class="h-16 flex items-center justify-between pl-4 pr-10 border-b border-gray-200 bg-white ">
                <div class="flex items-center gap-4">
                    <button onclick="toggleSidebar()"
                        class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 lg:hidden">
                        <i data-lucide="menu" class="h-5 w-5"></i>
                    </button>
                    <div class="hidden md:flex items-center h-9 rounded-md border border-gray-200 bg-gray-50 ">
                        <span class="pl-3 pr-1">
                            <i data-lucide="search" class="h-4 w-4 text-gray-400 "></i>
                        </span>
                        <input type="text" placeholder="Search..."
                            class="w-48 lg:w-64 bg-transparent border-0 outline-none py-1 text-sm">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <!-- <button onclick="toggleDarkMode()" class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 "> <i data-lucide="moon" class="h-5 w-5 "></i> <i data-lucide="sun-medium" class="h-5 w-5 hidden "></i> </button> -->
                    <div class="relative"> <button onclick="toggleNotifications()"
                            class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 "> <i data-lucide="bell"
                                class="h-5 w-5"></i> <span
                                class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span> </button> </div>
                    <div class="relative"> <button onclick="toggleUserMenu()"
                            class="flex items-center gap-2 p-1 rounded-md hover:bg-gray-100 ">
                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center"> <i
                                    data-lucide="user" class="h-4 w-4 text-indigo-600 "></i> </div> <span
                                class="hidden md:inline text-sm font-medium"><?php echo $_SESSION['user_name']; ?></span>
                        </button> </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                <div class="space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <h1 class="text-2xl font-bold"><?= $editing ? "Update" : "Add" ?> Rent</h1>
                    </div>
                    <form action="" method="POST" enctype="multipart/form-data"
                        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden p-10">

                        <?php if ($editing && $rentData): ?>
                        <input type="hidden" name="rent_id" value="<?= $rentData['id'] ?>">
                        <?php endif; ?>
                        <div class="space-y-6">

                            <div class="<?php echo ($role_id == 2) ? 'hidden' : ''; ?>">
                                <label for="user_id" class="block font-medium text-gray-700 mb-1">User
                                    <span class="text-red-500">*</span></label>
                                <select id="user_id" name="user_id"
                                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:outline-none transition-colors border-gray-300 focus:ring-blue-100 focus:border-blue-500"
                                    required>
                                    <option value="">Select a user</option>
                                    <?php
                                        $userQuery = "SELECT * FROM users";
                                        $userResult = $conn->query($userQuery);
                                        while ($row = $userResult->fetch_assoc()) {
                                            $selected = '';
                                            if ($editing && $rentData['book_id'] == $row["id"]) {
                                                $selected = "selected";
                                            } elseif (!$editing && $user_id == $row["id"]) {
                                                $selected = "selected";
                                            }
                                            echo "<option value='" . $row["id"] . "' $selected>" . $row["name"] . "</option>";
                                        }
                                        ?>
                                </select>
                            </div>

                            <div>
                                <label for="book_id" class="block font-medium text-gray-700 mb-1">Book
                                    <span class="text-red-500">*</span></label>
                                <select id="book_id" name="book_id"
                                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:outline-none transition-colors border-gray-300 focus:ring-blue-100 focus:border-blue-500"
                                    required>
                                    <option value="">Select a book</option>
                                    <?php
                                        $bookQuery = "SELECT * FROM books";
                                        $bookResult = $conn->query($bookQuery);
                                        while ($row = $bookResult->fetch_assoc()) {
                                            $selected = '';
                                            if ($editing && $rentData['book_id'] == $row["id"]) {
                                                $selected = "selected";
                                            } elseif (!$editing && $bookFromUrl == $row["id"]) {
                                                $selected = "selected";
                                            }
                                            echo "<option value='" . $row["id"] . "' $selected>" . $row["title"] . "</option>";
                                        }
                                    ?>
                                </select>
                            </div>

                            <div>
                                <label for="rent_date" class="block font-medium text-gray-700 mb-1">
                                    Rent Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="rent_date" name="rent_date"
                                    value="<?= $editing ? $rentData['rent_date'] : '' ?>"
                                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:outline-none transition-colors border-gray-300 focus:ring-blue-100 focus:border-blue-500"
                                    required />
                            </div>
                            <div>
                                <label for="due_date" class="block font-medium text-gray-700 mb-1">
                                    Due Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="due_date" name="due_date"
                                    value="<?= $editing ? $rentData['due_date'] : '' ?>"
                                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:outline-none transition-colors border-gray-300 focus:ring-blue-100 focus:border-blue-500"
                                    required />
                            </div>

                            <?php if ($editing): ?>
                            <div>
                                <label for="return_date" class="block font-medium text-gray-700 mb-1">
                                    Return Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="return_date" name="return_date"
                                    value="<?= $editing ? $rentData['return_date'] : '' ?>"
                                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:outline-none transition-colors border-gray-300 focus:ring-blue-100 focus:border-blue-500" />
                            </div>

                            <div>
                                <label for="late_date" class="block font-medium text-gray-700 mb-1">
                                    Late Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="late_date" name="late_date"
                                    value="<?= $editing ? $rentData['late_date'] : '' ?>"
                                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:outline-none transition-colors border-gray-300 focus:ring-blue-100 focus:border-blue-500" />
                            </div>

                            <div>
                                <label for="return_proof_image" class="block font-medium text-gray-700 mb-1">Return
                                    Proof Image
                                    <span class="text-red-500">*</span></label>
                                <input type="file" id="return_proof_image" name="return_proof_image"
                                    value="<?= $editing ? $rentData['return_proof_image'] : '' ?>"
                                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:outline-none transition-colors border-gray-300 focus:ring-blue-100 focus:border-blue-500"
                                    placeholder="Enter return proof image" />
                            </div>

                            <img id="imagePreview"
                                src="../../uploads/<?= $editing && isset($rentData['return_proof_image']) && !empty($rentData['return_proof_image']) ? $rentData['return_proof_image'] : '' ?>"
                                alt="Image Preview"
                                class="mt-4 <?= $editing ? '' : 'hidden' ?> w-[200px] h-[200px] object-cover rounded-md" />

                            <div>
                                <label for="rating" class="block font-medium text-gray-700 mb-1">Rating<span
                                        class="text-red-500">*</span></label>
                                <input type="number" id="rating" name="rating" min="1" max="5"
                                    value="<?= $editing ? $rentData['rating'] : '' ?>" oninput="limitRating(this)"
                                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:outline-none transition-colors border-gray-300 focus:ring-blue-100 focus:border-blue-500"
                                    placeholder="Enter rating" />
                            </div>

                            <div>
                                <label for="comment" class="block font-medium text-gray-700 mb-1">Comment<span
                                        class="text-red-500">*</span></label>
                                <textarea id="comment" name="comment"
                                    class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:outline-none transition-colors border-gray-300 focus:ring-blue-100 focus:border-blue-500"
                                    placeholder="Enter comment"><?= $editing ? htmlspecialchars($rentData['comment']) : '' ?></textarea>
                            </div>
                            <?php endif; ?>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <button type="button"
                                    class="inline-flex w-full cursor-pointer items-center justify-center px-4 py-2 text-sm font-medium rounded-md shadow-sm text-white bg-rose-600 hover:bg-rose-700 transition-colors duration-200">
                                    <a href="../rent.php" class="w-full"><span>Back</span></a>
                                </button>
                                <button type="submit"
                                    class="inline-flex w-full cursor-pointer items-center justify-center px-4 py-2 text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-200">
                                    <span><?= $editing ? "Update" : "Add" ?> Rent</span>
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </main>
        </div>
    </div>
    <script>
    // Initialize Lucide icons
    lucide.createIcons();

    function getStatusColor(status) {
        switch (status.toLowerCase()) {
            case 'active':
                return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-400';
            case 'inactive':
                return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
            case 'pending':
                return 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-400';
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
        }
    }

    // Sidebar toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('-translate-x-full');
    }

    // Dark mode toggle
    function toggleDarkMode() {
        document.documentElement.classList.toggle('dark');
    }

    function limitRating(el) {
        const val = parseInt(el.value);
        if (val > 5) el.value = 5;
        if (val < 1) el.value = 1;
    }
    </script>
</body>

</html>