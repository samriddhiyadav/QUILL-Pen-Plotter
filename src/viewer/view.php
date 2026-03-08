<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Start session
session_start();

// Check if post ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    showError('Invalid post ID', BASE_URL . '/src/viewer/index.php');
}

$postId = (int)$_GET['id'];

// Get the post from database
$post = getPostById($pdo, $postId);

// Check if post exists and is published (unless user is author/admin)
if (!$post) {
    showError('Post not found', BASE_URL . '/src/viewer/index.php');
}

if ($post['status'] !== 'published') {
    if (!isLoggedIn() || (!isPostOwner($pdo, $postId) && !isAdmin())) {
        showError('This post is not available', BASE_URL . '/src/viewer/index.php');
    }
}

// Get related posts (excluding current post)
$relatedPosts = getAllPosts($pdo, 3);
$relatedPosts = array_filter($relatedPosts, function($p) use ($postId) {
    return $p['id'] != $postId;
});
$relatedPosts = array_slice($relatedPosts, 0, 3);

// Get any success/error messages
$success = getFlashMessage('success');
$error = getFlashMessage('error');

// Get first initial of author's name for avatar
$authorInitial = strtoupper(substr($post['author_name'], 0, 1));
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
    <title><?php echo htmlspecialchars($post['title']); ?> | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lora:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/styles.css" rel="stylesheet">
    <style>
        .author-avatar-initial {
            width: 50px;
            height: 50px;
            border-radius: 45%;
            background-color: #C9A66B;;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: bold;
        }
    </style>
</head>

<body>
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
                        <li><a href="<?php echo BASE_URL; ?>/src/viewer/index.php">All Posts</a></li>
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

    <!-- Main Content -->
    <div class="container">
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

        <!-- Post Content -->
        <article class="post-view">
            <!-- Post Header -->
            <div class="post-header">
                <div class="post-meta">
                    <div class="author-info">
                        <div class="author-avatar">
                            <div class="author-avatar-initial"><?php echo $authorInitial; ?></div>
                        </div>
                        <div class="author-details">
                            <span class="author-name"><?php echo htmlspecialchars($post['author_name']); ?></span>
                            <span class="post-date"><?php echo formatDate($post['created_at']); ?></span>
                        </div>
                    </div>
                    <div class="post-stats">
                        <span><i class="far fa-clock"></i> <?php echo ceil(str_word_count(strip_tags($post['content'])) / 200); ?> min read</span>
                    </div>
                </div>

                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>

                <?php if (isPostOwner($pdo, $postId) || isAdmin()): ?>
                    <div class="post-actions">
                        <a href="<?php echo BASE_URL; ?>/src/author/edit.php?id=<?php echo $postId; ?>" 
                           class="btn btn-sm btn-secondary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Featured Image -->
            <?php if (!empty($post['featured_image'])): ?>
                <div class="post-image">
                    <img src="<?php echo $randomImage; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                </div>
            <?php endif; ?>

            <!-- Post Content -->
            <div class="post-content">
                <?php echo $post['content']; ?>
            </div>

            <!-- Post Footer -->
            <div class="post-footer">
                <!-- Tags -->
                <?php if (!empty($post['tags'])): ?>
                    <div class="post-tags">
                        <span class="tags-label">Tags:</span>
                        <?php 
                        $tags = explode(',', $post['tags']);
                        foreach ($tags as $tag): 
                        ?>
                            <a href="#" class="tag"><?php echo htmlspecialchars(trim($tag)); ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Social Sharing -->
                <div class="post-actions">
                    <div class="social-sharing">
                        <span class="share-label">Share:</span>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($post['title']); ?>&url=<?php echo urlencode(BASE_URL . '/src/viewer/view.php?id=' . $postId); ?>" 
                           class="social-share twitter" target="_blank">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(BASE_URL . '/src/viewer/view.php?id=' . $postId); ?>" 
                           class="social-share facebook" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(BASE_URL . '/src/viewer/view.php?id=' . $postId); ?>&title=<?php echo urlencode($post['title']); ?>" 
                           class="social-share linkedin" target="_blank">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>
        </article>

        <!-- Related Posts -->
        <?php if (!empty($relatedPosts)): ?>
            <section class="related-posts">
                <h2 class="related-title">You might also like</h2>
                <div class="related-grid">
                    <?php foreach ($relatedPosts as $relatedPost): 
                        $relatedAuthorInitial = strtoupper(substr($relatedPost['author_name'], 0, 1));
                    ?>
                        <div class="related-post">
                            <a href="<?php echo BASE_URL; ?>/src/viewer/view.php?id=<?php echo $relatedPost['id']; ?>" 
                               class="related-post-link">
                                <?php if (!empty($relatedPost['featured_image'])): ?>
                                    <div class="related-post-image">
                                        <img src="<?php echo $randomImage; ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="related-post-content">
                                    <h3 class="related-post-title"><?php echo htmlspecialchars($relatedPost['title']); ?></h3>
                                    <div class="related-post-meta">
                                        <div class="related-author-info">
                                            <div class="author-avatar-initial small"><?php echo $relatedAuthorInitial; ?></div>
                                            <span><?php echo htmlspecialchars($relatedPost['author_name']); ?></span>
                                        </div>
                                        <div class="related-post-stats">
                                            <span><?php echo formatDate($relatedPost['created_at']); ?></span>
                                            <span><?php echo ceil(str_word_count(strip_tags($relatedPost['content'])) / 200); ?> min read</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>About <?php echo SITE_NAME; ?></h3>
                    <p>A minimalist yet luxurious blogging platform where authors craft elegant posts and readers enjoy a premium experience.</p>
                </div>
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/src/viewer/index.php">All Posts</a></li>
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

    <script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.main-nav ul').classList.toggle('show');
        });

        // Handle post interactions (like, save, share)
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize post interactions
            initPostInteractions();
        });
    </script>
</body>

</html>