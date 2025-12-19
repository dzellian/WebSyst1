<?php
$page_title = "Home";
require_once 'config/config.php';
require_once 'config/session.php';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container mt-5">
    <div class="jumbotron text-center bg-light p-5 rounded">
        <h1 class="display-4"><i class="fas fa-book"></i> Thesis Archive Management System</h1>
        <p class="lead">Digital repository for academic research and thesis documents</p>
        <hr class="my-4">
        <p>Search, browse, and access approved thesis documents from our comprehensive archive.</p>
        <div class="mt-4">
            <a class="btn btn-primary btn-lg me-2" href="public/library.php" role="button">
                <i class="fas fa-library"></i> Browse Library
            </a>
            <a class="btn btn-success btn-lg" href="public/search.php" role="button">
                <i class="fas fa-search"></i> Search Thesis
            </a>
        </div>
    </div>
    
    <div class="row mt-5">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-upload fa-3x text-primary mb-3"></i>
                    <h5>For Students</h5>
                    <p>Upload and manage your thesis submissions</p>
                    <a href="auth/login.php" class="btn btn-primary">Login</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>For Advisers</h5>
                    <p>Review and approve student submissions</p>
                    <a href="auth/login.php" class="btn btn-success">Login</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-cog fa-3x text-warning mb-3"></i>
                    <h5>For Administrators</h5>
                    <p>Manage system and user accounts</p>
                    <a href="auth/login.php" class="btn btn-warning">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>