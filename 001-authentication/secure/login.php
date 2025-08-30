<?php
session_start();
$error = "";


if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $current_time = time();
    if ($_SESSION['login_attempts'] >= 5 && ($current_time - $_SESSION['last_attempt_time']) < 900) {
        $error = "Terlalu banyak percobaan login. Silakan coba lagi setelah 15 menit.";
    } else {
        $conn = new mysqli("localhost", "root", "", "secure_lab_authentication");

        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);

        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['logged_in'] = true;

                $_SESSION['login_attempts'] = 0;

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Username atau password salah!";
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
            }
        } else {
            $error = "Username atau password salah!";
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
