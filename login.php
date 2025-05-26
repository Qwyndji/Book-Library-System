<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../config.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cek ke database
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            // Simpan session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role_id'] = $user['role_id'];

            // Redirect ke dashboard
            if ($user['role_id'] == 2) {
                header("Location: ../dashboard/rent.php"); // 🔁 redirect ke rent untuk role 2
            } else {
                header("Location: ../dashboard/book.php"); // default dashboard
            }
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title>Login - BookNGo</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="..\CSS\style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
</head>

<body>
    
    <div class="border-login">
        <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <!-- <img class="mx-auto h-10 w-auto"
                src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600" alt="Your Company"> -->
                <h1 style="font-family: 'Playfair Display', serif;" class="font-bold text-center text-5xl">BookLibrary</h1>
                <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Sign in to your account</h2>
                <?php if (!empty($error)): ?>
            <p class="text-red-600 text-center mt-4 text-sm"><?php echo $error; ?></p>
            <?php endif; ?>
        </div>
        
        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <form class="space-y-6" method="POST">
                <div>
                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
                    <div class="mt-2">
                        <input type="email" name="email" id="email" autocomplete="email" required
                            class="transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-pink-200 sm:text-sm/6">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                    </div>
                    <div class="mt-2">
                        <input type="password" name="password" id="password" autocomplete="current-password" required
                            class="transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-pink-200 sm:text-sm/6">
                    </div>
                </div>

                <div>
                    <button type="submit" name="login"
                    class="transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 flex w-full justify-center rounded-md bg-blue-200 px-3 py-1.5 text-sm/6 font-semibold text-black shadow-xs hover:bg-sky-200SS focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-100">
                    Login
                </button>
            </div>
        </form>
        
        <p class="mt-10 text-center text-sm/6 text-gray-500">
            Don't have an account?
            <a href="http://localhost/booklibrary/auth/register.php"
            class="font-semibold text-indigo-600 hover:text-gray-900">Register</a>
        </p>
    </div>
</div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>