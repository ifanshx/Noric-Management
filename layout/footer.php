<footer class="main-footer text-center no-print" style="padding: 20px; color: #94a3b8; font-size: 12px;">
    <strong>Copyright &copy; <?php echo date('Y'); ?> NORIC RACING EXHAUST.</strong> All rights reserved.
</footer>

<script>
    function toggleSidebar() {
        const width = window.innerWidth;
        const body = document.body;

        if (width <= 768) {
            // Logic HP: Tambah class 'sidebar-open' untuk memunculkan sidebar
            body.classList.toggle('sidebar-open');
        } else {
            // Logic Desktop: Tambah class 'sidebar-collapsed' untuk menyembunyikan sidebar
            body.classList.toggle('sidebar-collapsed');
        }
    }

    // Auto-reset saat layar di-resize (misal putar HP)
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            document.body.classList.remove('sidebar-open');
        } else {
            document.body.classList.remove('sidebar-collapsed');
        }
    });
</script>

</body>
</html>