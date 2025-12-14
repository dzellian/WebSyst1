*,
*::before,
*::after {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --bg-body: #f3f4f6;
    --bg-card: #ffffff;
    --bg-header: #ffffff;
    --primary: #2563eb;
    --primary-soft: #dbeafe;
    --primary-dark: #1d4ed8;
    --secondary: #10b981;
    --secondary-soft: #d1fae5;
    --danger: #ef4444;
    --danger-soft: #fee2e2;
    --warning: #f59e0b;
    --warning-soft: #fffbeb;
    --text-main: #111827;
    --text-muted: #6b7280;
    --border: #e5e7eb;
    --radius-sm: 4px;
    --radius-md: 8px;
    --radius-lg: 12px;
    --shadow-soft: 0 10px 25px rgba(15, 23, 42, 0.08);
    --shadow-light: 0 4px 10px rgba(15, 23, 42, 0.05);
    --transition: 0.2s ease;
    --font-main: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

body {
    font-family: var(--font-main);
    background-color: var(--bg-body);
    color: var(--text-main);
    min-height: 100vh;
}

html {
    scroll-behavior: smooth;
}

a {
    color: var(--primary);
    text-decoration: none;
    transition: color var(--transition), background-color var(--transition);
}

a:hover {
    color: var(--primary-dark);
}

.container {
    max-width: 1160px;
    margin: 0 auto;
    padding: 20px 16px 40px;
}

header {
    background-color: var(--bg-header);
    border-bottom: 1px solid rgba(229, 231, 235, 0.8);
    box-shadow: 0 2px 12px rgba(15, 23, 42, 0.03);
    position: sticky;
    top: 0;
    z-index: 50;
}

header .container {
    padding-top: 10px;
    padding-bottom: 10px;
}

nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

nav h1 {
    font-size: 20px;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: var(--primary-dark);
}

nav > div {
    display: flex;
    align-items: center;
    gap: 10px;
}

nav a {
    position: relative;
    font-size: 14px;
    font-weight: 500;
    padding: 7px 12px;
    border-radius: 999px;
    color: var(--text-muted);
}

nav a:hover {
    color: var(--primary-dark);
    background-color: var(--primary-soft);
}

nav a::after {
    content: "";
    position: absolute;
    left: 12px;
    right: 12px;
    bottom: 4px;
    height: 2px;
    border-radius: 999px;
    background-color: var(--primary);
    transform: scaleX(0);
    transform-origin: center;
    transition: transform var(--transition);
}

nav a:hover::after {
    transform: scaleX(1);
}

h1, h2, h3, h4 {
    font-weight: 600;
    color: var(--text-main);
}

h2 {
    font-size: 22px;
    margin-bottom: 12px;
}

h3 {
    font-size: 18px;
    margin-bottom: 8px;
}

.card {
    background-color: var(--bg-card);
    border-radius: var(--radius-lg);
    padding: 18px 18px 16px;
    box-shadow: var(--shadow-soft);
    margin-bottom: 18px;
    border: 1px solid rgba(229, 231, 235, 0.7);
}

.card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.form-group {
    margin-bottom: 12px;
}

label {
    display: block;
    margin-bottom: 5px;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-muted);
}

input[type="text"],
input[type="email"],
input[type="password"],
input[type="file"],
select,
textarea {
    width: 100%;
    padding: 9px 10px;
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
    background-color: #f9fafb;
    font-size: 14px;
    color: var(--text-main);
    transition: border-color var(--transition), box-shadow var(--transition), background-color var(--transition);
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: var(--primary);
    background-color: #ffffff;
    box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.15);
}

input[type="file"] {
    padding: 6px 6px;
    background-color: #ffffff;
}

button,
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    background: var(--primary);
    color: #ffffff;
    box-shadow: var(--shadow-light);
    transition: background-color var(--transition), transform var(--transition), box-shadow var(--transition);
    white-space: nowrap;
}

button:hover,
.btn:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 7px 18px rgba(15, 23, 42, 0.18);
}

button:active,
.btn:active {
    transform: translateY(0);
    box-shadow: var(--shadow-light);
}

.btn-danger {
    background: var(--danger);
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-secondary {
    background: var(--secondary);
}

.btn-secondary:hover {
    background: #059669;
}

.btn-group {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.btn-group a,
.btn-group button {
    flex: 1;
    font-size: 12px;
    padding: 6px 10px;
}

.alert {
    padding: 10px 12px;
    border-radius: var(--radius-md);
    margin-bottom: 12px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
    border: 1px solid transparent;
}

.alert-success {
    background: var(--secondary-soft);
    color: #047857;
    border-color: #6ee7b7;
}

.alert-danger {
    background: var(--danger-soft);
    color: #b91c1c;
    border-color: #fecaca;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 13px;
}

table thead th {
    background: #f3f4f6;
    color: #4b5563;
    padding: 9px 8px;
    text-align: left;
    font-weight: 600;
    border-bottom: 1px solid var(--border);
}

table tbody td {
    padding: 8px 8px;
    border-bottom: 1px solid #e5e7eb;
}

table tbody tr:nth-child(even) {
    background: #f9fafb;
}

table tbody tr:hover {
    background: #e5edff;
}

.badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    color: #ffffff;
}

.badge-success { background: var(--secondary); }
.badge-warning { background: var(--warning); }
.badge-danger  { background: var(--danger); }
.badge-info    { background: var(--primary); }

.profile-pic {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: var(--shadow-light);
    border: 2px solid #e5e7eb;
    background-color: #f9fafb;
}

.signature-img {
    max-width: 240px;
    height: auto;
    border-radius: var(--radius-sm);
    border: 1px solid var(--border);
    background: #f9fafb;
}

.grid {
    display: grid;
    gap: 16px;
}

.grid-2 {
    display: grid;
    grid-template-columns: 2fr 1.2fr;
    gap: 18px;
}

.grid-3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
}

.text-center { text-align: center; }
.text-right  { text-align: right; }
.text-muted  { color: var(--text-muted); }

.mt-1 { margin-top: 4px; }
.mt-2 { margin-top: 8px; }
.mt-3 { margin-top: 12px; }
.mt-4 { margin-top: 16px; }

.mb-1 { margin-bottom: 4px; }
.mb-2 { margin-bottom: 8px; }
.mb-3 { margin-bottom: 12px; }
mb-4 { margin-bottom: 16px; }

.helper-text {
    font-size: 12px;
    color: var(--text-muted);
}

.auth-wrapper {
    max-width: 420px;
    margin: 60px auto;
}

.auth-card {
    background: var(--bg-card);
    border-radius: var(--radius-lg);
    padding: 22px 22px 20px;
    box-shadow: var(--shadow-soft);
    border: 1px solid rgba(229, 231, 235, 0.9);
}

footer {
    font-size: 13px;
    color: var(--text-muted);
}

@media (max-width: 768px) {
    nav {
        flex-direction: column;
        align-items: flex-start;
    }

    nav > div {
        width: 100%;
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .container {
        padding-left: 14px;
        padding-right: 14px;
    }

    .grid-2,
    .grid-3 {
        grid-template-columns: 1fr;
    }

    .profile-pic {
        width: 120px;
        height: 120px;
    }
}