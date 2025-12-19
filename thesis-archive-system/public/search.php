<?php
$page_title = "Search Thesis";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

$results = [];
$search_query = '';

if (isset($_GET['q'])) {
    $search_query = sanitize($_GET['q']);
    
    $stmt = $conn->prepare("
        SELECT t.*, u.first_name, u.last_name, d.department_name, p.program_name
        FROM thesis t
        JOIN users u ON t.author_id = u.user_id
        JOIN departments d ON t.department_id = d.department_id
        JOIN programs p ON t.program_id = p.program_id
        WHERE t.status = 'approved' AND (
            t.title LIKE ? OR 
            t.abstract LIKE ? OR 
            t.keywords LIKE ? OR 
            CONCAT(u.first_name, ' ', u.last_name) LIKE ?
        )
        ORDER BY t.publication_year DESC
    ");
    $search_term = "%$search_query%";
    $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
    $results = $stmt->fetchAll();
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="content-wrapper">
    <div class="container mt-4 mb-5">
        <h2><i class="fas fa-search"></i> Search Thesis</h2>
        <hr>
        
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-primary text-white">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               name="q" 
                               class="form-control" 
                               placeholder="Search by title, author, keywords, or abstract..." 
                               value="<?php echo htmlspecialchars($search_query); ?>" 
                               required 
                               autofocus>
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                    <small class="form-text text-muted mt-2 d-block">
                        <i class="fas fa-info-circle"></i> 
                        Tip: Try searching for specific terms like "machine learning" or author names
                    </small>
                </form>
            </div>
        </div>
        
        <?php if ($search_query): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h5>
                <span class="badge bg-primary"><?php echo count($results); ?> found</span>
            </div>
            <hr>
            
            <?php if (count($results) > 0): ?>
                <?php foreach ($results as $thesis): ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="view-thesis.php?id=<?php echo $thesis['thesis_id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($thesis['title']); ?>
                                </a>
                            </h5>
                            <p class="text-muted mb-2">
                                <small>
                                    <i class="fas fa-user"></i> <strong>Author:</strong> <?php echo $thesis['first_name'] . ' ' . $thesis['last_name']; ?> | 
                                    <i class="fas fa-calendar"></i> <strong>Year:</strong> <?php echo $thesis['publication_year']; ?> | 
                                    <i class="fas fa-building"></i> <strong>Department:</strong> <?php echo $thesis['department_name']; ?> |
                                    <i class="fas fa-graduation-cap"></i> <strong>Program:</strong> <?php echo $thesis['program_name']; ?>
                                </small>
                            </p>
                            <?php if ($thesis['keywords']): ?>
                                <p class="mb-2">
                                    <small>
                                        <i class="fas fa-tags"></i> <strong>Keywords:</strong> 
                                        <?php
                                        $keywords = explode(',', $thesis['keywords']);
                                        foreach ($keywords as $keyword) {
                                            echo '<span class="badge bg-secondary me-1">' . trim($keyword) . '</span>';
                                        }
                                        ?>
                                    </small>
                                </p>
                            <?php endif; ?>
                            <p class="card-text"><?php echo substr($thesis['abstract'], 0, 250); ?>...</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="view-thesis.php?id=<?php echo $thesis['thesis_id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View Full Details
                                </a>
                                <div>
                                    <span class="badge bg-info">
                                        <i class="fas fa-eye"></i> <?php echo $thesis['views']; ?> views
                                    </span>
                                    <span class="badge bg-success">
                                        <i class="fas fa-download"></i> <?php echo $thesis['downloads']; ?> downloads
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>No results found.</strong> Try different keywords or check your spelling.
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5><i class="fas fa-lightbulb"></i> Search Tips:</h5>
                        <ul>
                            <li>Try broader terms (e.g., "data" instead of "data mining")</li>
                            <li>Check spelling of author names</li>
                            <li>Use single keywords instead of full phrases</li>
                            <li>Browse the <a href="library.php">full library</a> instead</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-search fa-5x text-muted mb-3"></i>
                <h4 class="text-muted">Start Your Search</h4>
                <p class="text-muted">Enter keywords, author names, or topics to find relevant thesis</p>
                <a href="library.php" class="btn btn-outline-primary mt-3">
                    <i class="fas fa-library"></i> Browse All Thesis
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>