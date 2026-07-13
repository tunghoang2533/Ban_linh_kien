// ── Sidebar Toggle ──────────────────────────────────────────
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!sidebar) return;
    sidebar.classList.toggle('show');
    overlay && overlay.classList.toggle('show');
    document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
}

function closeSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!sidebar) return;
    sidebar.classList.remove('show');
    overlay && overlay.classList.remove('show');
    document.body.style.overflow = '';
}

// Close sidebar on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeSidebar();
});

// ── On DOM Ready ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {

    // Close sidebar when clicking a nav link on mobile
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768) closeSidebar();
        });
    });

    // Show Add Product form
    const showAddBtn = document.getElementById('showAddProductButton');
    if (showAddBtn) {
        showAddBtn.addEventListener('click', function() {
            const section = document.getElementById('addProductSection');
            if (section) {
                section.classList.remove('hidden');
                setTimeout(function() {
                    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    const firstInput = section.querySelector('input[type="text"]');
                    if (firstInput) firstInput.focus();
                }, 50);
            }
        });
    }

    // Toggle edit rows
    document.querySelectorAll('.toggle-edit-button').forEach(button => {
        button.addEventListener('click', function(event) {
            const editId = this.dataset.editId;
            if (window.adminEditId && String(window.adminEditId) === editId) {
                event.preventDefault();
                window.location.href = '?page=products';
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Close modals on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    });

});

// ── Delete Confirmation ──────────────────────────────────────
function confirmDelete() {
    return confirm('Bạn chắc chắn muốn xóa mục này?\nHành động này không thể hoàn tác.');
}
