        </main>
    </div>

    <!-- Mobile Bottom App Navigation Bar (Always Visible on Smartphones) -->
    <nav class="mobile-bottom-nav d-lg-none" aria-label="Mobile Navigation">
        <a href="<?= $base_url ?>/dashboard.php" class="<?= ($current_page == 'dashboard.php' || $folder == 'TexQura') ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <span>Home</span>
        </a>
        <a href="<?= $base_url ?>/fabrics/index.php" class="<?= $folder == 'fabrics' ? 'active' : '' ?>">
            <i class="bi bi-layers-half"></i>
            <span>Fabrics</span>
        </a>
        <a href="<?= $base_url ?>/rolls/index.php" class="<?= $folder == 'rolls' ? 'active' : '' ?>">
            <i class="bi bi-record-circle"></i>
            <span>Rolls</span>
        </a>
        <a href="<?= $base_url ?>/complaints/index.php" class="<?= $folder == 'complaints' ? 'active' : '' ?>">
            <i class="bi bi-exclamation-octagon"></i>
            <span>Claims</span>
        </a>
        <a href="javascript:void(0)" onclick="toggleSidebar()">
            <i class="bi bi-grid"></i>
            <span>Menu</span>
        </a>
    </nav>

    <!-- Bootstrap 5.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar && backdrop) {
                sidebar.classList.toggle('active');
                backdrop.classList.toggle('active');
            }
        }

        // Auto-update topbar title based on page document title
        document.addEventListener('DOMContentLoaded', function() {
            const titleParts = document.title.split('-');
            const titleEl = document.getElementById('pageTitle');
            if (titleParts.length > 0 && titleEl) {
                const cleanTitle = titleParts[0].trim();
                if (cleanTitle !== 'TexQura') {
                    titleEl.innerText = cleanTitle;
                }
            }
        });
    </script>
</body>
</html>
