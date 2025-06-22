<footer class="app-footer">
        <p>&copy; <?php echo date('Y'); ?> DoTask. Dibuat Oleh Kelompok 6</p>
    </footer>
    <script>
        feather.replace();
        const profileToggleBtn = document.getElementById('profile-toggle-btn');
        if (profileToggleBtn) {
            const profileDropdownMenu = document.getElementById('profile-dropdown-menu');
            profileToggleBtn.addEventListener('click', function(e) { e.stopPropagation(); profileDropdownMenu.classList.toggle('show'); });
            window.addEventListener('click', function(e) { if (!profileToggleBtn.contains(e.target)) { profileDropdownMenu.classList.remove('show'); } });
        }
    </script>
</body>
</html>