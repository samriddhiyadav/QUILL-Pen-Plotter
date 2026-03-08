<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quill | Luxe Blog Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lora:wght@400;500;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --rich-black: rgb(13, 11, 11);
            --warm-brown: #5D4037;
            --soft-beige: #D7CCC8;
            --vanilla-cream: #E8D8C4;
            --warm-gold: #C9A66B;
            --soft-ivory: #F9F7F4;
            --text-dark: #1A1A1A;
            --text-light: #F5F5F5;
            --gold-gradient: linear-gradient(135deg, #C9A66B 0%, #E8D8C4 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lora', serif;
            background-color: var(--soft-ivory);
            color: var(--text-dark);
            overflow-x: hidden;
            line-height: 1.6;
        }

        h1,
        h2,
        h3,
        h4,
        .logo {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: var(--rich-black);
            color: var(--text-light);
            padding: 1.5rem 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .logo {
            font-size: 2.2rem;
            display: flex;
            align-items: center;
            letter-spacing: 1px;
            color: var(--vanilla-cream);
        }

        .logo-icon {
            margin-right: 12px;
            color: var(--warm-gold);
            animation: pulse 2s infinite;
        }

        nav ul {
            display: flex;
            list-style: none;
        }

        nav ul li {
            margin-left: 2.5rem;
        }

        nav ul li a {
            color: var(--soft-beige);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            letter-spacing: 0.5px;
        }

        nav ul li a:hover {
            color: var(--warm-gold);
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: var(--warm-gold);
            transition: width 0.3s ease;
        }

        nav ul li a:hover::after {
            width: 100%;
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-light);
            font-size: 1.8rem;
            cursor: pointer;
        }

        /* Hero Section */
        .hero {
            height: 90vh;
            min-height: 700px;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            background: linear-gradient(rgba(26, 26, 26, 0.85), rgba(93, 64, 55, 0.8)), url('https://images.unsplash.com/photo-1455390582262-044cdead277a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
            color: var(--vanilla-cream);
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 700px;
        }

        .hero h1 {
            font-size: 4.5rem;
            margin-bottom: 2rem;
            line-height: 1.1;
            letter-spacing: 1px;
            animation: fadeInUp 1s ease;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero p {
            font-size: 1.4rem;
            margin-bottom: 3rem;
            animation: fadeInUp 1s ease 0.2s forwards;
            opacity: 0;
            font-weight: 300;
            letter-spacing: 0.5px;
        }

        .cta-buttons {
            display: flex;
            gap: 1.5rem;
            animation: fadeInUp 1s ease 0.4s forwards;
            opacity: 0;
        }

        .btn {
            padding: 1rem 2.5rem;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .btn-primary {
            background-color: var(--warm-gold);
            color: var(--rich-black);
            border: 2px solid var(--warm-gold);
        }

        .btn-primary:hover {
            background-color: transparent;
            color: var(--warm-gold);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            border: 2px solid var(--warm-gold);
            color: var(--warm-gold);
            background-color: transparent;
        }

        .btn-secondary:hover {
            background-color: var(--warm-gold);
            color: var(--rich-black);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Features Section */
        .features {
            padding: 80px 0;
            background-color: var(--soft-ivory);
            position: relative;
            overflow: hidden;
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--rich-black);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .section-title h2:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background: var(--gold-gradient);
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .subtitle {
            color: #666;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
            font-weight: 300;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            position: relative;
            z-index: 2;
        }

        .feature-card {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(201, 166, 107, 0.2);
        }

        .feature-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 0;
            background: var(--gold-gradient);
            transition: all 0.6s ease;
        }

        .feature-card:hover:before {
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: var(--gold-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            color: var(--rich-black);
            font-size: 1.8rem;
            transition: all 0.4s ease;
        }

        .feature-card:hover .feature-icon {
            transform: rotate(15deg) scale(1.1);
        }

        .feature-card h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: var(--rich-black);
            position: relative;
            display: inline-block;
        }

        .feature-card h3:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background: var(--gold-gradient);
            transition: width 0.4s ease;
        }

        .feature-card:hover h3:after {
            width: 50px;
        }

        .feature-card p {
            font-size: 1rem;
            color: #666;
            line-height: 1.7;
            font-weight: 300;
        }

        /* Testimonials */
        .testimonials {
            padding: 6rem 0;
            background-color: var(--soft-ivory);
            position: relative;
            overflow: hidden;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            position: relative;
            z-index: 2;
        }

        .testimonial-card {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(201, 166, 107, 0.2);
        }

        .testimonial-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 0;
            background: var(--gold-gradient);
            transition: all 0.6s ease;
        }

        .testimonial-card:hover:before {
            height: 100%;
        }

        .testimonial-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
        }

        .testimonial-card::after {
            content: '"';
            position: absolute;
            top: 30px;
            left: 30px;
            font-family: 'Playfair Display', serif;
            font-size: 6rem;
            color: var(--warm-brown);
            opacity: 0.05;
            line-height: 1;
            z-index: 0;
        }

        .testimonial-text {
            font-style: italic;
            margin-bottom: 2rem;
            position: relative;
            z-index: 1;
            font-size: 1.1rem;
            line-height: 1.7;
            font-weight: 300;
            color: #555;
        }

        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--warm-gold);
            transition: all 0.3s ease;
        }

        .testimonial-card:hover .author-avatar {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .author-info h4 {
            color: var(--rich-black);
            font-weight: 600;
        }

        .author-info p {
            font-size: 0.9rem;
            color: var(--text-dark);
            opacity: 0.7;
            font-weight: 300;
        }

        /* CTA Section */
        .cta-section {
            padding: 6rem 0;
            background: linear-gradient(135deg, var(--rich-black), var(--warm-brown));
            color: var(--vanilla-cream);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80') center/cover;
            opacity: 0.1;
            z-index: 1;
        }

        .cta-section .container {
            position: relative;
            z-index: 2;
        }

        .cta-section h2 {
            font-size: 3.5rem;
            margin-bottom: 2rem;
            letter-spacing: 1px;
        }

        .cta-section p {
            max-width: 700px;
            margin: 0 auto 3rem;
            font-size: 1.3rem;
            font-weight: 300;
            letter-spacing: 0.5px;
        }

        /* Footer */
        footer {
            background-color: var(--rich-black);
            color: var(--vanilla-cream);
            padding: 4rem 0 2rem;
            position: relative;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-column h3 {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: var(--warm-gold);
            letter-spacing: 1px;
        }

        .footer-column p {
            font-weight: 300;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column ul li {
            margin-bottom: 1rem;
        }

        .footer-column ul li a {
            color: var(--vanilla-cream);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 300;
            letter-spacing: 0.5px;
        }

        .footer-column ul li a:hover {
            color: var(--warm-gold);
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 1.5rem;
        }

        .social-links a {
            color: var(--vanilla-cream);
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            color: var(--warm-gold);
            transform: translateY(-5px);
        }

        .copyright {
            text-align: center;
            padding-top: 3rem;
            border-top: 1px solid rgba(232, 216, 196, 0.2);
            font-size: 0.9rem;
            opacity: 0.7;
            font-weight: 300;
        }

        /* Animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(3deg);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* How It Works Section */
        .how-it-works {
            padding: 6rem 0;
            background-color: var(--soft-ivory);
            position: relative;
            overflow: hidden;
        }

        .steps {
            display: flex;
            flex-direction: column;
            gap: 30px;
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .step {
            display: flex;
            gap: 3rem;
            align-items: center;
            padding: 3rem;
            background-color: white;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(201, 166, 107, 0.2);
        }

        .step:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 0;
            background: var(--gold-gradient);
            transition: all 0.6s ease;
        }

        .step:hover:before {
            height: 100%;
        }

        .step:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.1);
        }

        .step-number {
            font-size: 4rem;
            font-weight: 700;
            color: var(--warm-brown);
            opacity: 0.15;
            min-width: 80px;
            font-family: 'Playfair Display', serif;
            transition: all 0.4s ease;
        }

        .step:hover .step-number {
            opacity: 0.3;
            transform: scale(1.05);
        }

        .step-content h3 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: var(--rich-black);
            position: relative;
            display: inline-block;
        }

        .step-content h3:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background: var(--gold-gradient);
            transition: width 0.4s ease;
        }

        .step:hover .step-content h3:after {
            width: 50px;
        }

        /* Floating elements for the how-it-works section */
        .how-it-works .floating-element {
            position: absolute;
            background-color: rgba(93, 64, 55, 0.05);
            border-radius: 50%;
            filter: blur(40px);
            z-index: 1;
        }

        .how-it-works .floating-element-1 {
            width: 300px;
            height: 300px;
            top: 10%;
            right: -150px;
            animation: float 15s infinite ease-in-out;
        }

        .how-it-works .floating-element-2 {
            width: 200px;
            height: 200px;
            bottom: 20%;
            left: -100px;
            animation: float 12s infinite ease-in-out 3s;
        }

        /* Floating Elements */
        .floating-element {
            position: absolute;
            background-color: rgba(236, 204, 163, 0.15);
            border-radius: 50%;
            filter: blur(40px);
            z-index: 1;
        }

        .floating-element-1 {
            width: 400px;
            height: 400px;
            top: -200px;
            right: -200px;
            animation: float 18s infinite ease-in-out;
        }

        .floating-element-2 {
            width: 300px;
            height: 300px;
            bottom: 100px;
            left: -150px;
            animation: float 15s infinite ease-in-out 3s;
        }

        .floating-element-3 {
            width: 200px;
            height: 200px;
            top: 40%;
            right: 20%;
            animation: float 12s infinite ease-in-out 2s;
        }

        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .hero h1 {
                font-size: 3.8rem;
            }
        }

        @media (max-width: 992px) {
            .hero h1 {
                font-size: 3.2rem;
            }

            .feature-card {
                padding: 30px 25px;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            nav ul {
                margin-top: 1.5rem;
                flex-direction: column;
                gap: 1.5rem;
                display: none;
            }

            nav ul.show {
                display: flex;
            }

            nav ul li {
                margin-left: 0;
            }

            .mobile-menu-btn {
                display: block;
                position: absolute;
                top: 1.5rem;
                right: 20px;
            }

            .hero {
                height: auto;
                padding: 6rem 0;
                min-height: auto;
                text-align: center;
            }

            .hero h1 {
                font-size: 2.8rem;
            }

            .hero p {
                font-size: 1.2rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .section-title h2 {
                font-size: 2.2rem;
            }

            .feature-card {
                padding: 25px 20px;
            }

            .feature-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .cta-section h2 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 576px) {
            .logo {
                font-size: 1.8rem;
            }

            .hero h1 {
                font-size: 2.2rem;
            }

            .section-title h2 {
                font-size: 1.8rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .btn {
                padding: 0.8rem 1.8rem;
                font-size: 0.8rem;
            }

            .feature-card h3 {
                font-size: 1.2rem;
            }

            .feature-card p {
                font-size: 0.9rem;
            }

            .testimonial-card {
                padding: 2rem;
            }

            .cta-section h2 {
                font-size: 2rem;
            }

            .cta-section p {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header>

        <div class="floating-element floating-element-1"></div>
        <div class="floating-element floating-element-2"></div>
        <div class="floating-element floating-element-3"></div>
        
        <div class="container header-content">
            <div class="logo">
                <i class="fas fa-feather-alt logo-icon"></i>
                <span>Quill</span>
            </div>
            <nav>
                <ul id="nav-menu">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#testimonials">Testimonials</a></li>
                    <li><a href="#pricing">Pricing</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li><a href="src/auth/auth.php?tab=login" style="color: #C9A66B">Sign In</a></li>
                </ul>
            </nav>
            <button class="mobile-menu-btn" id="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero-content">
            <h1>Where Words Find Their Elegance</h1>
            <p>A minimalist yet luxurious blogging platform where Authors craft elegant posts, Admins oversee content,
                and Viewers enjoy a premium reading experience.</p>
            <div class="cta-buttons">
                <a href="src/auth/auth.php?tab=register" class="btn btn-primary">Begin Writing</a>
                <a href="#features" class="btn btn-secondary">Explore Features</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Crafted for Discerning Writers</h2>
                <p class="subtitle">Experience the perfect balance of simplicity and sophistication</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-pen-fancy"></i>
                    </div>
                    <h3>Luxe Editor</h3>
                    <p>A distraction-free writing environment with elegant typography and seamless markdown support for
                        focused creation.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h3>Role System</h3>
                    <p>Three distinct roles (Viewer, Author, Admin) with tailored permissions to maintain content
                        quality and platform integrity.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>Reading Experience</h3>
                    <p>Premium typography with optimal line length and spacing for effortless reading pleasure.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-drafting-compass"></i>
                    </div>
                    <h3>Draft Management</h3>
                    <p>Save works in progress and return to them when inspiration strikes again.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <h3>Admin Dashboard</h3>
                    <p>Comprehensive tools for managing users and content with precision and ease.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Custom Themes</h3>
                    <p>Select from carefully curated color schemes to match your personal aesthetic.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works" id="how-it-works">
        <div class="container">
            <div class="section-title">
                <h2>The Quill Experience</h2>
                <p class="subtitle">A refined approach to blogging that respects both writers and readers</p>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>For Viewers</h3>
                        <p>Immerse yourself in beautifully presented content. Enjoy distraction-free reading with
                            premium typography and layout. Search and discover new writers without any clutter or
                            unnecessary features.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>For Authors</h3>
                        <p>Create elegant posts with our luxe editor. Save drafts, publish when ready, and manage your
                            content effortlessly. The minimalist interface keeps you focused on what matters - your
                            words.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>For Admins</h3>
                        <p>Maintain quality with comprehensive oversight. Manage users, review content, and ensure the
                            platform remains true to its vision of elegant simplicity. Powerful tools presented with
                            refined design.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Voices of Distinction</h2>
                <p class="subtitle">Join the community of discerning writers who've found their perfect platform</p>
            </div>
            <div class="testimonial-grid">
                <div class="testimonial-card">
                    <p class="testimonial-text">Quill transformed my writing routine. The minimalist interface removes
                        all distractions while the elegant typography makes my words shine. It's the perfect balance of
                        form and function.</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Eleanor R."
                            class="author-avatar">
                        <div class="author-info">
                            <h4>Eleanor R.</h4>
                            <p>Novelist & Essayist</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">As an editor managing multiple contributors, the role system is
                        invaluable. I can oversee content quality while giving writers the freedom they need. The admin
                        dashboard is beautifully intuitive.</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Nathaniel W."
                            class="author-avatar">
                        <div class="author-info">
                            <h4>Nathaniel W.</h4>
                            <p>Editor at Literary Review</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <p class="testimonial-text">The reading experience alone justifies using Quill. The typography and
                        layout make long-form content a pleasure to read, and the clean design keeps the focus on the
                        words.</p>
                    <div class="testimonial-author">
                        <img src="https://randomuser.me/api/portraits/women/42.jpg" alt="Isabelle L."
                            class="author-avatar">
                        <div class="author-info">
                            <h4>Isabelle L.</h4>
                            <p>Ph.D. Candidate, Philosophy</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" id="signup">
        <div class="container">
            <h2>Ready to Elevate Your Writing?</h2>
            <p>Experience the intersection of elegant design and powerful writing tools with our free trial.</p>
            <a href="auth/register.php" class="btn btn-primary">Begin Your Journey</a>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Quill</h3>
                    <p>A minimalist yet luxurious blogging platform for those who value both words and design.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-medium"></i></a>
                        <a href="#"><i class="fab fa-pinterest-p"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Platform</h3>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#updates">Release Notes</a></li>
                        <li><a href="#roadmap">Roadmap</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="#blog">Writing Tips</a></li>
                        <li><a href="#guides">Style Guides</a></li>
                        <li><a href="#research">Typography</a></li>
                        <li><a href="#community">Writer's Forum</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Company</h3>
                    <ul>
                        <li><a href="#about">Our Philosophy</a></li>
                        <li><a href="#careers">Join Our Team</a></li>
                        <li><a href="#press">Press Kit</a></li>
                        <li><a href="#contact">Get in Touch</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 Quill. All rights reserved. Crafted with intention for writers worldwide.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const navMenu = document.getElementById('nav-menu');

            mobileMenuBtn.addEventListener('click', () => {
                navMenu.classList.toggle('show');
                mobileMenuBtn.innerHTML = navMenu.classList.contains('show') ?
                    '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
            });

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();

                    const targetId = this.getAttribute('href');
                    const targetElement = document.querySelector(targetId);

                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 100,
                            behavior: 'smooth'
                        });

                        // Close mobile menu if open
                        if (navMenu.classList.contains('show')) {
                            navMenu.classList.remove('show');
                            mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                        }
                    }
                });
            });

            // Animation on scroll with intersection observer for better performance
            const animateElements = () => {
                const elements = document.querySelectorAll('.feature-card, .testimonial-card');

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1 });

                elements.forEach(element => {
                    element.style.opacity = '0';
                    element.style.transform = 'translateY(30px)';
                    element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    observer.observe(element);
                });
            };

            animateElements();
        });
    </script>
</body>

</html>