
<?php
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Hapus user
if (isset($_GET['delete_user']) && is_numeric($_GET['delete_user']) && $_GET['delete_user'] != $_SESSION['user_id']) {
    mysqli_query($conn, "DELETE FROM users WHERE id = {$_GET['delete_user']}");
    header("Location: admin_dashboard.php?status=user_deleted");
    exit();
}

// Hapus tugas
if (isset($_GET['delete_task']) && is_numeric($_GET['delete_task'])) {
    mysqli_query($conn, "DELETE FROM tasks WHERE id = {$_GET['delete_task']}");
    header("Location: admin_dashboard.php?status=task_deleted");
    exit();
}

// Ambil data
$users_result = mysqli_query($conn, "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
$tasks_result = mysqli_query($conn, "SELECT tasks.id, tasks.title, tasks.status, users.name AS creator_name 
                                     FROM tasks JOIN users ON tasks.creator_id = users.id 
                                     ORDER BY tasks.created_at DESC");

$total_users = mysqli_num_rows($users_result);
$total_tasks = mysqli_num_rows($tasks_result);

require 'templates/header.php';
?>

<div class="main-content">
    <!-- Header Admin -->
    <div class="content-header">
        <h3>Dashboard Admin</h3>
        <p>Halo, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>! Anda masuk sebagai <span class="role-badge role-admin">Admin</span></p>
    </div>

    <!-- Statistik Ringkas -->
    <div class="dashboard-row">
        <div class="dashboard-card">
            <h5>Total Pengguna</h5>
            <p style="font-size: 2rem; font-weight: bold;"><?php echo $total_users; ?> </p>
        </div>
        <div class="dashboard-card">
            <h5>Total Tugas</h5>
            <p style="font-size: 2rem; font-weight: bold;"><?php echo $total_tasks; ?> </p>
        </div>
    </div>

    <!-- Manajemen Pengguna -->
    <div class="admin-section">
        <h4>Manajemen Pengguna</h4>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Tanggal Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($users_result, 0); while ($user = mysqli_fetch_assoc($users_result)): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo htmlspecialchars($user['role']); ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                        <td class="actions">
                            <a href="admin_edit_user.php?id=<?php echo $user['id']; ?>" class="action-btn-sm btn-edit">Edit</a>
                            <a href="admin_dashboard.php?delete_user=<?php echo $user['id']; ?>" class="action-btn-sm btn-delete" onclick="return confirm('Menghapus pengguna juga akan menghapus semua tugasnya. Lanjutkan?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Manajemen Tugas -->
    <div class="admin-section mt-6">
        <h4> Manajemen Tugas Global</h4>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Status</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($task = mysqli_fetch_assoc($tasks_result)): ?>
                    <tr>
                        <td><?php echo $task['id']; ?></td>
                        <td><?php echo htmlspecialchars($task['title']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $task['status']; ?>">
                                <?php echo ucwords(str_replace('_', ' ', $task['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($task['creator_name']); ?></td>
                        <td class="actions">
                            <a href="admin_edit_task.php?id=<?php echo $task['id']; ?>" class="action-btn-sm btn-edit">Edit</a>
                            <a href="admin_dashboard.php?delete_task=<?php echo $task['id']; ?>" class="action-btn-sm btn-delete" onclick="return confirm('Yakin ingin menghapus tugas ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require 'templates/footer.php'; ?>