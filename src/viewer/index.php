<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Start session
session_start();

// Get all published posts
$posts = getAllPosts($pdo);

// Handle search functionality
$searchQuery = isset($_GET['search']) ? sanitize($_GET['search']) : '';
if ($searchQuery) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, u.name as author_name, 
                      GROUP_CONCAT(t.name) as tags,
                      (SELECT image_url FROM images WHERE post_id = p.id AND is_featured = TRUE LIMIT 1) as featured_image
                      FROM posts p 
                      JOIN users u ON p.author_id = u.id
                      LEFT JOIN post_tags pt ON p.id = pt.post_id
                      LEFT JOIN tags t ON pt.tag_id = t.id
                      WHERE p.status = 'published' 
                      AND (p.title LIKE :search OR p.content LIKE :search OR u.name LIKE :search OR t.name LIKE :search)
                      GROUP BY p.id
                      ORDER BY p.created_at DESC");
        $searchParam = "%$searchQuery%";
        $stmt->bindParam(':search', $searchParam);
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $posts = [];
    }
}

// Get any success/error messages
$success = getFlashMessage('success');
$error = getFlashMessage('error');
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
    <title>Blog | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lora:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/styles.css" rel="stylesheet">
</head>

<body>
    <div class="viewer-index">
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
                            <li><a href="<?php echo BASE_URL; ?>" class="active">Home</a></li>
                            <?php if (isLoggedIn()): ?>
                                <?php if (isAuthor()): ?>
                                    <li><a href="<?php echo BASE_URL; ?>/src/author/dashboard.php">Author Dashboard</a></li>
                                <?php endif; ?>
                                <?php if (isAdmin()): ?>
                                    <li><a href="<?php echo BASE_URL; ?>/src/admin/dashboard.php">Admin Dashboard</a></li>
                                <?php endif; ?>
                                <li><a href="<?php echo BASE_URL; ?>/src/auth/auth.php">Logout</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo BASE_URL; ?>/src/auth/login.php">Login</a></li>
                                <li><a href="<?php echo BASE_URL; ?>/src/auth/register.php">Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <button class="mobile-menu-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <h1 class="hero-title">Welcome to <?php echo SITE_NAME; ?></h1>
                <p class="hero-subtitle">A minimalist yet luxurious blogging platform for elegant writing</p>

                <!-- Search Form -->
                <form method="GET" class="search-form">
                    <div class="search-input-group">
                        <input type="text" name="search" placeholder="Search posts..."
                            value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Main Content -->
        <div class="container">
            <!-- Featured Posts -->
            <section class="featured-posts">
                <div class="section-title">
                    <h2><?php echo $searchQuery ? "Search Results" : "Featured Posts"; ?></h2>
                    <p class="subtitle">
                        <?php echo $searchQuery
                            ? "Posts matching your search: \"$searchQuery\""
                            : "Discover our latest and most popular articles"; ?>
                    </p>
                </div>

                <!-- Success/Error Messages -->
                <?php if ($success): ?>
                    <div class="alert-message success-message">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert-message error-message">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($searchQuery): ?>
                    <div class="search-results-header">
                        <h3><?php echo count($posts); ?> results found</h3>
                        <a href="<?php echo BASE_URL; ?>/src/viewer/index.php" class="clear-search">
                            <i class="fas fa-times"></i> Clear search
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (empty($posts)): ?>
                    <div class="no-results">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <h3>No posts found</h3>
                        <p><?php echo $searchQuery ? "Try a different search term" : "Check back later for new content"; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="posts-grid">
                        <?php foreach ($posts as $post): ?>
                            <article class="post-card animate-on-scroll" data-post-id="<?php echo $post['id']; ?>">
                                <div class="post-card-inner">
                                    <div class="post-card-image">
                                        <img src="<?php echo $randomImage; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                    </div>
                                    <div class="post-card-content">
                                        <div class="post-meta">
                                            <span class="post-author">By
                                                <?php echo htmlspecialchars($post['author_name']); ?></span>
                                            <span class="post-date"><?php echo formatDate($post['created_at']); ?></span>
                                        </div>
                                        <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                                        <p class="post-excerpt"><?php echo generateExcerpt($post['content'], 25); ?></p>
                                        <div class="post-actions">
                                            <a href="<?php echo BASE_URL; ?>/src/viewer/view.php?id=<?php echo $post['id']; ?>"
                                                class="read-more">Read More</a>
                                            <span class="reading-time">
                                                <?php echo ceil(str_word_count(strip_tags($post['content'])) / 200); ?> min read
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- Footer -->
        <footer>
            <div class="container">
                <div class="footer-content">
                    <div class="footer-column">
                        <h3>About <?php echo SITE_NAME; ?></h3>
                        <p>A minimalist yet luxurious blogging platform where authors craft elegant posts and readers
                            enjoy a premium experience.</p>
                    </div>
                    <div class="footer-column">
                        <h3>Quick Links</h3>
                        <ul>
                            <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/posts/index.php">All Posts</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/auth/login.php">Login</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/auth/register.php">Register</a></li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <h3>Connect</h3>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-facebook"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-pinterest"></i></a>
                        </div>
                    </div>
                </div>
                <div class="copyright">
                    &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.
                </div>
            </div>
        </footer>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function () {
            document.querySelector('.main-nav ul').classList.toggle('show');
        });

        // Animate post cards on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate-on-scroll').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>

</html>