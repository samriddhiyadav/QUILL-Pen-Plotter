<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Start session and check author access
session_start();
if (!isLoggedIn() || !isAuthor()) {
    redirect(BASE_URL . '/src/auth/auth.php');
}

// Get post ID from URL
$postId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch the post
$post = getPostById($pdo, $postId);

// Check if post exists and belongs to current user
if (!$post || ($post['author_id'] != $_SESSION['user_id'] && !isAdmin())) {
    $_SESSION['error'] = "Post not found or you don't have permission to edit it";
    redirect(BASE_URL . '/src/author/dashboard.php');
}

$errors = [];
$postData = [
    'title' => $post['title'],
    'content' => $post['content'],
    'status' => $post['status']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $postData['title'] = trim($_POST['title'] ?? '');
    $postData['content'] = trim($_POST['content'] ?? '');
    $postData['status'] = $_POST['status'] ?? 'draft';

    // Validate input
    if (empty($postData['title'])) {
        $errors['title'] = 'Title is required';
    } elseif (strlen($postData['title']) > 255) {
        $errors['title'] = 'Title must be less than 255 characters';
    }

    if (empty($postData['content'])) {
        $errors['content'] = 'Content is required';
    } elseif (strlen($postData['content']) < 50) {
        $errors['content'] = 'Content must be at least 50 characters';
    }

    if (!in_array($postData['status'], ['draft', 'published'])) {
        $errors['status'] = 'Invalid status';
    }

    // If no errors, update the post
    if (empty($errors)) {
        if (updatePost($pdo, $postId, $postData)) {
            $_SESSION['success'] = "Post updated successfully!";
            redirect(BASE_URL . '/src/author/dashboard.php');
        } else {
            $errors[] = "Failed to update post. Please try again.";
        }
    }
}

// Get any success/error messages
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
unset($_SESSION['success']);

$errors = array_merge($errors, isset($_SESSION['error']) ? [$_SESSION['error']] : []);
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post | <?php echo SITE_NAME; ?></title>
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
                            <li><a href="<?php echo BASE_URL; ?>/src/author/dashboard.php">Dashboard</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/author/create.php">New Post</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/src/viewer/index.php">View Blog</a></li>
                            <li><a href="?logout=1">Logout</a></li>
                        </ul>
                    </nav>
                    <button class="mobile-menu-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </header>

        <div class="container">
            <div class="card">
                <div class="section-title">
                    <h2>Edit Post</h2>
                    <p class="subtitle">Refine your content</p>
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

                <!-- Post Form -->
                <form id="post-form" method="POST" class="post-form-container">
                    <div class="form-group <?php echo isset($errors['title']) ? 'error' : ''; ?>">
                        <label for="title">Post Title</label>
                        <input type="text" id="title" name="title" class="form-control"
                            value="<?php echo htmlspecialchars($postData['title']); ?>" required>
                        <?php if (isset($errors['title'])): ?>
                            <div class="error-message"><?php echo $errors['title']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group <?php echo isset($errors['content']) ? 'error' : ''; ?>">
                        <label for="content">Post Content</label>
                        <div id="editor" class="rich-text-editor" contenteditable="true">
                            <?php echo htmlspecialchars($postData['content']); ?>
                        </div>
                        <textarea id="content" name="content"
                            style="display:none;"><?php echo htmlspecialchars($postData['content']); ?></textarea>
                        <?php if (isset($errors['content'])): ?>
                            <div class="error-message"><?php echo $errors['content']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Post Status</label>
                        <div class="status-options">
                            <div class="status-option">
                                <input type="radio" id="status-draft" name="status" value="draft" <?php echo $postData['status'] === 'draft' ? 'checked' : ''; ?>>
                                <label for="status-draft">Draft</label>
                            </div>
                            <div class="status-option">
                                <input type="radio" id="status-published" name="status" value="published" <?php echo $postData['status'] === 'published' ? 'checked' : ''; ?>>
                                <label for="status-published">Publish</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="<?php echo BASE_URL; ?>/src/author/dashboard.php" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" name="save_post" class="btn btn-primary" style="background-color: black; color: #E8D8C4;">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
    <script>
        // Initialize rich text editor
        document.addEventListener('DOMContentLoaded', function () {
            const editor = document.getElementById('editor');
            const contentField = document.getElementById('content');

            // Update hidden textarea when editor content changes
            editor.addEventListener('input', function () {
                contentField.value = this.innerHTML;
            });

            // Also update on blur in case input event doesn't catch everything
            editor.addEventListener('blur', function () {
                contentField.value = this.innerHTML;
            });

            // Handle form submission to ensure content is properly set
            document.getElementById('post-form').addEventListener('submit', function () {
                contentField.value = editor.innerHTML;
                return true;
            });
        });
    </script>
</body>

</html>