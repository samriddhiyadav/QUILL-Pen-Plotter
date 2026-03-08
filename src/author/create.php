<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Start session and check author access
session_start();
if (!isLoggedIn() || !isAuthor()) {
    redirect(BASE_URL . '/src/auth/auth.php');
}

$errors = [];
$postData = [
    'title' => '',
    'content' => '',
    'status' => 'draft',
    'tags' => []
];

// Get all available tags for the dropdown
$tags = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM tags ORDER BY name");
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Failed to load tags: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $postData['title'] = trim($_POST['title'] ?? '');
    $postData['content'] = trim($_POST['content'] ?? '');
    $postData['status'] = $_POST['status'] ?? 'draft';
    $postData['author_id'] = $_SESSION['user_id'];
    $postData['tags'] = $_POST['tags'] ?? [];

    // Validate input
    if (empty($postData['title'])) {
        $errors['title'] = 'Title is required';
    } elseif (strlen($postData['title']) > 255) {
        $errors['title'] = 'Title must be less than 255 characters';
    }

    if (empty($postData['content'])) {
        $errors['content'] = 'Content is required';
    } elseif (strlen(strip_tags($postData['content'])) < 50) {
        $errors['content'] = 'Content must be at least 50 characters';
    }

    if (!in_array($postData['status'], ['draft', 'published'])) {
        $errors['status'] = 'Invalid status';
    }

    // Validate tags
    if (!empty($postData['tags'])) {
        foreach ($postData['tags'] as $tagId) {
            if (!is_numeric($tagId)) {
                $errors['tags'] = 'Invalid tag selection';
                break;
            }
        }
    }

    // Handle file upload for featured image
    $featuredImage = null;
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '/uploads/'; // Relative to document root
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $fileExt = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('img_') . '.' . $fileExt;
        $filePath = $uploadPath . $fileName;

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['featured_image']['type'], $allowedTypes)) {
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $filePath)) {
                $featuredImage = $uploadDir . $fileName;
            } else {
                $errors['featured_image'] = 'Failed to upload image';
            }
        } else {
            $errors['featured_image'] = 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.';
        }
    }

    // If no errors, save the post and related data
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Save the post
            $postId = createPost($pdo, $postData);

            if ($postId) {
                // Save featured image if uploaded
                if ($featuredImage) {
                    $stmt = $pdo->prepare("INSERT INTO images (post_id, image_url, is_featured) VALUES (?, ?, 1)");
                    $stmt->execute([$postId, $featuredImage]);
                }

                // Save tags if any
                if (!empty($postData['tags'])) {
                    $stmt = $pdo->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                    foreach ($postData['tags'] as $tagId) {
                        $stmt->execute([$postId, $tagId]);
                    }
                }

                $pdo->commit();

                $_SESSION['success'] = "Post " . ($postData['status'] === 'published' ? 'published' : 'saved as draft') . " successfully!";
                redirect(BASE_URL . '/src/author/dashboard.php');
            } else {
                $errors[] = "Failed to save post. Please try again.";
                $pdo->rollBack();
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Database error: " . $e->getMessage();
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
    <title>Create New Post | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lora:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/styles.css" rel="stylesheet">
    <style>
        /* Rich text editor styles */
        .editor-toolbar {
            background: #f8f9fa;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px 4px 0 0;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .editor-toolbar button {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 5px 10px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .editor-toolbar button:hover {
            background: #f0f0f0;
        }

        .editor-toolbar button.active {
            background: #e0e0e0;
        }

        .rich-text-editor {
            min-height: 300px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
            padding: 15px;
            outline: none;
        }

        .rich-text-editor:focus {
            border-color: #aaa;
        }

        .rich-text-editor ul,
        .rich-text-editor ol {
            padding-left: 30px;
        }

        .rich-text-editor ul {
            list-style-type: disc;
        }

        .rich-text-editor ol {
            list-style-type: decimal;
        }

        .status-options {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .status-option {
            display: flex;
            align-items: center;
        }

        .status-option input[type="radio"] {
            appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #d4af37;
            border-radius: 50%;
            margin-right: 8px;
            position: relative;
            cursor: pointer;
        }

        .status-option input[type="radio"]:checked {
            background-color: #d4af37;
        }

        .status-option label {
            cursor: pointer;
            font-weight: 500;
        }

        .image-upload-container {
            margin-top: 15px;
        }

        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }

        .featured-image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #ced4da !important;
        }
    </style>
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
                            <li><a href="<?php echo BASE_URL; ?>/src/author/create.php" class="active">New Post</a></li>
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
                    <h2>Create New Post</h2>
                    <p class="subtitle">Craft your next masterpiece</p>
                </div>

                <!-- Success/Error Messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert-message success-message">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert-message error-message">
                        <?php if (is_array($errors) && array_values($errors) === $errors): ?>
                            <!-- This is an indexed array -->
                            <?php if (count($errors) === 1): ?>
                                <?php echo $errors[0]; ?>
                            <?php else: ?>
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- This is an associative array -->
                            <ul>
                                <?php foreach ($errors as $field => $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Post Form -->
                <form id="post-form" method="POST" class="post-form-container" enctype="multipart/form-data">
                    <div class="form-group <?php echo isset($errors['title']) ? 'error' : ''; ?>">
                        <label for="title">Post Title</label>
                        <input type="text" id="title" name="title" class="form-control"
                            value="<?php echo htmlspecialchars($postData['title']); ?>" required>
                        <?php if (isset($errors['title'])): ?>
                            <div class="error-message"><?php echo $errors['title']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group image-upload-container">
                        <label class="image-upload-label" for="featured_image">Featured Image</label>
                        <label for="featured_image" class="custom-file-upload">
                            <div class="file-info">
                                <i class="fas fa-image"></i>
                                <span id="file-name">Choose an image...</span>
                            </div>
                            <span class="upload-btn">Browse</span>
                        </label>
                        <input type="file" id="featured_image" name="featured_image" class="file-input"
                            accept="image/*">
                        <img id="featured-image-preview" class="featured-image-preview" src="#"
                            alt="Featured image preview">
                        <?php if (isset($errors['featured_image'])): ?>
                            <div class="error-message"><?php echo $errors['featured_image']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="tags">Tags</label>
                        <select id="tags" name="tags[]" class="form-control" multiple="multiple">
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?php echo $tag['id']; ?>" <?php echo in_array($tag['id'], $postData['tags']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['tags'])): ?>
                            <div class="error-message"><?php echo $errors['tags']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group <?php echo isset($errors['content']) ? 'error' : ''; ?>">
                        <label for="content">Post Content</label>

                        <!-- Editor Toolbar -->
                        <div class="editor-toolbar">
                            <button type="button" data-tag="bold" title="Bold"><i class="fas fa-bold"></i></button>
                            <button type="button" data-tag="italic" title="Italic"><i
                                    class="fas fa-italic"></i></button>
                            <button type="button" data-tag="underline" title="Underline"><i
                                    class="fas fa-underline"></i></button>
                            <button type="button" data-tag="h1" title="Heading 1">H1</button>
                            <button type="button" data-tag="h2" title="Heading 2">H2</button>
                            <button type="button" data-tag="h3" title="Heading 3">H3</button>
                            <button type="button" data-tag="h4" title="Heading 4">H4</button>
                            <button type="button" data-tag="h5" title="Heading 5">H5</button>
                            <button type="button" data-tag="h6" title="Heading 6">H6</button>
                            <button type="button" data-tag="ul" title="Bullet List"><i
                                    class="fas fa-list-ul"></i></button>
                            <button type="button" data-tag="ol" title="Numbered List"><i
                                    class="fas fa-list-ol"></i></button>
                            <button type="button" data-tag="quote" title="Quote"><i
                                    class="fas fa-quote-right"></i></button>
                            <button type="button" data-tag="link" title="Insert Link"><i
                                    class="fas fa-link"></i></button>
                            <button type="button" id="insert-image-btn" title="Insert Image"><i
                                    class="fas fa-image"></i></button>
                            <input type="file" id="image-upload" accept="image/*" style="display: none;">
                        </div>

                        <!-- Editor Content -->
                        <div id="editor" class="rich-text-editor" contenteditable="true">
                            <?php echo $postData['content']; ?>
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
                            Save Post
                        </button>
                    </div>
                </form>

                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const editor = document.getElementById('editor');
                        const contentField = document.getElementById('content');
                        const toolbarButtons = document.querySelectorAll('.editor-toolbar button[data-tag]');
                        const imageUpload = document.getElementById('image-upload');
                        const insertImageBtn = document.getElementById('insert-image-btn');
                        const featuredImageInput = document.getElementById('featured_image');
                        const featuredImagePreview = document.getElementById('featured-image-preview');
                        const fileNameSpan = document.getElementById('file-name');

                        // Initialize Select2 for tags
                        $('#tags').select2({
                            placeholder: "Select tags",
                            allowClear: true
                        });

                        // Update the hidden textarea with HTML content
                        function updateEditorContent() {
                            contentField.value = editor.innerHTML;
                        }

                        // Format text based on button click
                        toolbarButtons.forEach(button => {
                            button.addEventListener('click', function () {
                                const tag = this.getAttribute('data-tag');
                                formatText(tag);
                                this.classList.toggle('active');
                            });
                        });

                        // Check if selection is already formatted with a tag
                        function isFormattedWith(tag) {
                            const selection = window.getSelection();
                            if (!selection.rangeCount) return false;
                            
                            const range = selection.getRangeAt(0);
                            let node = range.commonAncestorContainer;
                            
                            // Walk up the DOM tree to find if we're inside the tag
                            while (node !== null && node !== editor) {
                                if (node.nodeType === Node.ELEMENT_NODE) {
                                    if (node.tagName.toLowerCase() === tag) {
                                        return true;
                                    }
                                    // For heading tags, check if we're inside any heading
                                    if (tag === 'h1' || tag === 'h2' || tag === 'h3' || 
                                        tag === 'h4' || tag === 'h5' || tag === 'h6') {
                                        if (node.tagName.toLowerCase().match(/^h[1-6]$/)) {
                                            return true;
                                        }
                                    }
                                }
                                node = node.parentNode;
                            }
                            return false;
                        }

                        // Handle text formatting with toggle functionality
                        function formatText(tag) {
                            const selection = window.getSelection();
                            if (!selection.rangeCount) return;

                            const range = selection.getRangeAt(0);
                            const selectedText = range.toString();

                            // Special handling for links and images
                            if (tag === 'link') {
                                const url = prompt('Enter the URL:');
                                if (url) {
                                    const linkText = selectedText || url;
                                    const formattedText = `<a href="${url}" style="color: #0066cc; text-decoration: underline;">${linkText}</a>`;
                                    range.deleteContents();
                                    range.insertNode(document.createRange().createContextualFragment(formattedText));
                                }
                                updateEditorContent();
                                return;
                            }

                            if (tag === 'image') {
                                imageUpload.click();
                                return;
                            }

                            // Check if we're already inside this formatting
                            if (isFormattedWith(tag)) {
                                // Remove formatting
                                const formattedElement = selection.anchorNode.parentNode.closest(tag);
                                if (formattedElement) {
                                    // Move all children out of the element
                                    while (formattedElement.firstChild) {
                                        formattedElement.parentNode.insertBefore(formattedElement.firstChild, formattedElement);
                                    }
                                    // Remove the empty element
                                    formattedElement.parentNode.removeChild(formattedElement);
                                }
                            } else {
                                // Apply formatting
                                if (!selectedText && tag !== 'ul' && tag !== 'ol' && tag !== 'quote') return;

                                let formattedText;
                                switch (tag) {
                                    case 'bold':
                                        formattedText = `<strong>${selectedText}</strong>`;
                                        break;
                                    case 'italic':
                                        formattedText = `<em>${selectedText}</em>`;
                                        break;
                                    case 'underline':
                                        formattedText = `<u>${selectedText}</u>`;
                                        break;
                                    case 'h1':
                                    case 'h2':
                                    case 'h3':
                                    case 'h4':
                                    case 'h5':
                                    case 'h6':
                                        formattedText = `<${tag}>${selectedText}</${tag}>`;
                                        break;
                                    case 'ul':
                                        formattedText = `<ul><li>${selectedText || 'List item'}</li></ul>`;
                                        break;
                                    case 'ol':
                                        formattedText = `<ol><li>${selectedText || 'List item'}</li></ol>`;
                                        break;
                                    case 'quote':
                                        formattedText = `<blockquote><q><em><strong>${selectedText}</strong></em></q></blockquote>`;
                                        break;
                                    default:
                                        return;
                                }

                                // Insert the formatted text
                                range.deleteContents();
                                const div = document.createElement('div');
                                div.innerHTML = formattedText;
                                const frag = document.createDocumentFragment();

                                while (div.firstChild) {
                                    frag.appendChild(div.firstChild);
                                }

                                range.insertNode(frag);
                            }
                            updateEditorContent();
                        }

                        // Handle image upload in editor
                        insertImageBtn.addEventListener('click', function () {
                            imageUpload.click();
                        });

                        imageUpload.addEventListener('change', function (e) {
                            if (e.target.files.length > 0) {
                                const file = e.target.files[0];
                                const reader = new FileReader();

                                reader.onload = function (event) {
                                    const img = document.createElement('img');
                                    img.src = event.target.result;
                                    img.style.maxWidth = '100%';

                                    const selection = window.getSelection();
                                    if (selection.rangeCount) {
                                        const range = selection.getRangeAt(0);
                                        range.deleteContents();
                                        range.insertNode(img);
                                    } else {
                                        editor.appendChild(img);
                                    }

                                    updateEditorContent();
                                };

                                reader.readAsDataURL(file);
                            }
                        });

                        // Handle featured image preview
                        featuredImageInput.addEventListener('change', function (e) {
                            if (e.target.files.length > 0) {
                                const file = e.target.files[0];
                                fileNameSpan.textContent = file.name;
                                
                                const reader = new FileReader();
                                reader.onload = function (event) {
                                    featuredImagePreview.src = event.target.result;
                                    featuredImagePreview.style.display = 'block';
                                };
                                reader.readAsDataURL(file);
                            } else {
                                fileNameSpan.textContent = 'Choose an image...';
                                featuredImagePreview.style.display = 'none';
                            }
                        });

                        // Listen for changes in the editor
                        editor.addEventListener('input', updateEditorContent);
                        editor.addEventListener('blur', updateEditorContent);
                    });
                </script>
</body>

</html>