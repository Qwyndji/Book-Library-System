<?php
session_start(); // WAJIB untuk akses $_SESSION
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$role_id = $_SESSION['role_id'] ?? null;

$sql = "SELECT reports.*, users.name AS user_name, books.title AS book_title, books.image AS book_image
        FROM reports
        JOIN users ON reports.user_id = users.id
        JOIN books ON reports.book_id = books.id
        ORDER BY reports.id ASC";
$result = $conn->query($sql);
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
                            class="w-full flex items-center gap-2 px-4 py-2.5 rounded-md hover:bg-gray-100 ">
                            <i data-lucide="book" class="h-5 w-5"></i> <span class="font-medium">Book</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="mb-2">
                        <a href="http://localhost/booklibrary/dashboard/rent.php"
                            class="w-full flex items-center gap-2 px-4 py-2.5 rounded-md hover:bg-gray-100">
                            <i data-lucide="timer" class="h-5 w-5"></i> <span class="font-medium">Rent</span>
                        </a>
                    </li>

                    <?php if ($role_id != 2): ?>
                    <li class="mb-2">
                        <a href="http://localhost/booklibrary/dashboard/report.php"
                            class="w-full flex items-center gap-2 px-4 py-2.5 rounded-md hover:bg-gray-100 bg-indigo-100 text-indigo-700">
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
                <div class="flex items-center gap-4"> <button onclick="toggleSidebar()"
                        class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 lg:hidden"> <i data-lucide="menu"
                            class="h-5 w-5"></i> </button>
                    <div class="hidden md:flex items-center h-9 rounded-md border border-gray-200 bg-gray-50 "> <span
                            class="pl-3 pr-1"> <i data-lucide="search" class="h-4 w-4 text-gray-400 "></i> </span>
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
                <!-- Dashboard View -->
                <!-- <div id="dashboardView" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold">Dashboard Overview</h1>
                        <div> <select class="bg-white border border-gray-200 rounded-md px-3 py-1.5 text-sm">
                                <option>Last 7 days</option>
                                <option>Last 30 days</option>
                                <option>Last 3 months</option>
                                <option>Last 12 months</option>
                            </select> </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    </div>
                </div> -->
                <!-- Table View -->
                <div class="space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <h1 class="text-2xl font-bold">List Report</h1>
                        <div class="flex gap-2">
                            <!-- <button
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md border border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                                <i data-lucide="refresh-cw" class="h-4 w-4"></i> <span
                                    class="ml-1 hidden sm:inline">Refresh</span>
                            </button> -->
                            <!-- <button
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md border border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                                <i data-lucide="filter" class="h-4 w-4"></i> <span
                                    class="ml-1 hidden sm:inline">Filter</span>
                            </button> -->
                            <!-- <button
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md border border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                                <i data-lucide="download" class="h-4 w-4"></i> <span
                                    class="ml-1 hidden sm:inline">Export</span>
                            </button> -->
                            <button
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-200">
                                <a href="http://localhost/booklibrary/dashboard/report/form.php" class="w-full">
                                    <span>Add Report</span>
                                </a>
                            </button>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- <div class="p-4 border-b border-gray-200 ">
                            <div class="flex items-center gap-2">
                                <div class="relative flex-grow">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                                    </div> <input type="text" id="searchInput"
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white "
                                        placeholder="Search users..." oninput="handleSearch()">
                                </div>
                            </div>
                        </div> -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                                    <tr>
                                        <th class="px-6 py-3 text-left">
                                            <div class="flex items-center gap-1 cursor-pointer select-none">
                                                <span>No.</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left">
                                            <div class="flex items-center gap-1 cursor-pointer select-none">
                                                <span>User</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left">
                                            <div class="flex items-center gap-1 cursor-pointer select-none">
                                                <span>Book</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left">
                                            <div class="flex items-center gap-1 cursor-pointer select-none">
                                                <span>Description</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-left">
                                            <div class="flex items-center gap-1 cursor-pointer select-none">
                                                <span>Report Date</span>
                                            </div>
                                        </th>
                                        <th class="px-6 py-3 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 ">
                                    <?php
                                    if ($result->num_rows > 0) {
                                        $no = 1;
                                        while($row = $result->fetch_assoc()) {
                                            echo"
                                            <tr>
                                                <td class='px-6 py-4 whitespace-nowrap text-sm text-center'>" . $no++ . ".</td>
                                                <td class='px-6 py-4 whitespace-nowrap'>
                                                    <div class='flex items-center'>
                                                        <div class='text-sm font-medium'>" . $row["user_name"] . "</div>
                                                    </div>
                                                </td>
                                                <td class='px-6 py-4 whitespace-nowrap'>
                                                    <div class='flex items-center gap-3'>
                                                        <img 
                                                            src='../uploads/" . $row["book_image"] . "' 
                                                            alt='" . $row["book_title"] . "' 
                                                            class='h-[100px] w-[100px] object-cover rounded'
                                                        />
                                                        <div class='flex items-center'>
                                                            <div class='text-sm font-medium'>" . $row["book_title"] . "</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class='px-6 py-4 whitespace-nowrap text-sm'>" . $row["description"] . "</td>
                                                <td class='px-6 py-4 whitespace-nowrap text-sm'>" . $row["report_date"] . "</td>
                                                <td
                                                    class='px-6 py-4 flex flex-col gap-4 justify-center whitespace-nowrap text-center'>
                                                    <button
                                                        class='inline-flex items-center justify-center text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 transition-colors duration-200'>
                                                        <a href='http://localhost/booklibrary/dashboard/report/form.php?id=" . $row["id"] . "' class='w-full px-4 py-2'>
                                                            <span>Edit</span>
                                                        </a>
                                                    </button>
                                                    <button
                                                        class='inline-flex items-center justify-center text-sm font-medium rounded-md shadow-sm text-white bg-rose-600 hover:bg-rose-700 transition-colors duration-200'>
                                                        <a href='http://localhost/booklibrary/dashboard/report/delete.php?id=" . $row["id"] . "' onclick=\"return confirm('Are you sure you want to delete this data?')\" class='w-full px-4 py-2'>
                                                            <span>Delete</span>
                                                        </a>
                                                    </button>
                                                </td>
                                            </tr>
                                            "; }
                                        } else {
                                            echo "<tr><td class='px-6 py-4 whitespace-nowrap text-sm text-center'>No record found.</td></tr>";
                                        }
                                        $conn->close();
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <!-- <div
                            class="px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between border-t border-gray-200 ">
                            <div class="text-sm text-gray-500 mb-4 sm:mb-0"> Showing <span id="startIndex">1</span> to
                                <span id="endIndex">10</span> of <span id="totalItems">0</span> results
                            </div>
                            <div class="flex items-center space-x-1" id="pagination">
                            </div>
                        </div> -->
                    </div>
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
    </script>
</body>

</html>