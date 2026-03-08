<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Start session and check admin access
session_start();
if (!isLoggedIn() || !isAdmin()) {
    redirect(BASE_URL . '/src/auth/auth.php');
}

// Handle user role updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $userId = (int) $_POST['user_id'];
    $newRole = $_POST['new_role'];

    if (updateUserRole($pdo, $userId, $newRole)) {
        $_SESSION['success'] = "User role updated successfully";
        // Update the user's role in session if it's the current user
        if ($_SESSION['user_id'] == $userId) {
            $_SESSION['role'] = $newRole;
        }
        redirect(BASE_URL . '/src/admin/dashboard.php');
    } else {
        $errors[] = "Failed to update user role";
    }
}

// Handle post status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_post_status'])) {
    $postId = (int) $_POST['post_id'];
    $newStatus = $_POST['new_status'];

    if (updatePostStatus($pdo, $postId, $newStatus)) {
        $_SESSION['success'] = "Post status updated successfully";
        redirect(BASE_URL . '/src/admin/dashboard.php');
    } else {
        $errors[] = "Failed to update post status";
    }
}

// Handle post deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    $postId = (int) $_POST['post_id'];

    if (deletePost($pdo, $postId)) {
        $_SESSION['success'] = "Post deleted successfully";
        redirect(BASE_URL . '/src/admin/dashboard.php');
    } else {
        $errors[] = "Failed to delete post";
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = (int) $_POST['user_id'];

    // Prevent admin from deleting themselves
    if ($_SESSION['user_id'] == $userId) {
        $errors[] = "You cannot delete your own account";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['success'] = "User deleted successfully";
                redirect(BASE_URL . '/src/admin/dashboard.php');
            } else {
                $errors[] = "Failed to delete user";
            }
        } catch (PDOException $e) {
            $errors[] = "Error deleting user: " . $e->getMessage();
        }
    }
}

// Search and filter functionality for users
$userSearchQuery = isset($_GET['user_search']) ? sanitize($_GET['user_search']) : '';
$userRoleFilter = isset($_GET['user_role']) ? sanitize($_GET['user_role']) : '';

// Search and filter functionality for posts
$postSearchQuery = isset($_GET['post_search']) ? sanitize($_GET['post_search']) : '';
$postStatusFilter = isset($_GET['post_status']) ? sanitize($_GET['post_status']) : '';

// Sorting functionality
$sortField = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'id';
$sortDirection = isset($_GET['dir']) ? sanitize($_GET['dir']) : 'desc';

// Validate sort field
$validSortFields = ['id', 'title', 'author_name', 'status', 'created_at'];
if (!in_array($sortField, $validSortFields)) {
    $sortField = 'id';
}

// Toggle sort direction
$sortDirection = $sortDirection === 'asc' ? 'asc' : 'desc';
$nextSortDirection = $sortDirection === 'asc' ? 'desc' : 'asc';

// Get all users with search and filter
$users = getAllUsersWithFilters($pdo, $userSearchQuery, $userRoleFilter, $sortField, $sortDirection);

// Get all posts with search and filter
$posts = getAllPostsWithFilters($pdo, $postSearchQuery, $postStatusFilter, $sortField, $sortDirection);

// Get statistics
$stats = [
    'total_users' => count($users),
    'total_posts' => count($posts),
    'published_posts' => array_reduce($posts, function ($carry, $post) {
        return $carry + ($post['status'] === 'published' ? 1 : 0);
    }, 0),
    'draft_posts' => array_reduce($posts, function ($carry, $post) {
        return $carry + ($post['status'] === 'draft' ? 1 : 0);
    }, 0),
];

// Get any success/error messages
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
unset($_SESSION['success']);

$errors = isset($_SESSION['error']) ? [$_SESSION['error']] : [];
unset($_SESSION['error']);

// Helper functions for filtering
function getAllUsersWithFilters($pdo, $searchQuery = '', $roleFilter = '', $sortField = 'id', $sortDirection = 'desc')
{
    try {
        $sql = "SELECT * FROM users WHERE 1=1";

        if (!empty($searchQuery)) {
            $sql .= " AND (name LIKE :search OR email LIKE :search)";
        }

        if (!empty($roleFilter)) {
            $sql .= " AND role = :role";
        }

        // Validate sort field
        $validSortFields = ['id', 'name', 'email', 'role', 'created_at'];
        $sortField = in_array($sortField, $validSortFields) ? $sortField : 'id';
        $sortDirection = $sortDirection === 'asc' ? 'ASC' : 'DESC';

        $sql .= " ORDER BY $sortField $sortDirection";

        $stmt = $pdo->prepare($sql);

        if (!empty($searchQuery)) {
            $searchParam = "%$searchQuery%";
            $stmt->bindParam(':search', $searchParam);
        }

        if (!empty($roleFilter)) {
            $stmt->bindParam(':role', $roleFilter);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function getAllPostsWithFilters($pdo, $searchQuery = '', $statusFilter = '', $sortField = 'id', $sortDirection = 'desc')
{
    try {
        $sql = "SELECT p.*, u.name as author_name 
                FROM posts p 
                JOIN users u ON p.author_id = u.id
                WHERE 1=1";

        if (!empty($searchQuery)) {
            $sql .= " AND (p.title LIKE :search OR p.content LIKE :search OR u.name LIKE :search)";
        }

        if (!empty($statusFilter)) {
            $sql .= " AND p.status = :status";
        }

        // Validate sort field
        $validSortFields = ['id', 'title', 'author_name', 'status', 'created_at'];
        $sortField = in_array($sortField, $validSortFields) ? $sortField : 'id';
        $sortDirection = $sortDirection === 'asc' ? 'ASC' : 'DESC';

        // Handle special cases for sorting
        if ($sortField === 'author') {
            $sortField = 'author_name';
        }

        $sql .= " ORDER BY $sortField $sortDirection";

        $stmt = $pdo->prepare($sql);

        if (!empty($searchQuery)) {
            $searchParam = "%$searchQuery%";
            $stmt->bindParam(':search', $searchParam);
        }

        if (!empty($statusFilter)) {
            $stmt->bindParam(':status', $statusFilter);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lora:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/styles.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="admin-dashboard">
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
                            <li><a href="<?php echo BASE_URL; ?>/src/admin/dashboard.php" class="active">Dashboard</a>
                            </li>
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
            <div class="admin-header">
                <h1>Admin Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?></p>
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
                    <div class="stat-value"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Total Users</div>
                    <i class="fas fa-users stat-icon"></i>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['total_posts']; ?></div>
                    <div class="stat-label">Total Posts</div>
                    <i class="fas fa-file-alt stat-icon"></i>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['published_posts']; ?></div>
                    <div class="stat-label">Published Posts</div>
                    <i class="fas fa-check-circle stat-icon"></i>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['draft_posts']; ?></div>
                    <div class="stat-label">Draft Posts</div>
                    <i class="fas fa-edit stat-icon"></i>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-widgets">
                <div class="chart-widget">
                    <div class="widget-header">
                        <h3><i class="fas fa-users"></i> Users by Role</h3>
                    </div>
                    <div class="widget-body">
                        <canvas id="usersByRoleChart"
                            data-labels='<?php echo json_encode(['Viewers', 'Authors', 'Admins']); ?>' data-data='<?php echo json_encode([
                                     count(array_filter($users, fn($u) => $u['role'] === 'viewer')),
                                     count(array_filter($users, fn($u) => $u['role'] === 'author')),
                                     count(array_filter($users, fn($u) => $u['role'] === 'admin'))
                                 ]); ?>'></canvas>
                    </div>
                </div>

                <div class="chart-widget">
                    <div class="widget-header">
                        <h3><i class="fas fa-file-alt"></i> Posts by Status</h3>
                    </div>
                    <div class="widget-body">
                        <canvas id="postsByStatusChart"
                            data-labels='<?php echo json_encode(['Published', 'Draft']); ?>' data-data='<?php echo json_encode([
                                    $stats['published_posts'],
                                    $stats['draft_posts']
                                ]); ?>'></canvas>
                    </div>
                </div>
            </div>

            <!-- User Management Section -->
            <div class="user-management">
                <div class="section-header">
                    <h2><i class="fas fa-users"></i> User Management</h2>
                    <div class="actions">
                        <form method="GET" class="search-form">
                            <div class="form-group">
                                <input type="text" name="user_search" placeholder="Search users..." class="form-control"
                                    value="<?php echo htmlspecialchars($userSearchQuery); ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </div>
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortField); ?>">
                            <input type="hidden" name="dir" value="<?php echo htmlspecialchars($sortDirection); ?>">
                        </form>
                        <div class="filter-group">
                            <form method="GET" class="filter-form">
                                <select name="user_role" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Roles</option>
                                    <option value="viewer" <?php echo $userRoleFilter === 'viewer' ? 'selected' : ''; ?>>
                                        Viewer</option>
                                    <option value="author" <?php echo $userRoleFilter === 'author' ? 'selected' : ''; ?>>
                                        Author</option>
                                    <option value="admin" <?php echo $userRoleFilter === 'admin' ? 'selected' : ''; ?>>Admin
                                    </option>
                                </select>
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortField); ?>">
                                <input type="hidden" name="dir" value="<?php echo htmlspecialchars($sortDirection); ?>">
                                <input type="hidden" name="post_search" value="<?php echo htmlspecialchars($postSearchQuery); ?>">
                                <input type="hidden" name="post_status" value="<?php echo htmlspecialchars($postStatusFilter); ?>">
                            </form>
                        </div>
                    </div>
                </div>

                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th><a
                                        href="?sort=id&dir=<?php echo $sortField === 'id' ? $nextSortDirection : 'desc'; ?>&user_search=<?php echo urlencode($userSearchQuery); ?>&user_role=<?php echo urlencode($userRoleFilter); ?>&post_search=<?php echo urlencode($postSearchQuery); ?>&post_status=<?php echo urlencode($postStatusFilter); ?>">ID
                                        <?php if ($sortField === 'id'): ?>
                                            <i
                                                class="fas fa-sort-<?php echo $sortDirection === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a></th>
                                <th><a
                                        href="?sort=name&dir=<?php echo $sortField === 'name' ? $nextSortDirection : 'desc'; ?>&user_search=<?php echo urlencode($userSearchQuery); ?>&user_role=<?php echo urlencode($userRoleFilter); ?>&post_search=<?php echo urlencode($postSearchQuery); ?>&post_status=<?php echo urlencode($postStatusFilter); ?>">Name
                                        <?php if ($sortField === 'name'): ?>
                                            <i
                                                class="fas fa-sort-<?php echo $sortDirection === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a></th>
                                <th><a
                                        href="?sort=email&dir=<?php echo $sortField === 'email' ? $nextSortDirection : 'desc'; ?>&user_search=<?php echo urlencode($userSearchQuery); ?>&user_role=<?php echo urlencode($userRoleFilter); ?>&post_search=<?php echo urlencode($postSearchQuery); ?>&post_status=<?php echo urlencode($postStatusFilter); ?>">Email
                                        <?php if ($sortField === 'email'): ?>
                                            <i
                                                class="fas fa-sort-<?php echo $sortDirection === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a></th>
                                <th><a
                                        href="?sort=role&dir=<?php echo $sortField === 'role' ? $nextSortDirection : 'desc'; ?>&user_search=<?php echo urlencode($userSearchQuery); ?>&user_role=<?php echo urlencode($userRoleFilter); ?>&post_search=<?php echo urlencode($postSearchQuery); ?>&post_status=<?php echo urlencode($postStatusFilter); ?>">Role
                                        <?php if ($sortField === 'role'): ?>
                                            <i
                                                class="fas fa-sort-<?php echo $sortDirection === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a></th>
                                <th><a
                                        href="?sort=created_at&dir=<?php echo $sortField === 'created_at' ? $nextSortDirection : 'desc'; ?>&user_search=<?php echo urlencode($userSearchQuery); ?>&user_role=<?php echo urlencode($userRoleFilter); ?>&post_search=<?php echo urlencode($postSearchQuery); ?>&post_status=<?php echo urlencode($postStatusFilter); ?>">Joined
                                        <?php if ($sortField === 'created_at'): ?>
                                            <i
                                                class="fas fa-sort-<?php echo $sortDirection === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="6" class="no-results">No users found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <form method="POST" class="role-form">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <select name="new_role" class="form-control" onchange="this.form.submit()">
                                                    <option value="viewer" <?php echo $user['role'] === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                                                    <option value="author" <?php echo $user['role'] === 'author' ? 'selected' : ''; ?>>Author</option>
                                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                                <input type="hidden" name="update_role" value="1">
                                            </form>
                                        </td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this user?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Post Management Section -->
            <div class="user-management">
                <div class="section-header">
                    <h2><i class="fas fa-file-alt"></i> Post Management</h2>
                    <div class="actions">
                        <form method="GET" class="search-form">
                            <div class="form-group">
                                <input type="text" name="post_search" placeholder="Search posts..." class="form-control"
                                    value="<?php echo htmlspecialchars($postSearchQuery); ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </div>
                            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortField); ?>">
                            <input type="hidden" name="dir" value="<?php echo htmlspecialchars($sortDirection); ?>">
                        </form>
                        <div class="filter-group">
                            <form method="GET" class="filter-form">
                                <select name="post_status" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="published" <?php echo $postStatusFilter === 'published' ? 'selected' : ''; ?>>
                                        Published</option>
                                    <option value="draft" <?php echo $postStatusFilter === 'draft' ? 'selected' : ''; ?>>Drafts
                                    </option>
                                </select>
                                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortField); ?>">
                                <input type="hidden" name="dir" value="<?php echo htmlspecialchars($sortDirection); ?>">
                                <input type="hidden" name="user_search" value="<?php echo htmlspecialchars($userSearchQuery); ?>">
                                <input type="hidden" name="user_role" value="<?php echo htmlspecialchars($userRoleFilter); ?>">
                            </form>
                        </div>
                    </div>
                </div>

                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th><a
                                        href="?sort=id&dir=<?php echo $sortField === 'id' ? $nextSortDirection : 'desc'; ?>&post_search=<?php echo urlencode($postSearchQuery); ?>&post_status=<?php echo urlencode($postStatusFilter); ?>&user_search=<?php echo urlencode($userSearchQuery); ?>&user_role=<?php echo urlencode($userRoleFilter); ?>">ID
                                        <?php if ($sortField === 'id'): ?>
                                            <i
                                                class="fas fa-sort-<?php echo $sortDirection === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a></th>
                                <th><a
                                        href="?sort=title&dir=<?php echo $sortField === 'title' ? $nextSortDirection : 'desc'; ?>&post_search=<?php echo urlencode($postSearchQuery); ?>&post_status=<?php echo urlencode($postStatusFilter); ?>&user_search=<?php echo urlencode($userSearchQuery); ?>&user_role=<?php echo urlencode($userRoleFilter); ?>">Title
                                        <?php if ($sortField === 'title'): ?>
                                            <i
                                                class="fas fa-sort-<?php echo $sortDirection === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a></th>
                                <th><a
                                        href="?sort=author_name&dir=<?php echo $sortField === 'author_name' ? $nextSortDirection : 'desc'; ?>&post_search=<?php echo urlencode($postSearchQuery); ?>&post_status=<?php echo urlencode($postStatusFilter); ?>&user_search=<?php echo urlencode($userSearchQuery); ?>&user_role=<?php echo urlencode($userRoleFilter); ?>">Author
                                        <?php if ($sortField === 'author_name'): ?>
                                            <i
                                                class="fas fa-sort-<?php echo $sortDirection === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a></th>
                                <th><a
                                        href="?sort=status&dir=<?php echo $sortField === 'status' ? $nextSortDirection : 'desc'; ?>&post_search=<?php echo urlencode($postSearchQuery); ?>&post_status=<?php echo urlencode($postStatusFilter); ?>&user_search=<?php echo urlencode($userSearchQuery); ?>&user_role=<?php echo urlencode($userRoleFilter); ?>">Status
                                        <?php if ($sortField === 'status'): ?>
                                            <i
                                                class="fas fa-sort-<?php echo $sortDirection === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a></th>
                                
                                <th><a
                                        href="?sort=created_at&dir=<?php echo $sortField === 'created_at' ? $nextSortDirection : 'desc'; ?>&post_search=<?php echo urlencode($postSearchQuery); ?>&post_status=<?php echo urlencode($postStatusFilter); ?>&user_search=<?php echo urlencode($userSearchQuery); ?>&user_role=<?php echo urlencode($userRoleFilter); ?>">Created
                                        <?php if ($sortField === 'created_at'): ?>
                                            <i
                                                class="fas fa-sort-<?php echo $sortDirection === 'asc' ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($posts)): ?>
                                <tr>
                                    <td colspan="7" class="no-results">No posts found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($posts as $post): ?>
                                    <tr>
                                        <td><?php echo $post['id']; ?></td>
                                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                                        <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                        <td>
                                            <form method="POST" class="status-form">
                                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                <select name="new_status" class="form-control" onchange="this.form.submit()">
                                                    <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                    <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                                </select>
                                                <input type="hidden" name="update_post_status" value="1">
                                            </form>
                                        </td>
                                        <td><?php echo formatDate($post['created_at']); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                <button type="submit" name="delete_post" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this post?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
    <script>

        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function () {
            document.querySelector('.main-nav ul').classList.toggle('show');
        });

        // Confirm before deleting
        document.querySelectorAll('form[method="POST"]').forEach(form => {
            form.addEventListener('submit', function (e) {
                if (this.querySelector('button[name="delete_user"], button[name="delete_post"]')) {
                    if (!confirm('Are you sure you want to delete this?')) {
                        e.preventDefault();
                    }
                }
            });
        });
    </script>
</body>

</html>