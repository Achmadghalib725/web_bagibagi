
<?php
// admin_edit_user.php
require 'templates/header.php';

// --- Keamanan Berlapis ---
// 1. Pastikan yang mengakses adalah admin yang sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. Pastikan ada ID pengguna yang dikirim melalui URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id_to_edit = $_GET['id'];

// --- Logika untuk Memproses Form Saat Disubmit (POST) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Validasi sederhana
    if ($role == 'user' || $role == 'admin') {
        // Query untuk update data pengguna
        $update_query = "UPDATE users SET name = '$name', role = '$role' WHERE id = $user_id_to_edit";
        
        if (mysqli_query($conn, $update_query)) {
            // Redirect kembali ke dashboard admin dengan pesan sukses
            header("Location: admin_dashboard.php?status=user_updated");
            exit();
        } else {
            $error_message = "Gagal memperbarui data pengguna.";
        }
    } else {
        $error_message = "Peran yang dipilih tidak valid.";
    }
}


// --- Logika untuk Mengambil Data Awal Pengguna (GET) ---
$query = "SELECT id, name, email, role FROM users WHERE id = $user_id_to_edit";
$result = mysqli_query($conn, $query);

// Jika pengguna dengan ID tersebut tidak ditemukan
if (mysqli_num_rows($result) == 0) {
    header("Location: admin_dashboard.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

?>

<div class="page-header">
    <h3>Edit Pengguna</h3>
    <div class="page-actions">
        <a href="admin_dashboard.php" class="btn-secondary">Kembali ke Dashboard Admin</a>
    </div>
</div>

<?php if (isset($error_message)): ?>
    <div class="message-error"><?php echo $error_message; ?></div>
<?php endif; ?>

<form class="form-container" action="admin_edit_user.php?id=<?php echo $user_id_to_edit; ?>" method="POST">
    <div class="form-group">
        <label for="email">Email Pengguna (tidak dapat diubah)</label>
        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
    </div>
    <div class="form-group">
        <label for="name">Nama</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
    </div>
    <div class="form-group">
        <label for="role">Peran (Role)</label>
        <select id="role" name="role">
            <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>User</option>
            <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
        </select>
    </div>
    <div class="form-actions">
        <button type="submit">Simpan Perubahan</button>
    </div>
</form>

<?php
require 'templates/footer.php';
?>