// ============================================
// Dark Mode Engine
// ============================================
(function () {
    const DARK_KEY = 'taskTracker_darkMode';
    const body = document.body;
    const toggleBtn = document.getElementById('themeToggle');

    // Apply saved preference on page load
    function applyTheme(isDark) {
        if (isDark) {
            body.classList.add('dark-mode');
            if (toggleBtn) {
                toggleBtn.innerHTML = '<i class="bi bi-sun-fill"></i>';
                toggleBtn.title = 'Switch to Light Mode';
            }
        } else {
            body.classList.remove('dark-mode');
            if (toggleBtn) {
                toggleBtn.innerHTML = '<i class="bi bi-moon-fill"></i>';
                toggleBtn.title = 'Switch to Dark Mode';
            }
        }
    }

    // Load saved preference
    const saved = localStorage.getItem(DARK_KEY);
    applyTheme(saved === 'true');

    // Expose toggle function globally for onclick
    window.toggleTheme = function () {
        const isDark = body.classList.contains('dark-mode');
        localStorage.setItem(DARK_KEY, !isDark);
        applyTheme(!isDark);
    };
})();

// ============================================
// File Upload Drop Zone
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');

    if (dropZone && fileInput) {
        dropZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            dropZone.classList.add('border-primary');
        });
        dropZone.addEventListener('dragleave', function () {
            dropZone.classList.remove('border-primary');
        });
        dropZone.addEventListener('drop', function (e) {
            e.preventDefault();
            dropZone.classList.remove('border-primary');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                const name = e.dataTransfer.files[0].name;
                dropZone.querySelector('p').textContent = '📎 ' + name;
            }
        });
        fileInput.addEventListener('change', function () {
            if (fileInput.files.length > 0) {
                dropZone.querySelector('p').textContent = '📎 ' + fileInput.files[0].name;
            }
        });
    }

    // Auto-dismiss flash alerts after 4 seconds
    setTimeout(function () {
        document.querySelectorAll('.alert.alert-success, .alert.alert-danger').forEach(function (el) {
            el.classList.remove('show');
            el.classList.add('fade');
            setTimeout(() => el.remove(), 500);
        });
    }, 4000);

    // ============================================
    // Inline Edit: Title (double-click to edit)
    // ============================================
    document.querySelectorAll('.inline-title').forEach(function (el) {
        el.style.cursor = 'pointer';
        el.setAttribute('title', 'Double-click to edit');
        el.addEventListener('dblclick', function () {
            const taskId   = el.dataset.taskId;
            const original = el.textContent.trim();
            const input    = document.createElement('input');
            input.type      = 'text';
            input.value     = original;
            input.className = 'form-control form-control-sm d-inline';
            input.style.width = '220px';
            el.replaceWith(input);
            input.focus();

            function save() {
                const newTitle = input.value.trim();
                if (!newTitle || newTitle === original) { input.replaceWith(el); return; }
                fetch('/Task-Tracker/public/api/update-title/' + taskId, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'title=' + encodeURIComponent(newTitle)
                }).then(r => r.json()).then(data => {
                    if (data.success) { el.textContent = newTitle; }
                    input.replaceWith(el);
                });
            }
            input.addEventListener('blur', save);
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter')  save();
                if (e.key === 'Escape') input.replaceWith(el);
            });
        });
    });

    // ============================================
    // Inline Edit: AJAX Status Select
    // ============================================
    document.querySelectorAll('.ajax-status').forEach(function (select) {
        select.addEventListener('change', function () {
            const taskId = select.dataset.taskId;
            const status = select.value;
            const orig   = select.dataset.original || select.value;
            select.dataset.original = status;

            // Visual feedback
            select.style.opacity = '0.5';
            fetch('/Task-Tracker/public/api/update-status/' + taskId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'status=' + encodeURIComponent(status)
            }).then(r => r.json()).then(data => {
                select.style.opacity = '1';
                if (!data.success) { select.value = orig; }
            });
        });
    });
});