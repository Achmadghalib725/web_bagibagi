
<?php
// notifications.php
require 'config.php';
require 'templates/header.php';

// Keamanan: Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// --- Logika Inti Halaman Notifikasi ---

// 1. Tandai semua notifikasi yang belum dibaca sebagai "sudah dibaca" (is_read = 1)
// Ini harus dijalankan SEBELUM kita mengambil data untuk ditampilkan.
$update_notif_query = "UPDATE notifications SET is_read = 1 WHERE user_id = $user_id AND is_read = 0";
mysqli_query($conn, $update_notif_query);


// 2. Ambil semua notifikasi untuk pengguna ini untuk ditampilkan
$notifications_query = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC";
$notifications_result = mysqli_query($conn, $notifications_query);

?>

<div class="content-wrapper">
    <header class="content-header-main">
        <h1>Notifikasi</h1>
        <p>Semua pembaruan dan undangan Anda akan muncul di sini.</p>
    </header>

    <div class="notification-list">
        <?php
        if (mysqli_num_rows($notifications_result) > 0):
            while ($notification = mysqli_fetch_assoc($notifications_result)):
        ?>
            <a href="<?php echo htmlspecialchars($notification['link']); ?>" class="notification-item">
                <div class="notification-icon">
                    <i data-feather="bell"></i>
                </div>
                <div class="notification-content">
                    <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                    <span class="notification-time"><?php echo date('d M Y, H:i', strtotime($notification['created_at'])); ?></span>
                </div>
            </a>
        <?php
            endwhile;
        else:
            // Tampilkan pesan jika tidak ada notifikasi sama sekali
            echo "<div class='no-tasks-message'>Tidak ada notifikasi untuk Anda saat ini.</div>";
        endif;
        ?>
    </div>
</div>

<?php
require 'templates/footer.php';

?>