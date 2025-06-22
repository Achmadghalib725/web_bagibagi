<?php
require 'config.php';


// Cek login
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: dashboard.php"); exit(); }

$user_id = $_SESSION['user_id'];
$task_id = $_GET['id'];
$error_message = '';
$message_success = '';

// Ambil data tugas
$query = "SELECT t.*, u.name as creator_name FROM tasks t JOIN users u ON t.creator_id = u.id WHERE t.id = $task_id";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) == 0) {
    require 'templates/header.php';
    echo "<div class='content-wrapper'><p>Tugas tidak ditemukan.</p></div>";
    require 'templates/footer.php';
    exit();
}
$task = mysqli_fetch_assoc($result);

// Escape judul tugas untuk notifikasi
$task_title = mysqli_real_escape_string($conn, $task['title']);

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Update tugas
    if (isset($_POST['update_task'])) {
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        $update_query = "UPDATE tasks SET title = '$title', description = '$description', status = '$status' WHERE id = $task_id";
        if ($_SESSION['user_role'] !== 'admin') {
            $update_query .= " AND creator_id = $user_id";
        }

        if (mysqli_query($conn, $update_query)) {
            $message_success = "Tugas berhasil diperbarui.";
            $result = mysqli_query($conn, $query);
            $task = mysqli_fetch_assoc($result);
            $task_title = mysqli_real_escape_string($conn, $task['title']); // update judul jika berubah
        } else {
            $error_message = "Gagal memperbarui tugas.";
        }
    }

    // Undang kolaborator
    if (isset($_POST['invite_user'])) {
        $invite_email = mysqli_real_escape_string($conn, $_POST['invite_email']);

        $find_user_query = "SELECT id FROM users WHERE email = '$invite_email'";
        $find_user_result = mysqli_query($conn, $find_user_query);

        if (mysqli_num_rows($find_user_result) > 0) {
            $invited_user = mysqli_fetch_assoc($find_user_result);
            $invited_user_id = $invited_user['id'];

            if ($invited_user_id == $task['creator_id']) {
                $error_message = "Pengguna ini adalah pemilik tugas.";
            } else {
                $check_collab_query = "SELECT * FROM task_collaborators WHERE task_id = $task_id AND user_id = $invited_user_id";
                $check_collab_result = mysqli_query($conn, $check_collab_query);

                if (mysqli_num_rows($check_collab_result) > 0) {
                    $error_message = "Pengguna ini sudah menjadi kolaborator.";
                } else {
                    $insert_collab_query = "INSERT INTO task_collaborators (task_id, user_id) VALUES ($task_id, $invited_user_id)";
                    if (mysqli_query($conn, $insert_collab_query)) {
                        $message_success = "Pengguna berhasil diundang.";

                        // Notifikasi undangan
                        $inviter_name = mysqli_real_escape_string($conn, $_SESSION['user_name']);
                        $notification_message = "$inviter_name mengundang Anda untuk berkolaborasi di tugas '$task_title'";
                        $notification_link = "edit_task.php?id=" . $task_id;
                        $notif_query = "INSERT INTO notifications (user_id, message, link) VALUES ($invited_user_id, '$notification_message', '$notification_link')";
                        mysqli_query($conn, $notif_query);
                    } else {
                        $error_message = "Gagal mengundang pengguna.";
                    }
                }
            }
        } else {
            $error_message = "Pengguna tidak ditemukan.";
        }
    }

    // Tambah komentar
    if (isset($_POST['submit_comment'])) {
        $comment_text = mysqli_real_escape_string($conn, $_POST['comment']);
        if (!empty($comment_text)) {
            $insert_comment = "INSERT INTO comments (task_id, user_id, comment) VALUES ($task_id, $user_id, '$comment_text')";
            mysqli_query($conn, $insert_comment);

            // Notifikasi komentar ke semua user terkait
            $notif_raw = $_SESSION['user_name'] . " mengomentari tugas '$task_title'";
            $notif_message = mysqli_real_escape_string($conn, $notif_raw);

            $notif_link = "edit_task.php?id=" . $task_id;

            $users_query = "SELECT user_id FROM task_collaborators WHERE task_id = $task_id
                            UNION SELECT creator_id as user_id FROM tasks WHERE id = $task_id";
            $users_result = mysqli_query($conn, $users_query);

            while ($u = mysqli_fetch_assoc($users_result)) {
                if ($u['user_id'] != $user_id) {
                    $notif_sql = "INSERT INTO notifications (user_id, message, link) 
                                  VALUES ({$u['user_id']}, '$notif_message', '$notif_link')";
                    mysqli_query($conn, $notif_sql);
                }
            }

            header("Location: edit_task.php?id=$task_id");
            exit();
        } else {
            $error_message = "Komentar tidak boleh kosong.";
        }
    }
}

// Cek izin akses
$is_collaborator_query = "SELECT COUNT(*) AS count FROM task_collaborators WHERE task_id = $task_id AND user_id = $user_id";
$is_collaborator_result = mysqli_query($conn, $is_collaborator_query);
$is_collaborator_row = mysqli_fetch_assoc($is_collaborator_result);
$is_collaborator = $is_collaborator_row['count'] > 0;

if ($_SESSION['user_role'] !== 'admin' && $task['creator_id'] != $user_id && !$is_collaborator) {
    require 'templates/header.php';
    echo "<div class='content-wrapper'><p>Anda tidak memiliki izin untuk melihat atau mengedit tugas ini.</p></div>";
    require 'templates/footer.php';
    exit();
}

$collaborators_query = "SELECT u.id, u.name, u.profile_picture FROM users u JOIN task_collaborators tc ON u.id = tc.user_id WHERE tc.task_id = $task_id";
$collaborators_result = mysqli_query($conn, $collaborators_query);

$owner_query = "SELECT id, name, profile_picture FROM users WHERE id = " . $task['creator_id'];
$owner_result = mysqli_query($conn, $owner_query);
$owner = mysqli_fetch_assoc($owner_result);

require 'templates/header.php';
?>

<div class="content-wrapper">
    <header class="content-header-main">
        <h1>Edit Tugas</h1>
        <a href="dashboard.php" class="btn-secondary" style="background-color: var(--light-bg); color: var(--dark-text);"> &lsaquo; Kembali</a>
    </header>

    <?php if ($message_success): ?><div class="message-success"><?php echo $message_success; ?></div><?php endif; ?>
    <?php if ($error_message): ?><div class="message-error"><?php echo $error_message; ?></div><?php endif; ?>

    <div class="edit-task-layout">
        <div class="main-edit-form">
            <form action="edit_task.php?id=<?php echo $task_id; ?>" method="POST">
                <div class="form-group">
                    <label for="title">Judul Tugas</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($task['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="pending" <?php if($task['status'] == 'pending') echo 'selected'; ?>>Tertunda</option>
                        <option value="in_progress" <?php if($task['status'] == 'in_progress') echo 'selected'; ?>>Dikerjakan</option>
                        <option value="completed" <?php if($task['status'] == 'completed') echo 'selected'; ?>>Selesai</option>
                    </select>
                </div>
                <button type="submit" name="update_task" style="background-color:var(--primary-color); color:white;">Simpan Perubahan</button>
            </form>
        </div>

        <div class="collaborators-section">
            <h4>Dibagikan dengan</h4>
            <div class="collaborators-list">
                <div class="collaborator-item owner">
                    <img src="uploads/<?php echo htmlspecialchars($owner['profile_picture']); ?>" alt="Avatar">
                    <span><?php echo htmlspecialchars($owner['name']); ?> (Pemilik)</span>
                </div>
                <?php while ($collaborator = mysqli_fetch_assoc($collaborators_result)): ?>
                    <div class="collaborator-item">
                        <img src="uploads/<?php echo htmlspecialchars($collaborator['profile_picture']); ?>" alt="Avatar">
                        <span><?php echo htmlspecialchars($collaborator['name']); ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
            <hr>
            <h5>Undang Pengguna Baru</h5>
            <form action="edit_task.php?id=<?php echo $task_id; ?>" method="POST" class="invite-form">
                <div class="form-group">
                    <label for="email">Email Pengguna</label>
                    <input type="email" name="invite_email" placeholder="contoh@email.com" required>
                </div>
                <button type="submit" name="invite_user" style="background-color:var(--primary-color); color:white;">Undang</button>
            </form>
        </div>
    </div>

    <!-- KOMENTAR -->
    <hr>
    <div class="task-comments">
        <h4>Komentar</h4>

        <form action="edit_task.php?id=<?php echo $task_id; ?>" method="POST" style="margin-bottom: 20px;">
            <textarea name="comment" rows="3" style="width: 100%;" placeholder="Tulis komentar..." required></textarea><br>
            <button type="submit" name="submit_comment" style="margin-top: 5px; background-color: var(--primary-color); color: white;">Kirim Komentar</button>
        </form>

        <?php
        $comments_query = "SELECT c.comment, c.created_at, u.name FROM comments c
                           JOIN users u ON c.user_id = u.id
                           WHERE c.task_id = $task_id
                           ORDER BY c.created_at DESC";
        $comments_result = mysqli_query($conn, $comments_query);
        if (mysqli_num_rows($comments_result) == 0) {
            echo "<p>Belum ada komentar.</p>";
        } else {
            while ($comment = mysqli_fetch_assoc($comments_result)) {
                echo "<div style='border:1px solid #ddd; padding:10px; margin-bottom:10px; border-radius:5px;'>
                        <strong>" . htmlspecialchars($comment['name']) . "</strong><br>
                        " . nl2br(htmlspecialchars($comment['comment'])) . "<br>
                        <small style='color:#888;'>" . $comment['created_at'] . "</small>
                      </div>";
            }
        }
        ?>
    </div>
</div>

<?php require 'templates/footer.php'; ?>
