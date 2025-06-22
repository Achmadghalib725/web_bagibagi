<?php
// edit_profile.php (Versi Definitif)
require 'config.php';

// Logika PHP di atas sini tidak berubah
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];
$message_success = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Logika Update Nama
    if (isset($_POST['update_profile'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $update_name_query = "UPDATE users SET name = '$name' WHERE id = $user_id";
        if (mysqli_query($conn, $update_name_query)) {
            $_SESSION['user_name'] = $name;
            $message_success = 'Nama berhasil diperbarui.';
        } else { $error_message = 'Gagal memperbarui nama.'; }
    }
    // Logika Ganti Password
    if (isset($_POST['change_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $user_query = "SELECT password FROM users WHERE id = $user_id";
        $user_result = mysqli_query($conn, $user_query);
        $user = mysqli_fetch_assoc($user_result);
        if (password_verify($old_password, $user['password'])) {
            if ($new_password == $confirm_password) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pass_query = "UPDATE users SET password = '$hashed_new_password' WHERE id = $user_id";
                if (mysqli_query($conn, $update_pass_query)) {
                    $message_success = 'Password berhasil diganti.';
                } else { $error_message = 'Gagal mengganti password.'; }
            } else { $error_message = 'Password baru dan konfirmasi tidak cocok.'; }
        } else { $error_message = 'Password lama salah.'; }
    }
    // Logika Upload Foto
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/";
        $image_name = uniqid() . '-' . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
        if ($check !== false && $_FILES["profile_picture"]["size"] < 2000000 && in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $update_pic_query = "UPDATE users SET profile_picture = '$image_name' WHERE id = $user_id";
                if(mysqli_query($conn, $update_pic_query)) {
                    $message_success = "Foto profil berhasil diperbarui.";
                } else { $error_message = "Gagal menyimpan path gambar."; }
            } else { $error_message = "Terjadi kesalahan saat mengunggah file."; }
        } else { $error_message = "File bukan gambar yang valid atau ukurannya terlalu besar (maks 2MB)."; }
    }
}

// Ambil data terbaru untuk ditampilkan
$query = "SELECT name, email, profile_picture FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Panggil header SETELAH semua logika selesai
require 'templates/header.php';
?>

<div class="content-wrapper">

    <header class="content-header-main">
        <h1>Pengaturan Profil</h1>
        <p>Kelola informasi dan keamanan akun Anda.</p>
    </header>

    <?php if ($message_success): ?>
        <div class="message-success"><?php echo $message_success; ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="message-error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="profile-page-layout">
        <div class="profile-form-section">
            <h4>Detail Profil</h4>
            <div class="profile-picture-section">
                <h4>Foto Profil</h4>
                    <img src="uploads/<?php echo htmlspecialchars($user['profile_picture'] ? $user['profile_picture'] : 'default.png'); ?>" alt="Foto Profil" class="profile-pic-large">
                <form action="edit_profile.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="profile_picture_input" class="file-upload-label">Pilih Gambar Baru</label>
                        <input type="file" id="profile_picture_input" name="profile_picture" required>
                    </div>
                    <button type="submit">Unggah Foto</button>
                </form>
            </div>
            <form action="edit_profile.php" method="post">
                <div class="form-group">
                    <label for="name">Nama</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email (tidak dapat diubah)</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                </div>
                <button type="submit" name="update_profile">Simpan Perubahan</button>
            </form>

            <hr>

            <h4>Ganti Password</h4>
            <form action="edit_profile.php" method="post">
                <div class="form-group">
                    <label for="old_password">Password Lama</label>
                    <input type="password" id="old_password" name="old_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password Baru</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password">Ganti Password</button>
            </form>
        </div>
        
        
    </div>

</div> <?php
require 'templates/footer.php';