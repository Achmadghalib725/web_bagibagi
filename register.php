<?php
require 'config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if (!empty($name) && !empty($email) && !empty($password)) {
        // Cek apakah email sudah terdaftar
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Email sudah terdaftar. Silakan gunakan email lain atau <a href='login.php'>login</a>.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
            if (mysqli_query($conn, $query)) {
                $message = "Registrasi berhasil! Silakan <a href='login.php'>login</a>.";
            } else {
                $error = "Terjadi kesalahan saat registrasi.";
            }
        }
    } else {
        $error = "Semua kolom harus diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - DoTask</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-form-column">
                <div class="auth-header">
                    <h1 class="logotype">DoTask.</h1>
                    <h4>Sign up</h4>
                </div>
                <?php if(isset($message)): ?>
                    <div class="message-success" style="text-align:center;"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="message-error" style="text-align:center;"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="register.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn-primary-auth">Sign up with Email</button>
                </form>
                <div class="auth-footer">
                    <p>Already signed up? <a href="login.php">Go to login</a></p>
                </div>
            </div>
            <div class="auth-promo-column">
                <div class="promo-grid">
                     <div class="promo-item"><img src="assets/css/undraw_saving-notes_wp71.svg" alt="Ilustrasi" style="width:150px;"><p><strong>Saving notes</strong><br></p></div>
                    <div class="promo-item"><img src="assets/css/undraw_team-goals_0026.svg" alt="Ilustrasi" style="width:200px;"><p><strong>Team goals</strong><br></p></div>
                    <div class="promo-item"><img src="assets/css/undraw_to-do-list_eoia.svg" alt="Ilustrasi" style="width:90px;"><p><strong>To Do list</strong><br></p></div>
                    <div class="promo-item"><img src="assets/css/undraw_time-management_fedt.svg" alt="Ilustrasi" style="width:150px;"><p><strong>Time management</strong><br></p></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
