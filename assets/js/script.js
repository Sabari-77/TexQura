document.addEventListener("DOMContentLoaded", () => {
    // UI Polish: Auto-dismiss success/error alerts after 4 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = "opacity 0.5s ease, margin 0.5s ease, height 0.5s ease, padding 0.5s ease";
            alert.style.opacity = "0";
            alert.style.margin = "0";
            alert.style.padding = "0";
            alert.style.height = "0";
            alert.style.overflow = "hidden";
            setTimeout(() => alert.remove(), 500); 
        }, 4000);
    });

    // UI Polish: Prevent users from double-clicking submission buttons
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (this.classList.contains('is-submitting')) {
                e.preventDefault();
                return;
            }
            this.classList.add('is-submitting');
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
                btn.style.pointerEvents = "none";
                btn.style.opacity = "0.7";
            }
        });
    });
});