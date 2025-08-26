<?php
session_start();
$conn = new mysqli("localhost", "root", "", "security_lab_authorization");

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $user_query = $conn->query("SELECT * FROM users WHERE username='$username'");
    $user = $user_query->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>KF Lab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">KF Lab</a>
            <div class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['username'])): ?>
                    <span class="navbar-text">Selamat datang, <?php echo $_SESSION['username']; ?></span>
                    <a class="nav-link" href="?logout=1">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="?login=1">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="?" class="list-group-item list-group-item-action">Beranda</a>
                    <a href="?page=profile" class="list-group-item list-group-item-action">Profil</a>
                    
                    
                    <a href="?page=admin" class="list-group-item list-group-item-action">Panel Admin</a>
                    
                    <a href="?page=settings" class="list-group-item list-group-item-action">Pengaturan</a>
                </div>
            </div>
            <div class="col-md-9">
                <?php
               
                if (isset($_GET['page'])) {
                    $page = $_GET['page'];
                    
                    if ($page === 'profile') {
                        echo "<h3>Halaman Profil</h3>";
                        echo "<p>Ini adalah halaman profil untuk " . $_SESSION['username'] . "</p>";
                    } 
                    elseif ($page === 'admin') {
                        echo "<h3>Panel Administrator</h3>";
                        echo "<p>Ini adalah panel administrator yang seharusnya hanya bisa diakses oleh admin</p>";
                        echo "<div class='alert alert-danger'>Data sensitif sistem ditampilkan di sini!</div>";
                    }
                    elseif ($page === 'settings') {
                        echo "<h3>Pengaturan Pengguna</h3>";
                        echo "<p>Ini adalah halaman pengaturan</p>";
                    }
                    else {
                        echo "<h3>Halaman Tidak Ditemukan</h3>";
                    }
                } else {
                    echo "<h3>Beranda</h3>";
                    echo "<p>Selamat datang di aplikasi KF Lab</p>";
                }
                
                if (isset($_GET['logout'])) {
                    session_destroy();
                    header("Location: ?");
                    exit();
                }
                
                if (isset($_GET['login'])) {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $username = $_POST['username'];
                        $password = $_POST['password'];
                        
                        $result = $conn->query("SELECT * FROM users WHERE username='$username' AND password='$password'");
                        
                        if ($result->num_rows > 0) {
                            $_SESSION['username'] = $username;
                            header("Location: ?");
                            exit();
                        } else {
                            echo "<div class='alert alert-danger'>Login gagal!</div>";
                        }
                    }
                    
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