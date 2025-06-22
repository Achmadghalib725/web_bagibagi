<?php
// admin_edit_task.php
require 'templates/header.php';

// --- Keamanan: Cek apakah admin sudah login ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- Validasi ID tugas ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$task_id_to_edit = intval($_GET['id']);

// --- Jika form disubmit ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);

    $title = mysqli_real_escape_string($conn, $title);
    $description = mysqli_real_escape_string($conn, $description);
    $status = mysqli_real_escape_string($conn, $status);

    $allowed_statuses = ['pending', 'in_progress', 'completed'];

    if (in_array($status, $allowed_statuses)) {
        $update_query = "UPDATE tasks 
                         SET title = '$title', description = '$description', status = '$status' 
                         WHERE id = $task_id_to_edit";

        if (mysqli_query($conn, $update_query)) {
            header("Location: admin_dashboard.php?status=task_updated");
            exit();
        } else {
            $error_message = "❌ Gagal memperbarui data tugas.";
        }
    } else {
        $error_message = "❌ Status yang dipilih tidak valid.";
    }
}

// --- Ambil data tugas berdasarkan ID ---
$query = "SELECT tasks.*, users.name AS creator_name 
          FROM tasks 
          JOIN users ON tasks.creator_id = users.id 
          WHERE tasks.id = $task_id_to_edit";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: admin_dashboard.php?status=task_not_found");
    exit();
}

$task = mysqli_fetch_assoc($result);
?>

<div class="page-header">
    <h3>Edit Tugas (Admin)</h3>
    <div class="page-actions">
        <a href="admin_dashboard.php" class="btn-secondary">Kembali ke Dashboard Admin</a>
    </div>
</div>
<div class="pemilik-tugas">
    <p>Anda sedang mengedit tugas milik: <strong><?= htmlspecialchars($task['creator_name']); ?></strong></p>
</div>
<?php if (!empty($error_message)): ?>
    <div class="message-error"><?= $error_message; ?></div>
<?php endif; ?>

<form class="form-container" action="admin_edit_task.php?id=<?= $task_id_to_edit; ?>" method="POST">
    <div class="form-group">
        <label for="title">Judul Tugas</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($task['title']); ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="5"><?= htmlspecialchars($task['description']); ?></textarea>
    </div>

    <div class="form-group">
        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : ''; ?>>Tertunda</option>
            <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : ''; ?>>Dikerjakan</option>
            <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : ''; ?>>Selesai</option>
        </select>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">💾 Simpan Perubahan</button>
    </div>
</form>

<?php require 'templates/footer.php'; ?>
