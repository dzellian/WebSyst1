</div> <!-- Close content-wrapper if opened in header -->

<footer class="bg-dark text-white py-4 mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5><i class="fas fa-book"></i> <?php echo SITE_NAME; ?></h5>
                <p class="small">Digital repository for academic research and thesis documents.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h6>Quick Links</h6>
                <ul class="list-unstyled small">
                    <li><a href="<?php echo BASE_URL; ?>public/library.php" class="text-white-50">Browse Library</a></li>
                    <li><a href="<?php echo BASE_URL; ?>public/search.php" class="text-white-50">Search Thesis</a></li>
                    <li><a href="<?php echo BASE_URL; ?>auth/login.php" class="text-white-50">Login</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h6>Contact</h6>
                <p class="small mb-1">
                    <i class="fas fa-envelope"></i> support@thesis.com
                </p>
                <p class="small">
                    <i class="fas fa-phone"></i> +1 234 567 8900
                </p>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="text-center small">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <p class="mb-0">
                <a href="#" class="text-white-50 me-2">Privacy Policy</a> | 
                <a href="#" class="text-white-50 ms-2">Terms of Service</a>
            </p>
        </div>
    </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Custom JS -->
<script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>

<!-- Auto-hide alerts -->
<script>
$(document).ready(function() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>

</body>
</html>