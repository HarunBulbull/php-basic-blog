<?php
session_start();
require_once '../config/config.php';

if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin') {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT user_id, user_fullName, user_password, user_role FROM users WHERE user_email = '$email' AND user_role = 'admin' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['user_password'])) {
            $_SESSION['admin_id'] = $user['user_id'];
            $_SESSION['admin_name'] = $user['user_fullName'];
            $_SESSION['admin_role'] = $user['user_role'];
            $_SESSION['last_activity'] = time();

            header("Location: index.php");
            exit();
        } else {
            $error = 'E-posta veya şifre hatalı!';
        }
    } else {
        $error = 'E-posta veya şifre hatalı!';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="../main.css">
</head>
<body>
    <div class="container h-screen flex items-center justify-center mx-auto">
        <div class="w-[80%] max-w-[500px] m-auto">
            <div class="rounded-md shadow-xl bg-(--secondary)">
                <div class="bg-(--primary) rounded-t-md flex items-center justify-center p-4">
                    <h3 class="text-center text-white clamp-h3 font-bold">Admin Girişi</h3>
                </div>
                <div class="flex flex-col gap-4 p-4">
                    <?php if ($error): ?>
                        <div class="w-full p-4 bg-red-200 text-red-900 border-[1px] border-red-900 clamp-p rounded-md"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" autocomplete="off">
                        <div class="mb-6 flex flex-col">
                            <label for="email" class="clamp-p">E-posta</label>
                            <input type="email" class="clamp-p outline-none border-[1px] border-white rounded-md p-2" id="email" name="email" required>
                        </div>
                        <div class="mb-6 flex flex-col">
                            <label for="password" class="clamp-p">Şifre</label>
                            <input type="password" class="clamp-p outline-none border-[1px] border-white rounded-md p-2" id="password" name="password" required>
                        </div>
                        <button type="submit" class="bg-(--primary) text-white rounded-sm p-2 text-center w-full">Giriş Yap</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 