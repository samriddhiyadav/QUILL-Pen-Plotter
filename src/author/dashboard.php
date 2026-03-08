<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Start session and check author access
session_start();
if (!isLoggedIn() || !isAuthor()) {
    redirect(BASE_URL . '/src/auth/auth.php');
}

// Get current user ID
$userId = $_SESSION['user_id'];

// Get all posts by this author
$posts = getPostsByAuthor($pdo, $userId);

// Separate posts into drafts and published
$drafts = array_filter($posts, function($post) {
    return $post['status'] === 'draft';
});

$published = array_filter($posts, function($post) {
    return $post['status'] === 'published';
});

// Get post statistics
$stats = [
    'total_posts' => count($posts),
    'published_posts' => count($published),
    'draft_posts' => count($drafts),
];

// Handle post status changes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['publish_post'])) {
        $postId = (int)$_POST['post_id'];
        if (updatePostStatus($pdo, $postId, 'published')) {
            $_SESSION['success'] = "Post published successfully!";
            redirect(BASE_URL . '/src/author/dashboard.php');
        } else {
            $errors[] = "Failed to publish post";
        }
    } elseif (isset($_POST['unpublish_post'])) {
        $postId = (int)$_POST['post_id'];
        if (updatePostStatus($pdo, $postId, 'draft')) {
            $_SESSION['success'] = "Post moved to drafts!";
            redirect(BASE_URL . '/src/author/dashboard.php');
        } else {
            $errors[] = "Failed to unpublish post";
        }
    } elseif (isset($_POST['delete_post'])) {
        $postId = (int)$_POST['post_id'];
        if (deletePost($pdo, $postId)) {
            $_SESSION['success'] = "Post deleted successfully!";
            redirect(BASE_URL . '/src/author/dashboard.php');
        } else {
            $errors[] = "Failed to delete post";
        }
    }
}

// Get any success/error messages
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
unset($_SESSION['success']);

$errors = isset($_SESSION['error']) ? [$_SESSION['error']] : [];
unset($_SESSION['error']);

// Determine active tab from URL or default to published
$activeTab = isset($_GET['tab']) && in_array($_GET['tab'], ['published', 'drafts']) ? $_GET['tab'] : 'published';
?>

<?php
$postImages = [
    BASE_URL . '/assets/images/image1.jpg',
    BASE_URL . '/assets/images/image2.jpg',
    BASE_URL . '/assets/images/image3.jpg',
    BASE_URL . '/assets/images/image4.jpg',
];
$randomImage = $postImages[array_rand($postImages)];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Author Dashboard | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lora:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/styles.css" rel="stylesheet">
</head>

<body>
    <div class="author-dashboard">
        <!-- Header -->
        <header>
            <div class="container">
                <div class="header-content">
                    <a href="<?php echo BASE_URL; ?>" class="logo">
                        <i class="fas fa-feather-alt logo-icon"></i>
                        <span class="logo-text"><?php echo SITE_NAME; ?></span>
                    </a>
                    <nav class="main-nav">
                        <ul>
                            <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/author/dashboard.php" class="active">Dashboard</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/author/create.php">New Post</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/viewer/index.php">View Blog</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/auth/auth.php">Logout</a></li>
                        </ul>
                    </nav>
                    <button class="mobile-menu-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </header>

        <div class="container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1>Your Dashboard</h1>
                <a href="<?php echo BASE_URL; ?>/src/author/create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Post
                </a>
            </div>

            <!-- Success/Error Messages -->
            <?php if (!empty($success)): ?>
                <div class="alert-message success-message">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert-message error-message">
                    <?php if (count($errors) === 1): ?>
                        <?php echo $errors[0]; ?>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_posts']; ?></div>
                    <div class="stat-label">Total Posts</div>
                    <i class="fas fa-file-alt stat-icon"></i>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['published_posts']; ?></div>
                    <div class="stat-label">Published</div>
                    <i class="fas fa-check-circle stat-icon"></i>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['draft_posts']; ?></div>
                    <div class="stat-label">Drafts</div>
                    <i class="fas fa-edit stat-icon"></i>
                </div>
            </div>

            <!-- Posts Navigation Tabs -->
            <div class="posts-tabs">
                <a href="?tab=published" class="tab-link <?php echo $activeTab === 'published' ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle"></i> Published (<?php echo count($published); ?>)
                </a>
                <a href="?tab=drafts" class="tab-link <?php echo $activeTab === 'drafts' ? 'active' : ''; ?>">
                    <i class="fas fa-edit"></i> Drafts (<?php echo count($drafts); ?>)
                </a>
            </div>

            <!-- Posts Container -->
            <div class="posts-container">
                <?php if ($activeTab === 'published'): ?>
                    <!-- Published Posts -->
                    <div class="posts-list">
                        <h2>Published Posts</h2>
                        
                        <?php if (empty($published)): ?>
                            <div class="empty-state">
                                <i class="fas fa-file-alt"></i>
                                <p>You haven't published any posts yet.</p>
                                <a href="<?php echo BASE_URL; ?>/src/author/create.php" class="btn btn-primary">
                                    Create Your First Post
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="posts-grid">
                                <?php foreach ($published as $post): ?>
                                    <article class="post-card animate-on-scroll" data-post-id="<?php echo $post['id']; ?>">
                                        <div class="post-card-inner">
                                            <div class="post-card-image">
                                                <img src="<?php echo $randomImage; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                            </div>
                                            <div class="post-card-content">
                                                <div class="post-meta">
                                                    <span class="post-status published">
                                                        <?php echo ucfirst($post['status']); ?>
                                                    </span>
                                                    <span class="post-date"><?php echo formatDate($post['created_at']); ?></span>
                                                </div>
                                                <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                                <p class="post-excerpt"><?php echo generateExcerpt($post['content'], 25); ?></p>
                                                <div class="post-actions">
                                                    <div class="post-actions-left">
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                            <button type="submit" name="unpublish_post" class="btn btn-sm btn-secondary">
                                                                <i class="fas fa-eye-slash"></i> Unpublish
                                                            </button>
                                                        </form>
                                                        <a href="<?php echo BASE_URL; ?>/src/author/edit.php?id=<?php echo $post['id']; ?>" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                    </div>
                                                    <div class="post-actions-right">
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                            <button type="submit" name="delete_post" class="btn btn-sm btn-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this post?')">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Draft Posts -->
                    <div class="posts-list">
                        <h2>Drafts</h2>
                        
                        <?php if (empty($drafts)): ?>
                            <div class="empty-state">
                                <i class="fas fa-edit"></i>
                                <p>You don't have any drafts.</p>
                                <a href="<?php echo BASE_URL; ?>/src/author/create.php" class="btn btn-primary">
                                    Create New Draft
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="posts-grid">
                                <?php foreach ($drafts as $post): ?>
                                    <article class="post-card animate-on-scroll" data-post-id="<?php echo $post['id']; ?>">
                                        <div class="post-card-inner">
                                            <div class="post-card-image">
                                                <img src="<?php echo $randomImage; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                            </div>
                                            <div class="post-card-content">
                                                <div class="post-meta">
                                                    <span class="post-status draft">
                                                        <?php echo ucfirst($post['status']); ?>
                                                    </span>
                                                    <span class="post-date"><?php echo formatDate($post['created_at']); ?></span>
                                                </div>
                                                <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                                <p class="post-excerpt"><?php echo generateExcerpt($post['content'], 25); ?></p>
                                                <div class="post-actions">
                                                    <div class="post-actions-left">
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                            <button type="submit" name="publish_post" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye"></i> Publish
                                                            </button>
                                                        </form>
                                                        <a href="<?php echo BASE_URL; ?>/src/author/edit.php?id=<?php echo $post['id']; ?>" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                    </div>
                                                    <div class="post-actions-right">
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                            <button type="submit" name="delete_post" class="btn btn-sm btn-danger"
                                                                    onclick="return confirm('Are you sure you want to delete this draft?')">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.main-nav ul').classList.toggle('show');
        });

        // Confirm before deleting
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (this.querySelector('button[name="delete_post"]')) {
                    if (!confirm('Are you sure you want to delete this post?')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
</body>

</html>