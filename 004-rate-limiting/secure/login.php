<?php
session_start();
$conn = new mysqli("localhost", "root", "", "secure_lab_rate_limiting");
$error = "";
$lockout_time = 30; 
$max_attempts = 3; 

function log_login_attempt($conn, $username, $ip, $successful) {
    $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, username, successful) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $ip, $username, $successful);
    $stmt->execute();
    $stmt->close();
}

function is_locked_out($conn, $username, $ip) {
    global $max_attempts, $lockout_time;

    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempts
        FROM login_attempts
        WHERE (ip_address = ? OR username = ?)
        AND successful = 0
        AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
    ");
    $stmt->bind_param("ssi", $ip, $username, $lockout_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    return $data['attempts'] >= $max_attempts;
}

function get_lockout_time_remaining($conn, $username, $ip) {
    global $lockout_time;

    $stmt = $conn->prepare("
        SELECT MAX(attempt_time) as last_attempt
        FROM login_attempts
        WHERE (ip_address = ? OR username = ?)
        AND successful = 0
    ");
    $stmt->bind_param("ss", $ip, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if ($data['last_attempt']) {
        $last_attempt = strtotime($data['last_attempt']);
        $remaining = $lockout_time - (time() - $last_attempt);
        return max(0, $remaining);
    }

    return 0;
}

$ip = $_SERVER['REMOTE_ADDR'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (is_locked_out($conn, $username, $ip)) {
        $remaining = get_lockout_time_remaining($conn, $username, $ip);
        $error = "Terlalu banyak percobaan login. Silakan coba lagi dalam " . ceil($remaining / 60) . " menit.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password_hash'])) {
                log_login_attempt($conn, $username, $ip, 1);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php");
                exit();
            } else {
                log_login_attempt($conn, $username, $ip, 0);
                $error = "Username atau password salah!";
            }
        } else {
            log_login_attempt($conn, $username, $ip, 0);
            $error = "Username atau password salah!";
        }

        $stmt->close();
    }
}

$remaining_attempts = $max_attempts;
if (isset($username)) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempts
        FROM login_attempts
        WHERE (ip_address = ? OR username = ?)
        AND successful = 0
        AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)
    ");
    $stmt->bind_param("ssi", $ip, $username, $lockout_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $remaining_attempts = max(0, $max_attempts - $data['attempts']);
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>KF Lab</title>
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

                        <?php if (isset($remaining_attempts) && $remaining_attempts > 0): ?>
                            <div class="alert alert-info">
                                Percobaan login tersisa: <?php echo $remaining_attempts; ?>
                            </div>
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

                        <div class="mt-3 text-center">
                            <p>Rate limiting aktif: Maksimal <?php echo $max_attempts; ?> percobaan dalam <?php echo round($lockout_time / 60); ?> menit.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>