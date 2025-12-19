<?php
$page_title = "Backup System";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

requireRole('admin');

$message = '';

if (isset($_POST['backup'])) {
    $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $backup_path = ROOT_PATH . '/backups/';
    
    if (!file_exists($backup_path)) {
        mkdir($backup_path, 0755, true);
    }
    
    $command = "mysqldump -u root thesis_archive_db > " . $backup_path . $backup_file;
    system($command, $output);
    
    if ($output === 0) {
        $message = "Backup created successfully: $backup_file";
        logActivity($conn, getUserId(), 'BACKUP_CREATE', null, null, "Created backup: $backup_file");
    } else {
        $message = "Backup failed!";
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">
    <h2><i class="fas fa-database"></i> Backup System</h2>
    <hr>
    
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5>Database Backup</h5>
        </div>
        <div class="card-body">
            <p>Create a backup of the entire database. This will save all data to a SQL file.</p>
            <form method="POST">
                <button type="submit" name="backup" class="btn btn-danger">
                    <i class="fas fa-database"></i> Create Backup Now
                </button>
            </form>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header">
            <h5>Previous Backups</h5>
        </div>
        <div class="card-body">
            <?php
            $backup_dir = ROOT_PATH . '/backups/';
            if (file_exists($backup_dir)) {
                $files = scandir($backup_dir);
                $backups = array_filter($files, function($file) {
                    return pathinfo($file, PATHINFO_EXTENSION) === 'sql';
                });
                
                if (count($backups) > 0): ?>
                    <ul class="list-group">
                        <?php foreach ($backups as $backup): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?php echo $backup; ?></span>
                                <a href="<?php echo BASE_URL; ?>backups/<?php echo $backup; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No backups found.</p>
                <?php endif;
            }
            ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>