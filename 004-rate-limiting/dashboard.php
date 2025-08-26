<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>KF Labs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>Ini adalah dashboard aplikasi KF Labs.</p>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>