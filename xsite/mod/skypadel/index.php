<?php
/**
 * Sky Padel India - Landing Page
 * Premium Athletic Design with Parallax Effects
 */
include_once("x-skypadel.inc.php");

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xAction']) && $_POST['xAction'] === 'INQUIRY') {
    header('Content-Type: application/json');
    echo json_encode(spSubmitInquiry($_POST));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo spClean($spConfig['company_name']); ?> - <?php echo spClean($spConfig['tagline']); ?></title>
    <meta name="description" content="<?php echo spClean($spConfig['description']); ?>">
    <meta name="keywords" content="padel court, padel court manufacturer, padel court installation, padel india, sports infrastructure">

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo spClean($spConfig['company_name']); ?>">
    <meta property="og:description" content="<?php echo spClean($spConfig['description']); ?>">
    <meta property="og:type" content="website">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%230077B6' width='100' height='100' rx='12'/><circle cx='50' cy='50' r='25' fill='%23FCD34D'/></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo SP_ASSETS_URL; ?>css/skypadel.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="nav" id="mainNav">
        <div class="container">
            <div class="nav-inner">
                <a href="#" class="nav-logo">
                    <div class="nav-logo-icon">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/></svg>
                    </div>
                    <span class="nav-logo-text">SKY PADEL</span>
                </a>

                <div class="nav-links">
                    <a href="#about" class="nav-link">About</a>
                    <a href="#process" class="nav-link">Process</a>
                    <a href="#projects" class="nav-link">Projects</a>
                    <a href="#features" class="nav-link">Why Us</a>
                    <a href="#contact" class="nav-cta">
                        Get Quote
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <button class="nav-toggle" onclick="toggleMobileNav()">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation -->
    <div class="nav-mobile" id="mobileNav">
        <button class="nav-mobile-close" onclick="toggleMobileNav()">
            <span></span>
            <span></span>
        </button>
        <div class="nav-mobile-links">
            <a href="#about" class="nav-mobile-link" onclick="toggleMobileNav()">About</a>
            <a href="#process" class="nav-mobile-link" onclick="toggleMobileNav()">Process</a>
            <a href="#projects" class="nav-mobile-link" onclick="toggleMobileNav()">Projects</a>
            <a href="#features" class="nav-mobile-link" onclick="toggleMobileNav()">Why Us</a>
            <a href="#contact" class="nav-mobile-link" onclick="toggleMobileNav()">Contact</a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero" id="hero">
        <div class="hero-bg">
            <div class="hero-bg-image" data-parallax data-speed="0.3"></div>
            <div class="hero-court-lines"></div>
        </div>

        <!-- Floating Elements -->
        <div class="hero-floats">
            <div class="float-ball float-ball-1"></div>
            <div class="float-ball float-ball-2"></div>
            <div class="float-ball float-ball-3"></div>
            <div class="float-racket" data-parallax data-speed="0.1">
                <svg viewBox="0 0 100 180">
                    <ellipse cx="50" cy="50" rx="40" ry="50" fill="none" stroke="currentColor" stroke-width="3"/>
                    <line x1="50" y1="100" x2="50" y2="175" stroke="currentColor" stroke-width="8" stroke-linecap="round"/>
                    <line x1="20" y1="30" x2="80" y2="30" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                    <line x1="15" y1="45" x2="85" y2="45" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                    <line x1="15" y1="60" x2="85" y2="60" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                    <line x1="20" y1="75" x2="80" y2="75" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                    <line x1="35" y1="10" x2="35" y2="90" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                    <line x1="50" y1="5" x2="50" y2="95" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                    <line x1="65" y1="10" x2="65" y2="90" stroke="currentColor" stroke-width="1" opacity="0.5"/>
                </svg>
            </div>
        </div>

        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="hero-badge-dot"></span>
                    <span class="hero-badge-text">Padel Sports Foundation Approved</span>
                </div>

                <h1 class="display-1 hero-title">
                    <span class="hero-title-line">
                        <span class="hero-title-word">The Ball</span>
                    </span>
                    <span class="hero-title-line">
                        <span class="hero-title-word">Is In <span class="highlight">Your Court</span></span>
                    </span>
                </h1>

                <p class="hero-subtitle">
                    We design, manufacture and install the safest and highest-quality padel courts worldwide.
                    Manufacturing facilities in Spain, America & India.
                </p>

                <div class="hero-actions">
                    <a href="#contact" class="btn btn-primary btn-lg">
                        Get Free Quote
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="#projects" class="btn btn-secondary btn-lg">
                        View Projects
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-value"><?php echo $spConfig['stats']['courts']; ?></div>
                        <div class="hero-stat-label">Courts Installed</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value"><?php echo $spConfig['stats']['countries']; ?></div>
                        <div class="hero-stat-label">Countries Served</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value"><?php echo $spConfig['stats']['partners']; ?></div>
                        <div class="hero-stat-label">Global Partners</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-value"><?php echo $spConfig['stats']['warranty']; ?><span>*</span></div>
                        <div class="hero-stat-label">Anti-Rust Warranty</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="scroll-indicator">
            <span>Scroll</span>
            <div class="scroll-indicator-line"></div>
        </div>
    </section>

    <!-- About Section -->
    <section class="section about" id="about">
        <div class="container">
            <div class="about-grid">
                <div class="about-content reveal">
                    <span class="subtitle about-label">About Sky Padel</span>
                    <h2 class="heading-1 about-title">Leading Padel Court<br>Manufacturer in India</h2>
                    <p class="body-lg about-text">
                        Sky Padel is a premium padel court manufacturer that designs, manufactures,
                        and installs world-class courts globally. With manufacturing facilities in
                        <strong>Spain, America, and India</strong>, we maintain European and USA quality
                        standards while offering competitive local pricing.
                    </p>
                    <p class="about-text">
                        Our commitment to excellence has made us the trusted partner for sports
                        facilities, clubs, and organizations across 50+ countries. We serve customers
                        through a robust network of 40+ partners worldwide.
                    </p>

                    <div class="about-features">
                        <div class="about-feature">
                            <div class="about-feature-icon">
                                <svg viewBox="0 0 24 24" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                            </div>
                            <div class="about-feature-content">
                                <h4>European Standards</h4>
                                <p>Quality approved by international bodies</p>
                            </div>
                        </div>
                        <div class="about-feature">
                            <div class="about-feature-icon">
                                <svg viewBox="0 0 24 24" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/>
                                </svg>
                            </div>
                            <div class="about-feature-content">
                                <h4>Global Presence</h4>
                                <p>Manufacturing in 3 continents</p>
                            </div>
                        </div>
                        <div class="about-feature">
                            <div class="about-feature-icon">
                                <svg viewBox="0 0 24 24" stroke-width="2">
                                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                                    <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                            </div>
                            <div class="about-feature-content">
                                <h4>End-to-End Service</h4>
                                <p>Design, manufacture & install</p>
                            </div>
                        </div>
                        <div class="about-feature">
                            <div class="about-feature-icon">
                                <svg viewBox="0 0 24 24" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                </svg>
                            </div>
                            <div class="about-feature-content">
                                <h4>5-Year Warranty</h4>
                                <p>Anti-rust guarantee included</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="about-image reveal" data-delay="0.2">
                    <div class="about-image-main">
                        <!-- Court Construction Image Placeholder - use actual image -->
                        <div style="width: 100%; height: 500px; background: linear-gradient(135deg, #0077B6 0%, #00B4D8 50%, #10B981 100%); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                            <!-- Court Pattern Overlay -->
                            <svg viewBox="0 0 400 300" style="position: absolute; width: 80%; opacity: 0.2;">
                                <rect x="20" y="20" width="360" height="260" fill="none" stroke="#fff" stroke-width="3" rx="4"/>
                                <line x1="200" y1="20" x2="200" y2="280" stroke="#fff" stroke-width="2"/>
                                <line x1="20" y1="150" x2="380" y2="150" stroke="#fff" stroke-width="2"/>
                                <rect x="20" y="80" width="80" height="140" fill="none" stroke="#fff" stroke-width="2"/>
                                <rect x="300" y="80" width="80" height="140" fill="none" stroke="#fff" stroke-width="2"/>
                                <circle cx="200" cy="150" r="40" fill="none" stroke="#fff" stroke-width="2"/>
                            </svg>
                            <span style="font-family: 'Bebas Neue', sans-serif; font-size: 3rem; color: rgba(255,255,255,0.9); position: relative; z-index: 1;">PADEL COURT</span>
                        </div>
                    </div>
                    <div class="about-image-accent">
                        <span class="about-image-accent-value">1200+</span>
                        <span class="about-image-accent-label">Courts Built</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services / Process Section -->
    <section class="section services" id="process">
        <div class="container">
            <div class="services-header reveal">
                <span class="subtitle services-label">Our Process</span>
                <h2 class="display-2 services-title">How We Build<br>Your Dream Court</h2>
                <p class="body-lg services-subtitle">
                    From concept to completion, we handle every aspect of your padel court project
                    with precision and care.
                </p>
            </div>

            <div class="process-timeline">
                <div class="process-step reveal" data-delay="0.1">
                    <div class="process-step-number">01</div>
                    <h3 class="process-step-title">Consultation</h3>
                    <p class="process-step-desc">
                        We assess your space, understand requirements, and provide expert recommendations
                        for the perfect court setup.
                    </p>
                </div>

                <div class="process-step reveal" data-delay="0.2">
                    <div class="process-step-number">02</div>
                    <h3 class="process-step-title">Design</h3>
                    <p class="process-step-desc">
                        Custom court designs with 3D visualizations, considering space optimization,
                        lighting, and player experience.
                    </p>
                </div>

                <div class="process-step reveal" data-delay="0.3">
                    <div class="process-step-number">03</div>
                    <h3 class="process-step-title">Manufacturing</h3>
                    <p class="process-step-desc">
                        Premium materials, precision engineering, and rigorous quality control
                        at our state-of-the-art facilities.
                    </p>
                </div>

                <div class="process-step reveal" data-delay="0.4">
                    <div class="process-step-number">04</div>
                    <h3 class="process-step-title">Installation</h3>
                    <p class="process-step-desc">
                        Professional installation by certified teams with post-sales support
                        and maintenance services.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio / Projects Section -->
    <section class="section portfolio" id="projects">
        <div class="container">
            <div class="portfolio-header reveal">
                <div>
                    <span class="subtitle portfolio-label">Our Portfolio</span>
                    <h2 class="heading-1 portfolio-title">Featured Projects</h2>
                </div>
                <a href="#contact" class="btn btn-dark">
                    Start Your Project
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="portfolio-grid">
                <div class="project-card reveal">
                    <div class="project-card-image">
                        <div style="width: 100%; height: 100%; background: linear-gradient(45deg, #003566, #0077B6); display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 100 75" style="width: 60%; opacity: 0.3;">
                                <rect x="5" y="5" width="90" height="65" fill="none" stroke="#fff" stroke-width="2" rx="2"/>
                                <line x1="50" y1="5" x2="50" y2="70" stroke="#fff" stroke-width="1.5"/>
                                <circle cx="50" cy="37.5" r="12" fill="none" stroke="#fff" stroke-width="1.5"/>
                            </svg>
                        </div>
                    </div>
                    <div class="project-card-overlay"></div>
                    <div class="project-card-content">
                        <span class="project-card-tag">Indoor Court</span>
                        <h3 class="project-card-title">Sports Complex Mumbai</h3>
                        <div class="project-card-location">
                            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Mumbai, India
                        </div>
                    </div>
                </div>

                <div class="project-card reveal" data-delay="0.1">
                    <div class="project-card-image">
                        <div style="width: 100%; height: 100%; background: linear-gradient(45deg, #047857, #10B981); display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 100 75" style="width: 60%; opacity: 0.3;">
                                <rect x="5" y="5" width="90" height="65" fill="none" stroke="#fff" stroke-width="2" rx="2"/>
                                <line x1="50" y1="5" x2="50" y2="70" stroke="#fff" stroke-width="1.5"/>
                                <circle cx="50" cy="37.5" r="12" fill="none" stroke="#fff" stroke-width="1.5"/>
                            </svg>
                        </div>
                    </div>
                    <div class="project-card-overlay"></div>
                    <div class="project-card-content">
                        <span class="project-card-tag">Outdoor Court</span>
                        <h3 class="project-card-title">Resort & Spa</h3>
                        <div class="project-card-location">
                            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Goa, India
                        </div>
                    </div>
                </div>

                <div class="project-card reveal" data-delay="0.2">
                    <div class="project-card-image">
                        <div style="width: 100%; height: 100%; background: linear-gradient(45deg, #0077B6, #00B4D8); display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 100 75" style="width: 60%; opacity: 0.3;">
                                <rect x="5" y="5" width="90" height="65" fill="none" stroke="#fff" stroke-width="2" rx="2"/>
                                <line x1="50" y1="5" x2="50" y2="70" stroke="#fff" stroke-width="1.5"/>
                                <circle cx="50" cy="37.5" r="12" fill="none" stroke="#fff" stroke-width="1.5"/>
                            </svg>
                        </div>
                    </div>
                    <div class="project-card-overlay"></div>
                    <div class="project-card-content">
                        <span class="project-card-tag">Multi-Court</span>
                        <h3 class="project-card-title">Elite Sports Academy</h3>
                        <div class="project-card-location">
                            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Bangalore, India
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features / Why Us Section -->
    <section class="section features" id="features">
        <div class="container">
            <div class="features-header reveal">
                <span class="subtitle features-label">Why Choose Us</span>
                <h2 class="heading-1 features-title">The Sky Padel Advantage</h2>
            </div>

            <div class="features-grid">
                <div class="feature-card reveal">
                    <div class="feature-card-icon">
                        <svg viewBox="0 0 24 24" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <polyline points="9 12 11 14 15 10"/>
                        </svg>
                    </div>
                    <h3 class="feature-card-title">Premium Quality</h3>
                    <p class="feature-card-desc">
                        European and USA quality standards with materials sourced from the best
                        manufacturers globally. Every court meets international specifications.
                    </p>
                </div>

                <div class="feature-card reveal" data-delay="0.1">
                    <div class="feature-card-icon">
                        <svg viewBox="0 0 24 24" stroke-width="2">
                            <path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                        </svg>
                    </div>
                    <h3 class="feature-card-title">Competitive Pricing</h3>
                    <p class="feature-card-desc">
                        Local manufacturing in India means competitive pricing without
                        compromising on quality. Get world-class courts at the best value.
                    </p>
                </div>

                <div class="feature-card reveal" data-delay="0.2">
                    <div class="feature-card-icon">
                        <svg viewBox="0 0 24 24" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <h3 class="feature-card-title">Quick Delivery</h3>
                    <p class="feature-card-desc">
                        Efficient manufacturing and logistics ensure your court is delivered
                        and installed within the promised timeline. No delays.
                    </p>
                </div>

                <div class="feature-card reveal" data-delay="0.3">
                    <div class="feature-card-icon">
                        <svg viewBox="0 0 24 24" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M3 9h18M9 21V9"/>
                        </svg>
                    </div>
                    <h3 class="feature-card-title">Custom Designs</h3>
                    <p class="feature-card-desc">
                        Every space is unique. We offer customized court designs that perfectly
                        fit your available area and aesthetic preferences.
                    </p>
                </div>

                <div class="feature-card reveal" data-delay="0.4">
                    <div class="feature-card-icon">
                        <svg viewBox="0 0 24 24" stroke-width="2">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <h3 class="feature-card-title">Full Documentation</h3>
                    <p class="feature-card-desc">
                        Complete documentation including certifications, warranty cards,
                        maintenance guides, and all compliance certificates.
                    </p>
                </div>

                <div class="feature-card reveal" data-delay="0.5">
                    <div class="feature-card-icon">
                        <svg viewBox="0 0 24 24" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                        </svg>
                    </div>
                    <h3 class="feature-card-title">Expert Support</h3>
                    <p class="feature-card-desc">
                        Dedicated support team and extensive partner network ensures you get
                        help whenever needed. Post-sales service across 50+ countries.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content reveal">
                <h2 class="display-2 cta-title">Ready to Build<br>Your Padel Court?</h2>
                <p class="cta-text">
                    Join 1200+ satisfied clients worldwide. Get a free consultation and
                    quote for your dream padel court today.
                </p>
                <div class="cta-actions">
                    <a href="#contact" class="btn btn-white btn-lg">
                        Get Free Quote
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="tel:<?php echo $spConfig['phone']; ?>" class="btn btn-outline-white btn-lg">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                        Call Now
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section contact" id="contact">
        <div class="container">
            <div class="contact-grid">
                <div class="contact-info reveal">
                    <span class="subtitle contact-info-label">Get In Touch</span>
                    <h2 class="heading-1 contact-info-title">Let's Discuss<br>Your Project</h2>
                    <p class="body-lg contact-info-text">
                        Have a project in mind? Contact us for a free consultation and quote.
                        Our team of experts is ready to help you build your dream padel court.
                    </p>

                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-item-icon">
                                <svg viewBox="0 0 24 24" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                                </svg>
                            </div>
                            <div class="contact-item-content">
                                <h4>Phone</h4>
                                <p><a href="tel:<?php echo $spConfig['phone']; ?>"><?php echo spFormatPhone($spConfig['phone']); ?></a></p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-item-icon">
                                <svg viewBox="0 0 24 24" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </div>
                            <div class="contact-item-content">
                                <h4>Email</h4>
                                <p><a href="mailto:<?php echo $spConfig['email']; ?>"><?php echo $spConfig['email']; ?></a></p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-item-icon">
                                <svg viewBox="0 0 24 24" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                            </div>
                            <div class="contact-item-content">
                                <h4>Location</h4>
                                <p><?php echo spClean($spConfig['address']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="contact-form-card reveal" data-delay="0.2">
                    <form id="contactForm">
                        <input type="hidden" name="xAction" value="INQUIRY">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Your Name *</label>
                                <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" placeholder="Mumbai">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Court Type</label>
                            <select name="court_type" class="form-control form-select">
                                <option value="">Select court type</option>
                                <option value="Indoor">Indoor Court</option>
                                <option value="Outdoor">Outdoor Court</option>
                                <option value="Panoramic">Panoramic Court</option>
                                <option value="Multiple">Multiple Courts</option>
                                <option value="Other">Other / Not Sure</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tell us about your project</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="Describe your requirements, available space, timeline, etc."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;" id="submitBtn">
                            Submit Inquiry
                            <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                            </svg>
                        </button>
                    </form>

                    <div id="formMessage" style="display: none; margin-top: var(--space-lg); padding: var(--space-md); border-radius: var(--radius-md); text-align: center;"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <div class="footer-logo-icon">
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/></svg>
                        </div>
                        <span class="footer-logo-text">SKY PADEL</span>
                    </div>
                    <p class="footer-brand-text">
                        India's leading padel court manufacturer. We design, manufacture and install
                        world-class padel courts with European quality standards.
                    </p>
                    <div class="footer-social">
                        <a href="<?php echo $spConfig['social']['facebook']; ?>" aria-label="Facebook">
                            <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                        </a>
                        <a href="<?php echo $spConfig['social']['instagram']; ?>" aria-label="Instagram">
                            <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><circle cx="17.5" cy="6.5" r="1.5"/></svg>
                        </a>
                        <a href="<?php echo $spConfig['social']['linkedin']; ?>" aria-label="LinkedIn">
                            <svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                        </a>
                        <a href="<?php echo $spConfig['social']['youtube']; ?>" aria-label="YouTube">
                            <svg viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.25 29 29 0 00-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/></svg>
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#process">Our Process</a></li>
                        <li><a href="#projects">Projects</a></li>
                        <li><a href="#features">Why Choose Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">Services</h4>
                    <ul class="footer-links">
                        <li><a href="#contact">Indoor Courts</a></li>
                        <li><a href="#contact">Outdoor Courts</a></li>
                        <li><a href="#contact">Panoramic Courts</a></li>
                        <li><a href="#contact">Court Installation</a></li>
                        <li><a href="#contact">Maintenance</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="footer-title">Contact Info</h4>
                    <ul class="footer-links">
                        <li><a href="tel:<?php echo $spConfig['phone']; ?>"><?php echo spFormatPhone($spConfig['phone']); ?></a></li>
                        <li><a href="mailto:<?php echo $spConfig['email']; ?>"><?php echo $spConfig['email']; ?></a></li>
                        <li><?php echo spClean($spConfig['address']); ?></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p class="footer-copyright">
                    &copy; <?php echo date('Y'); ?> <?php echo spClean($spConfig['company_name']); ?>. All rights reserved.
                </p>
                <div class="footer-legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo SP_ASSETS_URL; ?>js/skypadel.js"></script>
</body>
</html>
