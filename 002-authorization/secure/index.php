<?php
session_start();
$conn = new mysqli("localhost", "root", "", "secure_lab_authorization");

function checkAuthorization($requiredRole = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ?login=1");
        exit();
    }

    if ($requiredRole && $_SESSION['role'] !== $requiredRole) {
        header("Location: ?error=unauthorized");
        exit();
    }

    return true;
}

function getUserData($conn) {
    if (!isset($_SESSION['user_id'])) return null;

    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

$user = isset($_SESSION['user_id']) ? getUserData($conn) : null;

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ?");
    exit();
}

if (isset($_GET['login'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();

            if (password_verify($password, $user_data['password_hash'])) {
                $_SESSION['user_id'] = $user_data['id'];
                $_SESSION['username'] = $user_data['username'];
                $_SESSION['role'] = $user_data['role'];

                header("Location: ?");
                exit();
            }
        }

        echo "<div class='alert alert-danger'>Login gagal!</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>KF Lab</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">KF Lab</a>
            <div class="navbar-nav ms-auto">
                <?php if(isset($user)): ?>
                    <span class="navbar-text">Selamat datang, <?php echo htmlspecialchars($user['username']); ?> (<?php echo $user['role']; ?>)</span>
                    <a class="nav-link" href="?logout=1">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="?login=1">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
            <div class="alert alert-danger">Anda tidak memiliki izin untuk mengakses halaman tersebut.</div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="?" class="list-group-item list-group-item-action">Beranda</a>
                    <a href="?page=profile" class="list-group-item list-group-item-action">Profil</a>

                    <?php if(isset($user) && $user['role'] === 'admin'): ?>
                        <a href="?page=admin" class="list-group-item list-group-item-action">Panel Admin</a>
                    <?php endif; ?>

                    <a href="?page=settings" class="list-group-item list-group-item-action">Pengaturan</a>
                </div>
            </div>
            <div class="col-md-9">
                <?php
                if (isset($_GET['page'])) {
                    $page = $_GET['page'];

                    if ($page === 'profile') {
                        checkAuthorization(); 
                        echo "<h3>Halaman Profil</h3>";
                        echo "<p>Ini adalah halaman profil untuk " . htmlspecialchars($user['username']) . "</p>";
                    }
                    elseif ($page === 'admin') {
                        checkAuthorization('admin'); 
                        echo "<h3>Panel Administrator</h3>";
                        echo "<p>Ini adalah panel administrator yang hanya bisa diakses oleh admin</p>";
                        echo "<div class='alert alert-info'>Data sensitif sistem hanya ditampilkan untuk admin!</div>";
                    }
                    elseif ($page === 'settings') {
                        checkAuthorization(); 
                        echo "<h3>Pengaturan Pengguna</h3>";
                        echo "<p>Ini adalah halaman pengaturan</p>";
                    }
                    else {
                        echo "<h3>Halaman Tidak Ditemukan</h3>";
                    }
                } else {
                    echo "<h3>Beranda</h3>";
                    echo "<p>Selamat datang di aplikasi KF Lab</p>";

                    if (!isset($user)) {
                        echo "<p>Silakan <a href='?login=1'>login</a> untuk mengakses fitur lengkap.</p>";
                    }
                }

                if (isset($_GET['login']) && !isset($user)) {
                    echo '
                    <h3>Login</h3>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
