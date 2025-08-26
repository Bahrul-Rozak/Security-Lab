<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: vulnerable_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Halo, <?php echo $_SESSION['username']; ?>!</h2>
        <p>Ini adalah dashboard aplikasi rentan :(</p>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>