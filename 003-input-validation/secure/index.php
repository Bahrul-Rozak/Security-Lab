<?php
$conn = new mysqli("localhost", "root", "", "secure_lab_input_validation");
$message = "";
$errors = [];

function validateName($name) {
    if (!preg_match("/^[a-zA-Z0-9\\s\\.\\-',]{1,100}$/", $name)) {
        return "Nama hanya boleh mengandung huruf, angka, spasi, dan karakter umum (. - , ')";
    }
    return true;
}

function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Format email tidak valid";
    }
    return true;
}

function validateAge($age) {
    if (!is_numeric($age) || $age < 1 || $age > 150) {
        return "Usia harus antara 1 dan 150";
    }
    return true;
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $age = sanitizeInput($_POST['age']);

    $nameValidation = validateName($name);
    if ($nameValidation !== true) {
        $errors['name'] = $nameValidation;
    }

    $emailValidation = validateEmail($email);
    if ($emailValidation !== true) {
        $errors['email'] = $emailValidation;
    }

    $ageValidation = validateAge($age);
    if ($ageValidation !== true) {
        $errors['age'] = $ageValidation;
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO users (name, email, age) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $email, $age);

        if ($stmt->execute()) {
            $message = "Data berhasil disimpan!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
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
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Form Input Data (Versi Aman)</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?php echo $message; ?></div>
                        <?php endif; ?>

                        <form method="POST" id="userForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama</label>
                                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                                       id="name" name="name" required
                                       pattern="[a-zA-Z0-9\\s\\.\\-',]{1,100}"
                                       title="Hanya huruf, angka, spasi, dan karakter umum (. - , ')">
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                                       id="email" name="email" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="age" class="form-label">Usia</label>
                                <input type="number" class="form-control <?php echo isset($errors['age']) ? 'is-invalid' : ''; ?>"
                                       id="age" name="age" min="1" max="150" required>
                                <?php if (isset($errors['age'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['age']; ?></div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Simpan</button>
                        </form>

                        <hr>

                        <h4>Data Tersimpan:</h4>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Usia</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['age']); ?></td>
                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('userForm').addEventListener('submit', function(e) {
            let isValid = true;
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const age = document.getElementById('age').value;

            const namePattern = /^[a-zA-Z0-9\\s\\.\\-',]{1,100}$/;
            if (!namePattern.test(name)) {
                alert('Nama hanya boleh mengandung huruf, angka, spasi, dan karakter umum (. - , \\')');
                isValid = false;
            }

            const emailPattern = /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/;
            if (!emailPattern.test(email)) {
                alert('Format email tidak valid');
                isValid = false;
            }

            if (isNaN(age) || age < 1 || age > 150) {
                alert('Usia harus antara 1 dan 150');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
