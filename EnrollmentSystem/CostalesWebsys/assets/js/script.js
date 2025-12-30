// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        closeModal(e.target.id);
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const activeModal = document.querySelector('.modal.active');
        if (activeModal) {
            closeModal(activeModal.id);
        }
    }
});

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Confirm delete actions
    const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });
    
    // Mobile sidebar toggle
    const createSidebarToggle = () => {
        if (window.innerWidth <= 768) {
            const navbar = document.querySelector('.navbar .container');
            const sidebar = document.querySelector('.sidebar');
            
            if (navbar && sidebar && !document.getElementById('sidebarToggle')) {
                const toggleBtn = document.createElement('button');
                toggleBtn.id = 'sidebarToggle';
                toggleBtn.className = 'btn btn-outline btn-sm';
                toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
                toggleBtn.style.marginRight = '10px';
                
                toggleBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                });
                
                navbar.insertBefore(toggleBtn, navbar.firstChild);
            }
        }
    };
    
    createSidebarToggle();
    window.addEventListener('resize', createSidebarToggle);
});

// File upload preview
function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
        };
        reader.readAsDataURL(file);
    }
}

// Grade validation
document.addEventListener('DOMContentLoaded', function() {
    const gradeInputs = document.querySelectorAll('input[type="number"][name*="grade"]');
    
    gradeInputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value < 1.0 || value > 5.0) {
                this.setCustomValidity('Grade must be between 1.0 and 5.0');
            } else {
                this.setCustomValidity('');
            }
        });
    });
});

// Search functionality (optional enhancement)
function initializeSearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    
    if (input && table) {
        input.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            }
        });
    }
}

// Print functionality
function printTable() {
    window.print();
}

// Export to CSV (simple implementation)
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = Array.from(cols).map(col => `"${col.textContent}"`);
        csv.push(rowData.join(','));
    });
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename || 'export.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Prerequisite viewer
function viewPrerequisites(subjectId) {
    // This would open a modal showing all prerequisites
    // Implementation depends on your specific needs
    console.log('View prerequisites for subject:', subjectId);
}

// Toast notifications (optional)
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `<i class="fas fa-info-circle"></i> ${message}`;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transition = 'opacity 0.5s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

console.log('Enrollment System - Ready');