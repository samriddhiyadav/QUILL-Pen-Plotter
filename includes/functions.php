<?php
// functions.php - Essential utility functions for Quill

/**
 * Redirect to a specified URL
 * @param string $url The URL to redirect to
 */
function redirect($url)
{
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Get the current user's role
 * @return string|null User role or null if not logged in
 */
function getCurrentUserRole()
{
    return $_SESSION['role'] ?? null;
}

/**
 * Check if current user has admin privileges
 * @return bool True if user is admin, false otherwise
 */
function isAdmin()
{
    return getCurrentUserRole() === 'admin';
}

/**
 * Check if current user has author privileges
 * @return bool True if user is author, false otherwise
 */
function isAuthor()
{
    $role = getCurrentUserRole();
    return $role === 'author' || $role === 'admin';
}

/**
 * Sanitize input data to prevent XSS
 * @param string $data The input to sanitize
 * @return string Sanitized output
 */
function sanitize($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format date in a consistent way
 * @param string $dateString The date string to format
 * @return string Formatted date
 */
function formatDate($dateString)
{
    return date('F j, Y', strtotime($dateString));
}

/**
 * Get all published posts with optional featured image and tags
 * @param PDO $pdo Database connection
 * @param int $limit Number of posts to return (0 for all)
 * @param bool $publishedOnly Whether to return only published posts
 * @return array Array of post data
 */
function getAllPosts($pdo, $limit = 0, $publishedOnly = true) {
    try {
        $sql = "SELECT p.*, u.name as author_name,
                GROUP_CONCAT(DISTINCT t.name) as tags,
                (SELECT image_url FROM images WHERE post_id = p.id AND is_featured = TRUE LIMIT 1) as featured_image
                FROM posts p
                JOIN users u ON p.author_id = u.id
                LEFT JOIN post_tags pt ON p.id = pt.post_id
                LEFT JOIN tags t ON pt.tag_id = t.id";

        if ($publishedOnly) {
            $sql .= " WHERE p.status = 'published'";
        }

        $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

        if ($limit > 0) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $pdo->prepare($sql);

        if ($limit > 0) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getAllPosts: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single post by ID with featured image and tags
 * @param PDO $pdo Database connection
 * @param int $postId The ID of the post to retrieve
 * @return array|null Post data or null if not found
 */
function getPostById($pdo, $postId) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, u.name as author_name,
                             GROUP_CONCAT(DISTINCT t.name) as tags,
                             (SELECT image_url FROM images WHERE post_id = p.id AND is_featured = TRUE LIMIT 1) as featured_image
                             FROM posts p
                             JOIN users u ON p.author_id = u.id
                             LEFT JOIN post_tags pt ON p.id = pt.post_id
                             LEFT JOIN tags t ON pt.tag_id = t.id
                             WHERE p.id = :id
                             GROUP BY p.id");
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getPostById: " . $e->getMessage());
        return null;
    }
}

/**
 * Get all images for a post
 * @param PDO $pdo Database connection
 * @param int $postId The ID of the post
 * @return array Array of image data
 */
function getPostImages($pdo, $postId) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM images WHERE post_id = :post_id ORDER BY is_featured DESC, created_at");
        $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getPostImages: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all tags for a post
 * @param PDO $pdo Database connection
 * @param int $postId The ID of the post
 * @return array Array of tag data
 */
function getPostTags($pdo, $postId) {
    try {
        $stmt = $pdo->prepare("SELECT t.* FROM tags t
                              JOIN post_tags pt ON t.id = pt.tag_id
                              WHERE pt.post_id = :post_id");
        $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getPostTags: " . $e->getMessage());
        return [];
    }
}

/**
 * Get all available tags
 * @param PDO $pdo Database connection
 * @return array Array of all tags
 */
function getAllTags($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM tags ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getAllTags: " . $e->getMessage());
        return [];
    }
}

/**
 * Add tags to a post
 * @param PDO $pdo Database connection
 * @param int $postId The ID of the post
 * @param array $tagIds Array of tag IDs to add
 * @return bool True on success, false on failure
 */
function addTagsToPost($pdo, $postId, $tagIds) {
    try {
        $pdo->beginTransaction();
        
        foreach ($tagIds as $tagId) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)");
            $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
            $stmt->bindParam(':tag_id', $tagId, PDO::PARAM_INT);
            $stmt->execute();
        }
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Database error in addTagsToPost: " . $e->getMessage());
        return false;
    }
}

/**
 * Remove all tags from a post
 * @param PDO $pdo Database connection
 * @param int $postId The ID of the post
 * @return bool True on success, false on failure
 */
function removeAllTagsFromPost($pdo, $postId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM post_tags WHERE post_id = :post_id");
        $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database error in removeAllTagsFromPost: " . $e->getMessage());
        return false;
    }
}

/**
 * Add an image to a post
 * @param PDO $pdo Database connection
 * @param int $postId The ID of the post
 * @param string $imageUrl The URL/path of the image
 * @param bool $isFeatured Whether this is the featured image
 * @return bool True on success, false on failure
 */
function addPostImage($pdo, $postId, $imageUrl, $isFeatured = false) {
    try {
        // If setting as featured, first unfeature any existing featured images
        if ($isFeatured) {
            $stmt = $pdo->prepare("UPDATE images SET is_featured = FALSE WHERE post_id = :post_id");
            $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
            $stmt->execute();
        }
        
        $stmt = $pdo->prepare("INSERT INTO images (post_id, image_url, is_featured) 
                              VALUES (:post_id, :image_url, :is_featured)");
        $stmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
        $stmt->bindParam(':image_url', $imageUrl);
        $stmt->bindValue(':is_featured', $isFeatured, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database error in addPostImage: " . $e->getMessage());
        return false;
    }
}

/**
 * Get posts by a specific author
 * @param PDO $pdo Database connection
 * @param int $authorId The ID of the author
 * @param string $status Post status filter (optional)
 * @return array Array of posts
 */
function getPostsByAuthor($pdo, $authorId, $status = null)
{
    try {
        $sql = "SELECT * FROM posts WHERE author_id = :author_id";

        if ($status) {
            $sql .= " AND status = :status";
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':author_id', $authorId, PDO::PARAM_INT);

        if ($status) {
            $stmt->bindParam(':status', $status);
        }

        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);


        return $result;
    } catch (PDOException $e) {
        // Log the error
        error_log("Database error in getPostsByAuthor: " . $e->getMessage());
        return [];
    }
}

/**
 * Create a new post
 * @param PDO $pdo Database connection
 * @param array $postData Array containing post data (title, content, author_id, status)
 * @return int|false ID of the new post or false on failure
 */
function createPost($pdo, $postData)
{
    try {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, author_id, status, created_at) 
                              VALUES (:title, :content, :author_id, :status, NOW())");

        $stmt->bindParam(':title', $postData['title']);
        $stmt->bindParam(':content', $postData['content']);
        $stmt->bindParam(':author_id', $postData['author_id'], PDO::PARAM_INT);
        $stmt->bindParam(':status', $postData['status']);

        $stmt->execute();
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Update an existing post
 * @param PDO $pdo Database connection
 * @param int $postId The ID of the post to update
 * @param array $postData Array containing updated post data
 * @return bool True on success, false on failure
 */
function updatePost($pdo, $postId, $postData)
{
    try {
        $stmt = $pdo->prepare("UPDATE posts 
                              SET title = :title, content = :content, status = :status, updated_at = NOW() 
                              WHERE id = :id");

        $stmt->bindParam(':title', $postData['title']);
        $stmt->bindParam(':content', $postData['content']);
        $stmt->bindParam(':status', $postData['status']);
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);

        return $stmt->execute();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Update post status
 * @param PDO $pdo Database connection
 * @param int $postId The ID of the post to update
 * @param string $newStatus The new status ('draft' or 'published')
 * @return bool True on success, false on failure
 */
function updatePostStatus($pdo, $postId, $newStatus)
{
    try {
        $stmt = $pdo->prepare("UPDATE posts SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$newStatus, $postId]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Delete a post
 * @param PDO $pdo Database connection
 * @param int $postId The ID of the post to delete
 * @return bool True on success, false on failure
 */
function deletePost($pdo, $postId)
{
    try {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get user by ID
 * @param PDO $pdo Database connection
 * @param int $userId The ID of the user to retrieve
 * @return array|null User data or null if not found
 */
function getUserById($pdo, $userId)
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get user by email
 * @param PDO $pdo Database connection
 * @param string $email The email to search for
 * @return array|null User data or null if not found
 */
function getUserByEmail($pdo, $email)
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get all users
 * @param PDO $pdo Database connection
 * @return array Array of all users
 */
function getAllUsers($pdo)
{
    try {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Update user role
 * @param PDO $pdo Database connection
 * @param int $userId The ID of the user to update
 * @param string $newRole The new role to assign
 * @return bool True on success, false on failure
 */
function updateUserRole($pdo, $userId, $newRole)
{
    try {
        $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->bindParam(':role', $newRole);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Register a new user
 * @param PDO $pdo Database connection
 * @param array $userData Array containing user data (name, email, password)
 * @param string $role User role (default: 'viewer')
 * @return int|false ID of the new user or false on failure
 */
function registerUser($pdo, $userData, $role = 'viewer')
{
    try {
        $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, created_at) 
                              VALUES (:name, :email, :password_hash, :role, NOW())");

        $stmt->bindParam(':name', $userData['name']);
        $stmt->bindParam(':email', $userData['email']);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->bindParam(':role', $role);

        $stmt->execute();
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Verify user credentials
 * @param PDO $pdo Database connection
 * @param string $email User email
 * @param string $password User password
 * @return array|false User data if credentials are valid, false otherwise
 */
function verifyCredentials($pdo, $email, $password)
{
    $user = getUserByEmail($pdo, $email);

    if ($user && password_verify($password, $user['password_hash'])) {
        return $user;
    }

    return false;
}

/**
 * Check if a post belongs to the current user
 * @param PDO $pdo Database connection
 * @param int $postId The ID of the post to check
 * @return bool True if post belongs to current user, false otherwise
 */
function isPostOwner($pdo, $postId)
{
    if (!isLoggedIn())
        return false;

    $post = getPostById($pdo, $postId);
    return $post && ($post['author_id'] == $_SESSION['user_id'] || isAdmin());
}

/**
 * Display error message and exit
 * @param string $message The error message to display
 * @param string $redirectUrl URL to redirect to (optional)
 */
function showError($message, $redirectUrl = null)
{
    $_SESSION['error'] = $message;

    if ($redirectUrl) {
        redirect($redirectUrl);
    } else {
        include_once __DIR__ . '/../includes/header.php';
        echo '<div class="alert alert-danger">' . $message . '</div>';
        include_once __DIR__ . '/../includes/footer.php';
        exit();
    }
}

/**
 * Display success message
 * @param string $message The success message to display
 * @param string $redirectUrl URL to redirect to (optional)
 */
function showSuccess($message, $redirectUrl = null)
{
    $_SESSION['success'] = $message;

    if ($redirectUrl) {
        redirect($redirectUrl);
    }
}

/**
 * Get flash message and clear it from session
 * @param string $type Message type ('success' or 'error')
 * @return string|null The message or null if none exists
 */
function getFlashMessage($type)
{
    $key = $type === 'success' ? 'success' : 'error';
    $message = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return $message;
}

/**
 * Generate excerpt from content
 * @param string $content The full content
 * @param int $length Length of excerpt in words
 * @return string Generated excerpt
 */
function generateExcerpt($content, $length = 30)
{
    $words = explode(' ', strip_tags($content));
    if (count($words) > $length) {
        return implode(' ', array_slice($words, 0, $length)) . '...';
    }
    return $content;
}

/**
 * Get a unique image for a post based on its ID
 * @param int $postId The post ID
 * @return string Path to the image
 */
function getPostImage($postId) {
    $imageDir = $_SERVER['DOCUMENT_ROOT'] . BASE_URL . '/assets/images/';
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $images = [];
    
    // Scan directory for valid images (excluding default-post.jpg)
    if (is_dir($imageDir)) {
        $files = scandir($imageDir);
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExtensions) && strpos($file, 'default-post') === false) {
                $images[] = $file;
            }
        }
    }
    
    // If no images found, fall back to default
    if (empty($images)) {
        return BASE_URL . '/assets/images/image1.jpg';
    }
    
    // Select image based on post ID (modulo ensures we stay within array bounds)
    $imageIndex = $postId % count($images);
    return BASE_URL . '/assets/images/' . $images[$imageIndex];
}
?>
