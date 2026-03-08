/**
 * Project Quill - Luxe Blog
 * Modern JavaScript for all frontend functionality
 */

// DOM Ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize all functionality
    initThemeSwitcher();
    initMobileMenu();
    initFormValidations();
    initRichTextEditor();
    initPostInteractions();
    initAdminDashboard();
    initAuthorDashboard();
    initAnimations();
});

// ======================
// 1. THEME & UI COMPONENTS
// ======================

/**
 * Initialize theme switcher (light/dark mode)
 */
function initThemeSwitcher() {
    const themeToggle = document.getElementById('theme-toggle');
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

    // Check for saved theme or use system preference
    const currentTheme = localStorage.getItem('theme') ||
        (prefersDarkScheme.matches ? 'dark' : 'light');
    document.body.classList.toggle('dark-theme', currentTheme === 'dark');

    if (themeToggle) {
        themeToggle.checked = currentTheme === 'dark';
        themeToggle.addEventListener('change', () => {
            const newTheme = themeToggle.checked ? 'dark' : 'light';
            document.body.classList.toggle('dark-theme', themeToggle.checked);
            localStorage.setItem('theme', newTheme);
        });
    }
}

/**
 * Initialize responsive mobile menu
 */
function initMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.main-nav');

    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            menuToggle.classList.toggle('open');
        });
    }
}

/**
 * Initialize smooth scrolling for anchor links
 */
function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ======================
// 2. FORM HANDLING & VALIDATION
// ======================

/**
 * Initialize form validations
 */
function initFormValidations() {
    // Login form validation
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', validateLoginForm);
    }

    // Registration form validation
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', validateRegisterForm);
    }

    // Post creation/editing form validation
    const postForm = document.getElementById('post-form');
    if (postForm) {
        postForm.addEventListener('submit', validatePostForm);
    }
}

/**
 * Validate login form
 */
function validateLoginForm(e) {
    e.preventDefault();
    const email = this.querySelector('input[name="email"]');
    const password = this.querySelector('input[name="password"]');
    let isValid = true;

    // Reset errors
    clearErrors(this);

    // Email validation
    if (!email.value.trim()) {
        showError(email, 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email.value.trim())) {
        showError(email, 'Please enter a valid email');
        isValid = false;
    }

    // Password validation
    if (!password.value.trim()) {
        showError(password, 'Password is required');
        isValid = false;
    }

    if (isValid) {
        this.submit();
    }
}

/**
 * Validate registration form
 */
function validateRegisterForm(e) {
    e.preventDefault();
    const name = this.querySelector('input[name="name"]');
    const email = this.querySelector('input[name="email"]');
    const password = this.querySelector('input[name="password"]');
    const confirmPassword = this.querySelector('input[name="confirm_password"]');
    let isValid = true;

    // Reset errors
    clearErrors(this);

    // Name validation
    if (!name.value.trim()) {
        showError(name, 'Name is required');
        isValid = false;
    }

    // Email validation
    if (!email.value.trim()) {
        showError(email, 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email.value.trim())) {
        showError(email, 'Please enter a valid email');
        isValid = false;
    }

    // Password validation
    if (!password.value.trim()) {
        showError(password, 'Password is required');
        isValid = false;
    } else if (password.value.length < 8) {
        showError(password, 'Password must be at least 8 characters');
        isValid = false;
    }

    // Confirm password
    if (password.value !== confirmPassword.value) {
        showError(confirmPassword, 'Passwords do not match');
        isValid = false;
    }

    if (isValid) {
        this.submit();
    }
}

/**
 * Validate post form
 */
function validatePostForm(e) {
    e.preventDefault();
    const title = this.querySelector('input[name="title"]');
    const content = this.querySelector('textarea[name="content"]');
    let isValid = true;

    // Reset errors
    clearErrors(this);

    // Title validation
    if (!title.value.trim()) {
        showError(title, 'Title is required');
        isValid = false;
    } else if (title.value.length > 100) {
        showError(title, 'Title must be less than 100 characters');
        isValid = false;
    }

    // Content validation
    if (!content.value.trim()) {
        showError(content, 'Content is required');
        isValid = false;
    } else if (content.value.length < 50) {
        showError(content, 'Content must be at least 50 characters');
        isValid = false;
    }

    if (isValid) {
        this.submit();
    }
}

// Helper function to show error messages
function showError(input, message) {
    const formGroup = input.closest('.form-group');
    if (!formGroup) return;

    let errorElement = formGroup.querySelector('.error-message');
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        formGroup.appendChild(errorElement);
    }

    errorElement.textContent = message;
    formGroup.classList.add('error');
}

// Helper function to clear errors
function clearErrors(form) {
    form.querySelectorAll('.form-group').forEach(group => {
        group.classList.remove('error');
        const errorElement = group.querySelector('.error-message');
        if (errorElement) {
            errorElement.textContent = '';
        }
    });
}

// Helper function to validate email
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ======================
// 3. RICH TEXT EDITOR
// ======================

/**
 * Initialize rich text editor for post content
 */
function initRichTextEditor() {
    const editor = document.getElementById('editor');
    if (!editor) return;

    // Simple toolbar for the editor
    const toolbar = document.createElement('div');
    toolbar.className = 'editor-toolbar';

    // Formatting buttons
    const buttons = [
        { tag: 'bold', icon: 'B', title: 'Bold' },
        { tag: 'italic', icon: 'I', title: 'Italic' },
        { tag: 'h2', icon: 'H2', title: 'Heading 2' },
        { tag: 'h3', icon: 'H3', title: 'Heading 3' },
        { tag: 'quote', icon: '❝', title: 'Quote' },
        { tag: 'ul', icon: '•', title: 'Bullet List' },
        { tag: 'ol', icon: '1.', title: 'Numbered List' },
        { tag: 'link', icon: '🔗', title: 'Insert Link' }
    ];

    buttons.forEach(btn => {
        const button = document.createElement('button');
        button.type = 'button';
        button.innerHTML = btn.icon;
        button.title = btn.title;
        button.dataset.tag = btn.tag;
        button.addEventListener('click', () => formatText(btn.tag));
        toolbar.appendChild(button);
    });

    editor.parentNode.insertBefore(toolbar, editor);

    // Handle formatting
    function formatText(tag) {
        const selection = window.getSelection();
        if (!selection.rangeCount) return;

        const range = selection.getRangeAt(0);
        const selectedText = range.toString();
        if (!selectedText && tag !== 'link') return;

        let formattedText;

        switch (tag) {
            case 'bold':
                formattedText = `<strong>${selectedText}</strong>`;
                break;
            case 'italic':
                formattedText = `<em>${selectedText}</em>`;
                break;
            case 'h2':
                formattedText = `<h2>${selectedText}</h2>`;
                break;
            case 'h3':
                formattedText = `<h3>${selectedText}</h3>`;
                break;
            case 'quote':
                formattedText = `<blockquote>${selectedText}</blockquote>`;
                break;
            case 'ul':
                formattedText = `<ul><li>${selectedText}</li></ul>`;
                break;
            case 'ol':
                formattedText = `<ol><li>${selectedText}</li></ol>`;
                break;
            case 'link':
                const url = prompt('Enter the URL:');
                if (url) {
                    const linkText = selectedText || 'Link';
                    formattedText = `<a href="${url}" target="_blank">${linkText}</a>`;
                } else {
                    return;
                }
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

        // Update the hidden textarea with the HTML content
        updateEditorContent();
    }

    // Update the hidden textarea with HTML content
    function updateEditorContent() {
        const contentField = document.querySelector('textarea[name="content"]');
        if (contentField) {
            contentField.value = editor.innerHTML;
        }
    }

    // Listen for changes in the editor
    editor.addEventListener('input', updateEditorContent);
    editor.addEventListener('blur', updateEditorContent);
}

// ======================
// 4. POST INTERACTIONS
// ======================

/**
 * Initialize post interactions (like, save, share)
 */
function initPostInteractions() {
    // Like button functionality
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const postId = this.dataset.postId;
            try {
                const response = await fetch(`/posts/like/${postId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    const likeCount = this.querySelector('.like-count');
                    if (likeCount) {
                        likeCount.textContent = data.likes;
                    }
                    this.classList.toggle('liked', data.action === 'liked');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });

    // Save/Bookmark functionality
    document.querySelectorAll('.save-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const postId = this.dataset.postId;
            try {
                const response = await fetch(`/posts/save/${postId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.classList.toggle('saved', data.action === 'saved');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    });

    // Share buttons
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const postUrl = this.dataset.url;
            const postTitle = this.dataset.title;

            if (navigator.share) {
                navigator.share({
                    title: postTitle,
                    url: postUrl
                }).catch(err => {
                    console.log('Error sharing:', err);
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const shareWindow = window.open(
                    `https://twitter.com/intent/tweet?text=${encodeURIComponent(postTitle)}&url=${encodeURIComponent(postUrl)}`,
                    '_blank',
                    'width=550,height=420'
                );
                shareWindow.focus();
            }
        });
    });

    // Post search functionality
    const searchForm = document.getElementById('post-search');
    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const query = this.querySelector('input[name="search"]').value.trim();
            if (query) {
                window.location.href = `/posts/search?q=${encodeURIComponent(query)}`;
            }
        });
    }
}

// ======================
// 5. ADMIN DASHBOARD
// ======================

/**
 * Initialize admin dashboard functionality
 */
function initAdminDashboard() {
    const adminDashboard = document.querySelector('.admin-dashboard');
    if (!adminDashboard) return;

    // User management
    document.querySelectorAll('.user-role-select').forEach(select => {
        select.addEventListener('change', async function () {
            const userId = this.dataset.userId;
            const newRole = this.value;

            try {
                const response = await fetch(`/admin/users/${userId}/role`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ role: newRole })
                });

                const data = await response.json();
                if (data.success) {
                    showToast('User role updated successfully');
                } else {
                    showToast('Error updating role', 'error');
                    this.value = this.dataset.originalValue;
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error updating role', 'error');
                this.value = this.dataset.originalValue;
            }
        });
    });

    // Post moderation
    document.querySelectorAll('.post-status-select').forEach(select => {
        select.addEventListener('change', async function () {
            const postId = this.dataset.postId;
            const newStatus = this.value;

            try {
                const response = await fetch(`/admin/posts/${postId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const data = await response.json();
                if (data.success) {
                    showToast('Post status updated successfully');
                    if (newStatus === 'deleted') {
                        document.querySelector(`.post-row[data-post-id="${postId}"]`).remove();
                    }
                } else {
                    showToast('Error updating post status', 'error');
                    this.value = this.dataset.originalValue;
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error updating post status', 'error');
                this.value = this.dataset.originalValue;
            }
        });
    });

    // Statistics charts
    if (typeof Chart !== 'undefined') {
        initDashboardCharts();
    }
}

/**
 * Initialize dashboard charts
 */
function initDashboardCharts() {
    // Users by role chart
    const usersCtx = document.getElementById('usersByRoleChart');
    if (usersCtx) {
        new Chart(usersCtx, {
            type: 'doughnut',
            data: {
                labels: JSON.parse(usersCtx.dataset.labels),
                datasets: [{
                    data: JSON.parse(usersCtx.dataset.data),
                    backgroundColor: [
                        '#C9A66B', // Gold
                        '#1A1A1A', // Black
                        '#E8D8C4'  // Cream
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }

    // Posts by status chart
    // Posts by Status Chart
    const postsCtx = document.getElementById('postsByStatusChart');
    if (postsCtx) {
        const postsData = JSON.parse(postsCtx.dataset.data);

        new Chart(postsCtx, {
            type: 'bar',
            data: {
                labels: JSON.parse(postsCtx.dataset.labels),
                datasets: [{
                    label: 'Published',
                    data: [postsData[0], 0],
                    backgroundColor: '#C9A66B', // Gold for published
                    borderColor: '#1A1A1A',
                    borderWidth: 1
                }, {
                    label: 'Draft',
                    data: [0, postsData[1]],
                    backgroundColor: '#1A1A1A', // Black for draft
                    borderColor: '#C9A66B',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        stacked: false
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
}

// ======================
// 6. AUTHOR DASHBOARD
// ======================

/**
 * Initialize author dashboard functionality
 */
function initAuthorDashboard() {
    const authorDashboard = document.querySelector('.author-dashboard');
    if (!authorDashboard) return;

    // Draft autosave
    const postForm = document.getElementById('post-form');
    if (postForm) {
        let autosaveTimer;
        const titleInput = postForm.querySelector('input[name="title"]');
        const contentInput = postForm.querySelector('textarea[name="content"]');

        const autosave = () => {
            const postId = postForm.dataset.postId || 'new';
            const data = {
                title: titleInput.value,
                content: contentInput.value,
                status: 'draft'
            };

            fetch(`/author/posts/${postId}/autosave`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            }).then(response => response.json())
                .then(data => {
                    if (data.success && postId === 'new') {
                        postForm.dataset.postId = data.postId;
                        window.history.replaceState(null, null, `/author/posts/${data.postId}/edit`);
                    }
                    showToast('Draft autosaved', 'info');
                })
                .catch(error => console.error('Error:', error));
        };

        titleInput.addEventListener('input', () => {
            clearTimeout(autosaveTimer);
            autosaveTimer = setTimeout(autosave, 2000);
        });

        contentInput.addEventListener('input', () => {
            clearTimeout(autosaveTimer);
            autosaveTimer = setTimeout(autosave, 2000);
        });
    }

    // Post status change
    document.querySelectorAll('.post-status-action').forEach(button => {
        button.addEventListener('click', async function () {
            const postId = this.dataset.postId;
            const action = this.dataset.action;

            try {
                const response = await fetch(`/author/posts/${postId}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ action })
                });

                const data = await response.json();
                if (data.success) {
                    showToast(`Post ${action} successfully`);
                    if (action === 'delete') {
                        document.querySelector(`.post-card[data-post-id="${postId}"]`).remove();
                    } else {
                        // Update status indicator
                        const statusBadge = document.querySelector(`.post-card[data-post-id="${postId}"] .post-status`);
                        if (statusBadge) {
                            statusBadge.textContent = action === 'publish' ? 'Published' : 'Draft';
                            statusBadge.className = `post-status ${action === 'publish' ? 'published' : 'draft'}`;
                        }
                    }
                } else {
                    showToast(`Error ${action} post`, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast(`Error ${action} post`, 'error');
            }
        });
    });
}

// ======================
// 7. ANIMATIONS & EFFECTS
// ======================

/**
 * Initialize animations and UI effects
 */
function initAnimations() {
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img.lazy');

        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    if (img.dataset.srcset) {
                        img.srcset = img.dataset.srcset;
                    }
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    }

    // Smooth scroll reveal animation
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.animate-on-scroll');

        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const windowHeight = window.innerHeight;

            if (elementPosition < windowHeight - 100) {
                element.classList.add('animated');
            }
        });
    };

    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Run once on load

    // Tooltips
    document.querySelectorAll('[data-tooltip]').forEach(el => {
        el.addEventListener('mouseenter', function () {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.dataset.tooltip;
            document.body.appendChild(tooltip);

            const rect = this.getBoundingClientRect();
            tooltip.style.left = `${rect.left + rect.width / 2 - tooltip.offsetWidth / 2}px`;
            tooltip.style.top = `${rect.top - tooltip.offsetHeight - 5}px`;

            this.addEventListener('mouseleave', () => {
                tooltip.remove();
            }, { once: true });
        });
    });
}

// ======================
// 8. UTILITY FUNCTIONS
// ======================

/**
 * Show toast notification
 * @param {string} message - The message to display
 * @param {string} [type='success'] - The type of toast (success, error, info)
 */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Debounce function to limit how often a function is called
 * @param {Function} func - The function to debounce
 * @param {number} wait - The delay in milliseconds
 * @returns {Function} - The debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function () {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

/**
 * Throttle function to limit how often a function is called
 * @param {Function} func - The function to throttle
 * @param {number} limit - The delay in milliseconds
 * @returns {Function} - The throttled function
 */
function throttle(func, limit) {
    let lastFunc;
    let lastRan;
    return function () {
        const context = this;
        const args = arguments;
        if (!lastRan) {
            func.apply(context, args);
            lastRan = Date.now();
        } else {
            clearTimeout(lastFunc);
            lastFunc = setTimeout(function () {
                if ((Date.now() - lastRan) >= limit) {
                    func.apply(context, args);
                    lastRan = Date.now();
                }
            }, limit - (Date.now() - lastRan));
        }
    };
}

// ======================
// 9. ERROR HANDLING
// ======================

// Global error handler
window.addEventListener('error', function (e) {
    console.error('Global error:', e.error);
    showToast('An unexpected error occurred', 'error');
});

// Unhandled promise rejection handler
window.addEventListener('unhandledrejection', function (e) {
    console.error('Unhandled rejection:', e.reason);
    showToast('An unexpected error occurred', 'error');
    e.preventDefault();
});