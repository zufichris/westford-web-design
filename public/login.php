<?php
session_start();
require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $query = "SELECT id, password FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'User not found';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Shop</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .login-container {
        max-width: 400px;
        margin: 100px auto;
        padding: 2rem;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .login-container h2 {
        text-align: center;
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
    }

    .form-group input {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .error {
        color: #dc3545;
        margin-bottom: 1rem;
        text-align: center;
    }

    .register-link {
        text-align: center;
        margin-top: 1rem;
    }
    </style>
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="login-container">
        <h2>Login</h2>
        <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>