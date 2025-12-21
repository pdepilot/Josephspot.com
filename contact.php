<?php
// Load appearance settings from database
require_once __DIR__ . '/includes/appearance_settings.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us | Joseph's Pot</title>
  <link rel="icon" href="<?php echo $appearance['favicon_path']; ?>?v=<?php echo time(); ?>">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- EmailJS -->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>

  <!-- Google reCAPTCHA -->
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>

  <!-- Google Maps API -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDqawMh1_TWjUGPFbPvOl39eYYNvKvX5eM&callback=initMap" async defer></script>

  <style>
    /* CSS Starts Here */
    /* Override CSS variables - using :root */
    :root {
      --brown: <?php echo $appearance['primary_color']; ?>;
      --brown-light: <?php echo $appearance['primary_light']; ?>;
      --brown-dark: <?php echo $appearance['primary_dark']; ?>;
      --white: #ffffff;
      --pale-orange: #ffe4b5;
      --pale-orange-light: #fff8dc;
      --accent: #d2691e;
      --text: #333333;
      --text-light: #666666;
      --shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s ease;
      --error: #e74c3c;
      --success: #27ae60;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      background-color: var(--pale-orange);
      color: var(--text);
      line-height: 1.6;
    }

    /* Navbar Styles */
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      padding: 15px 0;
      background: var(--pale-orange);
      z-index: 1000;
      border-bottom: 2px solid var(--brown);
      box-shadow: var(--shadow);
      transition: var(--transition);
    }

    .navbar.scrolled {
      padding: 10px 0;
      background: var(--brown);
    }

    .navbar.scrolled .nav-links a {
      color: var(--white);
    }

    .navbar.scrolled .social a {
      color: var(--white);
    }

    .navbar.scrolled .menu-toggle {
      color: var(--white);
    }

    .containerr {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .logo img {
      height: 50px;
      transition: var(--transition);
    }

    .navbar.scrolled .logo img {
      height: 40px;
      filter: brightness(0) invert(1);
    }

    .nav-links {
      display: flex;
      gap: 25px;
    }

    .nav-links a {
      color: var(--brown);
      text-decoration: none;
      font-weight: 600;
      font-size: 16px;
      position: relative;
      padding: 8px 0;
      transition: var(--transition);
    }

    .nav-links a:hover {
      color: var(--accent);
    }

    .nav-links a::after {
      content: "";
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      left: 0;
      background-color: var(--brown);
      transition: var(--transition);
    }

    .nav-links a.active {
      color: var(--brown-dark);
    }

    .nav-links a.active::after,
    .nav-links a:hover::after {
      width: 100%;
    }

    .social {
      display: flex;
      gap: 15px;
    }

    .social a {
      color: var(--brown);
      font-size: 18px;
      transition: var(--transition);
    }

    .social a:hover {
      color: var(--accent);
      transform: translateY(-3px);
    }

    .menu-toggle {
      display: none;
      font-size: 24px;
      cursor: pointer;
      color: var(--brown);
      transition: var(--transition);
    }

    .menu-toggle:hover {
      color: var(--accent);
    }

    /* Header/Hero Section */
    main {
      margin-top: 90px;
      background: linear-gradient(to bottom,
          rgba(139, 69, 19, 0.85),
          rgba(101, 67, 33, 0.75)),
        url("./images/instagram image4.jpg") center/cover no-repeat fixed;
      color: white;
      padding: 60px 20px;
      text-align: center;
      position: relative;
    }

    main h1 {
      font-size: 3.5rem;
      margin-bottom: 20px;
      font-family: 'Poppins', sans-serif;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    main p {
      font-size: 1.8rem;
      opacity: 0.95;
      color: var(--pale-orange);
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    }

    /* Main Container */
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    /* Contact Container */
    .contact-container {
      display: flex;
      flex-wrap: wrap;
      gap: 40px;
      margin: 60px auto;
      background: var(--white);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: var(--shadow);
      border: 2px solid var(--brown);
      max-width: 1200px;
    }

    /* Contact Info Section */
    .contact-info {
      flex: 1;
      min-width: 300px;
      padding: 40px;
      background: linear-gradient(135deg, var(--brown-dark), var(--brown));
      color: var(--white);
    }

    .contact-info h2 {
      font-size: 2rem;
      margin-bottom: 30px;
      color: var(--white);
    }

    .info-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 30px;
    }

    .info-item i {
      font-size: 1.5rem;
      margin-right: 20px;
      color: var(--pale-orange);
      min-width: 30px;
    }

    .info-text h3 {
      font-size: 1.3rem;
      margin-bottom: 8px;
      color: var(--pale-orange);
    }

    .info-text p {
      color: var(--pale-orange-light);
      line-height: 1.8;
    }

    /* Map Container - Updated Styles */
    .map-container {
      margin-top: 15px;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      height: 200px;
      position: relative;
      transition: var(--transition);
      cursor: pointer;
    }

    .map-container:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    }

    .map-container::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to bottom, transparent 70%, rgba(139, 69, 19, 0.1) 100%);
      pointer-events: none;
      border-radius: 10px;
    }

    #map {
      width: 100%;
      height: 100%;
      transition: var(--transition);
    }

    .map-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(139, 69, 19, 0.1);
      opacity: 0;
      transition: var(--transition);
      z-index: 2;
      pointer-events: none;
      border-radius: 10px;
    }

    .map-container:hover .map-overlay {
      opacity: 1;
      background: rgba(139, 69, 19, 0.05);
    }

    .map-click-instruction {
      background: rgba(255, 255, 255, 0.95);
      padding: 10px 20px;
      border-radius: 50px;
      font-size: 0.9rem;
      color: var(--brown);
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      transform: translateY(10px);
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .map-container:hover .map-click-instruction {
      transform: translateY(0);
    }

    .map-click-instruction i {
      color: var(--accent);
    }

    .social-links {
      display: flex;
      gap: 20px;
      margin-top: 15px;
    }

    .social-links a {
      color: var(--white);
      font-size: 1.3rem;
      transition: var(--transition);
      width: 40px;
      height: 40px;
      border-radius: 50%;
      /* background: rgba(255, 255, 255, 0.1); */
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
    }

    .social-links a:hover {
      color: var(--brown);
      /* background: var(--pale-orange); */
      transform: translateY(-5px);
    }

    /* Contact Form Section */
    .contact-form {
      flex: 1;
      min-width: 300px;
      padding: 40px;
      background: var(--white);
    }

    .contact-form h2 {
      font-size: 2rem;
      margin-bottom: 30px;
      color: var(--brown);
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      display: block;
      margin-bottom: 10px;
      font-weight: 600;
      color: var(--brown);
      font-size: 0.95rem;
    }

    .form-control {
      width: 100%;
      padding: 15px 20px;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      font-size: 1rem;
      transition: var(--transition);
      background: var(--pale-orange-light);
    }

    .form-control:focus {
      border-color: var(--accent);
      outline: none;
      box-shadow: 0 0 0 3px rgba(210, 105, 30, 0.1);
      background: var(--white);
    }

    textarea.form-control {
      min-height: 180px;
      resize: vertical;
    }

    .submit-btn {
      background: linear-gradient(135deg, var(--brown), var(--brown-dark));
      color: var(--white);
      border: none;
      padding: 16px 40px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: var(--transition);
      width: 100%;
    }

    .submit-btn:hover {
      background: linear-gradient(135deg, var(--brown-dark), var(--brown));
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(139, 69, 19, 0.2);
    }

    .submit-btn.loading {
      opacity: 0.8;
      cursor: not-allowed;
      color: transparent;
      position: relative;
    }

    .submit-btn.loading::after {
      content: '';
      position: absolute;
      left: 50%;
      top: 50%;
      width: 24px;
      height: 24px;
      border: 3px solid white;
      border-top-color: transparent;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      transform: translate(-50%, -50%);
    }

    @keyframes spin {
      to {
        transform: translate(-50%, -50%) rotate(360deg);
      }
    }

    .g-recaptcha {
      margin: 25px 0;
      transform: scale(0.95);
      transform-origin: 0 0;
    }

    .status-message {
      padding: 15px 20px;
      margin: 20px 0;
      border-radius: 8px;
      display: none;
      animation: slideIn 0.3s ease;
      border: 1px solid transparent;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .status-message.success {
      background-color: rgba(39, 174, 96, 0.1);
      color: var(--success);
      border-color: rgba(39, 174, 96, 0.2);
      display: block;
    }

    .status-message.error {
      background-color: rgba(231, 76, 60, 0.1);
      color: var(--error);
      border-color: rgba(231, 76, 60, 0.2);
      display: block;
    }

    /* Footer Styles */
    .footer {
      background: var(--brown);
      color: var(--white);
      padding: 80px 20px 40px;
      margin-top: 80px;
    }

    .footer-glass {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 50px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .footer-content {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 40px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .footer-column {
      flex: 1 1 250px;
    }

    .footer-column img {
      margin-bottom: 20px;
      border-radius: 10px;
    }

    .footer-column h3,
    .footer-column h4 {
      margin-bottom: 25px;
      color: var(--white);
      font-size: 1.4rem;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .footer-column ul {
      list-style: none;
      padding: 0;
    }

    .footer-column ul li {
      margin-bottom: 15px;
    }

    .footer-column ul li a {
      color: var(--pale-orange);
      text-decoration: none;
      transition: var(--transition);
    }

    .footer-column ul li a:hover {
      color: var(--white);
    }

    .footer-column p,
    .footer-column a {
      color: var(--pale-orange);
      font-size: 1rem;
      line-height: 1.8;
      text-decoration: none;
    }

    .footer-bottom {
      text-align: center;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
      margin-top: 50px;
      padding-top: 30px;
      font-size: 0.95rem;
      color: var(--pale-orange);
      max-width: 1200px;
      margin: 50px auto 0;
    }

    .footer-social-links {
      display: flex;
      gap: 20px;
      margin-top: 25px;
    }

    .footer-social-links a {
      color: var(--white);
      font-size: 1.3rem;
      transition: var(--transition);
      background: rgba(255, 255, 255, 0.1);
      width: 45px;
      height: 45px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .footer-social-links a:hover {
      color: var(--brown);
      background: var(--pale-orange);
      transform: translateY(-5px);
    }

    /* WhatsApp Chat Bubble */
    .whatsapp-chat {
      position: fixed;
      bottom: 30px;
      right: 30px;
      z-index: 999;
      background: linear-gradient(135deg, #25d366, #128c7e);
      border-radius: 50%;
      width: 65px;
      height: 65px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 10px 25px rgba(37, 211, 102, 0.4);
      animation: pulse 2s infinite;
      transition: var(--transition);
      text-decoration: none;
    }

    @keyframes pulse {
      0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
      }

      70% {
        transform: scale(1.05);
        box-shadow: 0 0 0 15px rgba(37, 211, 102, 0);
      }

      100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
      }
    }

    .whatsapp-chat:hover {
      transform: scale(1.1);
      animation: none;
    }

    .whatsapp-chat img {
      width: 35px;
      height: 35px;
    }

    /* Scroll To Top Button */
    #scrollTopBtn {
      position: fixed;
      bottom: 110px;
      right: 30px;
      background: linear-gradient(135deg, var(--brown), var(--brown-dark));
      color: var(--white);
      border: none;
      width: 55px;
      height: 55px;
      border-radius: 50%;
      font-size: 1.8rem;
      cursor: pointer;
      z-index: 998;
      display: none;
      transition: var(--transition);
      box-shadow: 0 8px 20px rgba(139, 69, 19, 0.3);
    }

    #scrollTopBtn:hover {
      transform: translateY(-5px);
      background: linear-gradient(135deg, var(--brown-dark), var(--brown));
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .nav-links {
        position: fixed;
        top: 80px;
        left: -100%;
        width: 100%;
        height: calc(100vh - 85px);
        background: var(--brown);
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        gap: 30px;
        transition: var(--transition);
        z-index: 999;
        padding-top: 50px;
      }

      .nav-links.active {
        left: 0;
      }

      .nav-links a {
        color: var(--white);
        font-size: 1.2rem;
        padding: 15px 0;
      }

      .menu-toggle {
        display: block;
      }

      .social {
        display: none;
      }

      main {
        margin-top: 80px;
        padding: 80px 20px;
      }

      main h1 {
        font-size: 2.5rem;
      }

      main p {
        font-size: 1.4rem;
      }

      .contact-container {
        flex-direction: column;
        margin: 30px 15px;
        border-radius: 15px;
      }

      .contact-info,
      .contact-form {
        padding: 30px;
      }

      .map-container {
        height: 180px;
      }

      .footer-glass {
        padding: 30px 20px;
      }

      .footer-content {
        flex-direction: column;
        gap: 40px;
      }

      #scrollTopBtn {
        bottom: 120px;
        right: 20px;
        width: 50px;
        height: 50px;
      }

      .whatsapp-chat {
        bottom: 25px;
        right: 25px;
        width: 60px;
        height: 60px;
      }
    }

    @media (max-width: 480px) {
      main {
        margin-top: 70px;
        padding: 60px 15px;
      }

      main h1 {
        font-size: 2rem;
      }

      main p {
        font-size: 1.2rem;
      }

      .contact-info,
      .contact-form {
        padding: 20px;
      }

      .map-container {
        height: 160px;
      }

      .map-click-instruction {
        font-size: 0.8rem;
        padding: 8px 15px;
      }

      .footer {
        padding: 60px 15px 30px;
      }

      .g-recaptcha {
        transform: scale(0.85);
        transform-origin: 0 0;
      }
    }
  </style>
</head>

<body>
  <!-- Navigation Bar -->
  <header class="navbar" id="navbar">
    <div class="containerr">
      <div class="logo">
        <a href="index.php"><img src="<?php echo $appearance['logo_path']; ?>?v=<?php echo time(); ?>" alt="Joseph's Pot Logo"></a>
      </div>
      <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="menu.php">Menu</a>
        <a href="gallery.php">Gallery</a>
        <a href="index.php#eventContainer">Events</a>
        <a href="contact.php" class="active">Contact</a>
        <a href="order-online.php">Order Online</a>
      </nav>
      <div class="social">
        <a href="https://www.facebook.com/cruisewithjoe" target="_blank"><i class="fa-brands fa-facebook"></i></a>
        <a href="https://www.twitter.com/cruisewithjoe" target="_blank"><i class="fa-brands fa-x-twitter"></i></a>
        <a href="https://www.youtube.com/cruisewithjoe" target="_blank"><i class="fab fa-youtube"></i></a>
        <a href="https://www.instagram.com/cruisewithjoe" target="_blank"><i class="fab fa-instagram"></i></a>
      </div>
      <span class="menu-toggle" onclick="toggleMenu()"><i class="fa-solid fa-utensils"></i></span>
    </div>
  </header>
  <a href="admin/admin-login.php" target="_blank">Admin Panel</a>

  <!-- Hero Section -->
  <main>
    <div class="container">
      <h1>Contact Us</h1>
      <p>We'd love to hear from you</p>
    </div>
  </main>

  <!-- Contact Content -->
  <div class="container">
    <div class="contact-container">
      <div class="contact-info">
        <h2>Get In Touch</h2>

        <div class="info-item">
          <i class="fas fa-map-marker-alt"></i>
          <div class="info-text">
            <h3>Our Restaurant</h3>
            <p>
              120, Ikenegbu Layout<br>
              By Cherobim Junction<br>
              Owerri, Imo State<br>
              Nigeria
            </p>
            <div class="map-container" id="mapRedirect">
              <div id="map"></div>
              <div class="map-overlay">
                <div class="map-click-instruction">
                  <i class="fas fa-external-link-alt"></i> Click to open in Google Maps
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="info-item">
          <i class="fas fa-phone-alt"></i>
          <div class="info-text">
            <h3>Call Us</h3>
            <p>+234 810 434 4994</p>
            <p>Mon-Sun: 8:30am - 9pm</p>
          </div>
        </div>

        <div class="info-item">
          <i class="fas fa-envelope"></i>
          <div class="info-text">
            <h3>Email Us</h3>
            <p>info@josephspot.com</p>
            <p>orders@josephspot.com</p>
          </div>
        </div>

        <div class="info-item">
          <i class="fas fa-share-alt"></i>
          <div class="info-text">
            <h3>Follow Us</h3>
            <div class="social-links">
              <a href="https://www.facebook.com/cruisewithjoe" target="_blank"><i class="fa-brands fa-facebook"></i></a>
              <!-- <a href="https://www.twitter.com/cruisewithjoe" target="_blank"><i class="fa-brands fa-x-twitter"></i></a> -->
              <a href="https://www.youtube.com/cruisewithjoe" target="_blank"><i class="fab fa-youtube"></i></a>
              <a href="https://www.instagram.com/cruisewithjoe" target="_blank"><i class="fab fa-instagram"></i></a>
            </div>
          </div>
        </div>
      </div>

      <div class="contact-form">
        <h2>Send Us a Message</h2>
        <div id="status-message" class="status-message"></div>
        <form id="contactForm" novalidate>
          <div class="form-group">
            <label for="name">Your Name *</label>
            <input
              type="text"
              id="name"
              name="name"
              class="form-control"
              required
              placeholder="Enter your full name" />
          </div>

          <div class="form-group">
            <label for="email">Email Address *</label>
            <input
              type="email"
              id="email"
              name="email"
              class="form-control"
              required
              placeholder="your.email@example.com" />
          </div>

          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input
              type="tel"
              id="phone"
              name="phone"
              class="form-control"
              placeholder="+234 800 000 0000" />
          </div>

          <div class="form-group">
            <label for="subject">Subject *</label>
            <select id="subject" name="subject" class="form-control" required>
              <option value="">Select a subject</option>
              <option value="General Inquiry">General Inquiry</option>
              <option value="Reservation">Table Reservation</option>
              <option value="Custom Order">Custom Order</option>
              <option value="Wholesale Inquiry">Wholesale Inquiry</option>
              <option value="Events Inquiry">Events Inquiry</option>
              <option value="Feedback">Feedback & Suggestions</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="form-group">
            <label for="message">Your Message *</label>
            <textarea
              id="message"
              name="message"
              class="form-control"
              required
              rows="6"
              placeholder="Type your message here..."></textarea>
          </div>

          <div class="g-recaptcha" data-sitekey="6LeKVsQrAAAAAB4t3tCIo_-3TT0HXUguiqBrrSHI"></div>

          <button type="submit" class="submit-btn" id="submit-btn">
            Send Message
          </button>

          <p style="margin-top: 15px; font-size: 0.9rem; color: var(--text-light);">
            We typically respond within 24 hours.
          </p>
        </form>
      </div>
    </div>
  </div>

  <!-- Footer Section -->
  <footer class="footer">
    <div class="footer-glass">
      <div class="footer-glass-inner">
        <div class="footer-content">
          <div class="footer-column">
            <img src="<?php echo $appearance['logo_path']; ?>?v=<?php echo time(); ?>" alt="Joseph's Pot Logo" width="80px" />
            <p>
              Authentic taste, unforgettable experience.<br>
              Serving happiness from Owerri, Nigeria.
            </p>
            <div class="footer-social-links">
              <a href="https://facebook.com/cruisewithjoe" target="_blank"><i class="fab fa-facebook-f"></i></a>
              <a href="https://instagram.com/cruisewithjoe" target="_blank"><i class="fab fa-instagram"></i></a>
              <a href="https://twitter.com/cruisewithjoe" target="_blank"><i class="fab fa-twitter"></i></a>
              <a href="https://youtube.com/cruisewithjoe" target="_blank"><i class="fab fa-youtube"></i></a>
            </div>
          </div>

          <div class="footer-column">
            <h4>Quick Links</h4>
            <ul>
              <li><a href="index.php">Home</a></li>
              <li><a href="about.php">About</a></li>
              <li><a href="menu.php">Menu</a></li>
              <li><a href="gallery.php">Gallery</a></li>
              <li><a href="order-online.php">Order Online</a></li>
            </ul>
          </div>

          <div class="footer-column">
            <h4><i class="fas fa-clock"></i> Opening Hours</h4>
            <p>
              Monday – Friday: 08:30 AM – 9:00 PM<br>
              Saturday: 08:00 AM – 09:00 PM<br>
              Sunday: 12:00 PM – 09:00 PM
            </p>
          </div>

          <div class="footer-column">
            <h4><i class="fas fa-map-marker-alt"></i> Visit Us</h4>
            <p>
              Plot 120, Ikenegbu Layout<br>
              By Maris Junction, Owerri<br>
              Imo State, Nigeria<br>
              <a href="https://maps.google.com?q=Joseph's+Pot+Owerri+Nigeria" target="_blank">
                <span>📍 Get Directions</span>
              </a>
            </p>
          </div>
        </div>

        <div class="footer-bottom">
          <p>
            &copy; <span id="year"></span> Joseph's Pot. All rights reserved |
            Developed by ERIBS Tech
          </p>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scroll To Top Button -->
  <button id="scrollTopBtn" aria-label="Scroll to Top">
    <i class="fas fa-arrow-up"></i>
  </button>

  <!-- WhatsApp Chat Bubble -->
  <a href="#" class="whatsapp-chat" id="whatsappButton">
    <img src="https://cdn-icons-png.flaticon.com/512/124/124034.png" alt="WhatsApp" />
  </a>

  <!-- JavaScript Starts Here -->
<script>
  // ============================================
  // DOM CONTENT LOADED - MAIN INITIALIZATION
  // ============================================
  document.addEventListener("DOMContentLoaded", function () {
    console.log("Document loaded successfully");
    
    // ============================================
    // FOOTER YEAR UPDATE
    // ============================================
    document.getElementById("year").textContent = new Date().getFullYear();

    // ============================================
    // EMAILJS INITIALIZATION
    // ============================================
    emailjs.init("4mVvhX_iatqJbQ5iy");
    console.log("EmailJS initialized");
    // ========== END EMAILJS INITIALIZATION ==========

    // ============================================
    // NAVBAR SCROLL EFFECT
    // ============================================
    window.addEventListener('scroll', function() {
      const navbar = document.getElementById('navbar');
      if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    });
    // ========== END NAVBAR SCROLL EFFECT ==========

    // ============================================
    // MOBILE MENU TOGGLE
    // ============================================
    window.toggleMenu = function() {
      const navLinks = document.querySelector('.nav-links');
      navLinks.classList.toggle('active');
      console.log("Menu toggled");
    }

    // Close mobile menu when clicking on a link
    document.querySelectorAll('.nav-links a').forEach(link => {
      link.addEventListener('click', () => {
        const navLinks = document.querySelector('.nav-links');
        navLinks.classList.remove('active');
      });
    });
    // ========== END MOBILE MENU TOGGLE ==========

    // ============================================
    // SCROLL TO TOP BUTTON
    // ============================================
    const scrollBtn = document.getElementById("scrollTopBtn");
    window.addEventListener('scroll', function() {
      if (window.scrollY > 300) {
        scrollBtn.style.display = "flex";
        scrollBtn.style.alignItems = "center";
        scrollBtn.style.justifyContent = "center";
      } else {
        scrollBtn.style.display = "none";
      }
    });

    scrollBtn.addEventListener('click', function() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    // ========== END SCROLL TO TOP BUTTON ==========

    // ============================================
    // WHATSAPP FUNCTIONALITY
    // ============================================
    const whatsappButton = document.querySelector('.whatsapp-chat');
    whatsappButton.addEventListener('click', function(e) {
      e.preventDefault();
      const message = encodeURIComponent("Hello Joseph's Pot! I'd like to get more information about your restaurant.");
      const whatsappURL = `https://wa.me/2348104344994?text=${message}`;
      window.open(whatsappURL, '_blank');
      console.log("WhatsApp clicked");
    });
    // ========== END WHATSAPP FUNCTIONALITY ==========

    // ============================================
    // MAP REDIRECT FUNCTIONALITY
    // ============================================
    const mapContainer = document.getElementById('mapRedirect');
    if (mapContainer) {
      mapContainer.addEventListener('click', function() {
        const address = "120, Ikenegbu Layout, By Cherobim Junction, Owerri, Imo State, Nigeria";
        const googleMapsURL = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`;
        window.open(googleMapsURL, '_blank');
        console.log("Map clicked, redirecting to Google Maps");
      });
    }
    // ========== END MAP REDIRECT FUNCTIONALITY ==========

    // ============================================
    // CONTACT FORM SUBMISSION HANDLER - FINAL CLEAN VERSION
    // ============================================
    const contactForm = document.getElementById("contactForm");
    const statusMessage = document.getElementById("status-message");
    const submitBtn = document.getElementById("submit-btn");

    if (contactForm) {
      contactForm.addEventListener("submit", async function (event) {
        event.preventDefault();
        
        // Get form values
        const name = document.getElementById("name").value.trim();
        const email = document.getElementById("email").value.trim();
        const subject = document.getElementById("subject").value;
        const message = document.getElementById("message").value.trim();
        const phone = document.getElementById("phone").value.trim() || "Not provided";
        
        // Validation
        if (!name || !email || !subject || !message) {
          showStatus("Please fill in all required fields.", "error");
          return;
        }
        
        // Validate reCAPTCHA
        const recaptchaResponse = grecaptcha.getResponse();
        if (!recaptchaResponse) {
          showStatus("Please complete the reCAPTCHA verification.", "error");
          return;
        }
        
        // Disable button during submission
        submitBtn.disabled = true;
        submitBtn.textContent = "Sending...";
        submitBtn.classList.add('loading');
        
        // Prepare data
        const formData = {
          name: name,
          email: email,
          phone: phone,
          subject: subject,
          message: message
        };
        
        try {
          // Step 1: Save to database
          const dbResponse = await fetch('save-contact.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
          });
          
          const dbResult = await dbResponse.json();
          
          if (!dbResult.success) {
            throw new Error('Failed to save message to database');
          }
          
          // Step 2: Send email notification (optional)
          try {
            await emailjs.send(
              "service_te9k19v",
              "template_b4ywg0q",
              {
                ...formData,
                "g-recaptcha-response": recaptchaResponse
              }
            );
            
            showStatus(`Thank you, ${name}! Your message has been sent successfully. We'll get back to you within 24 hours.`, "success");
          } catch (emailError) {
            // Database saved but email failed - still show success
            console.warn("Email notification failed:", emailError);
            showStatus(`Thank you, ${name}! Your message has been saved successfully. (Email notification failed)`, "warning");
          }
          
          // Reset form on success
          contactForm.reset();
          grecaptcha.reset();
          
        } catch (error) {
          console.error("Submission error:", error);
          showStatus("Sorry, there was an error submitting your message. Please try again or contact us directly.", "error");
        } finally {
          // Re-enable button
          submitBtn.disabled = false;
          submitBtn.textContent = "Send Message";
          submitBtn.classList.remove('loading');
        }
      });
    }
    // ========== END CONTACT FORM SUBMISSION ==========

    // ============================================
    // STATUS MESSAGE DISPLAY FUNCTION
    // ============================================
    function showStatus(message, type) {
      const statusElement = document.getElementById("status-message");
      statusElement.textContent = message;
      statusElement.className = "status-message " + type;
    }
    // ========== END STATUS MESSAGE FUNCTION ==========

    // ============================================
    // GOOGLE MAP INITIALIZATION
    // ============================================
    if (document.getElementById('map')) {
      initMap();
    }
  });
  // ========== END DOM CONTENT LOADED ==========

  // ============================================
  // GOOGLE MAPS INITIALIZATION FUNCTION
  // ============================================
  function initMap() {
    console.log("Initializing Google Map");
    
    try {
      // Owerri, Nigeria coordinates (Ikenegbu Layout area)
      const restaurantLocation = { lat: 5.492, lng: 7.032 };
      
      const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 16,
        center: restaurantLocation,
        mapTypeId: 'roadmap',
        styles: [
          {
            featureType: "all",
            elementType: "geometry",
            stylers: [{ color: "#f5f5dc" }]
          },
          {
            featureType: "poi",
            elementType: "labels",
            stylers: [{ visibility: "off" }]
          },
          {
            featureType: "road",
            elementType: "geometry",
            stylers: [{ color: "#f0e6d2" }]
          },
          {
            featureType: "road",
            elementType: "labels.text.fill",
            stylers: [{ color: "#8b4513" }]
          },
          {
            featureType: "water",
            elementType: "geometry",
            stylers: [{ color: "#e6d9c8" }]
          }
        ],
        disableDefaultUI: true,
        zoomControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        mapTypeControl: false
      });

      // Custom marker icon
      const markerIcon = {
        url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
        scaledSize: new google.maps.Size(40, 40),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(20, 40)
      };

      // Create marker
      const marker = new google.maps.Marker({
        position: restaurantLocation,
        map: map,
        title: "Joseph's Pot Restaurant",
        icon: markerIcon,
        animation: google.maps.Animation.DROP
      });

      // Add click event to marker to also redirect
      marker.addListener('click', function() {
        const address = "120, Ikenegbu Layout, By Cherobim Junction, Owerri, Imo State, Nigeria";
        const googleMapsURL = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}`;
        window.open(googleMapsURL, '_blank');
      });

      // Add info window
      const infoWindow = new google.maps.InfoWindow({
        content: `
          <div style="padding: 10px;">
            <h3 style="margin: 0 0 5px 0; color: #8b4513;">Joseph's Pot Restaurant</h3>
            <p style="margin: 0; color: #666;">Ikenegbu Layout, Owerri<br>Click for directions</p>
          </div>
        `
      });

      marker.addListener('mouseover', function() {
        infoWindow.open(map, marker);
      });

      marker.addListener('mouseout', function() {
        infoWindow.close();
      });

      console.log("Google Map initialized successfully");
      
    } catch (error) {
      console.error("Google Maps error:", error);
      // Fallback if Google Maps fails
      const mapElement = document.getElementById('map');
      if (mapElement) {
        mapElement.innerHTML = `
          <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #f8f0e3; color: #8b4513; padding: 20px; text-align: center; cursor: pointer;">
            <div>
              <i class="fas fa-map-marker-alt" style="font-size: 2rem; margin-bottom: 10px;"></i>
              <p style="margin: 0 0 10px 0; font-weight: bold;">Joseph's Pot Restaurant</p>
              <p style="margin: 0; font-size: 0.9rem;">Ikenegbu Layout, Owerri</p>
              <p style="margin: 10px 0 0 0; font-size: 0.8rem; color: #a0522d;">
                <i class="fas fa-external-link-alt"></i> Click to open in Google Maps
              </p>
            </div>
          </div>
        `;
      }
    }
  }
  // ========== END GOOGLE MAPS FUNCTION ==========

  // ============================================
  // GOOGLE MAPS AUTHENTICATION ERROR HANDLER
  // ============================================
  window.gm_authFailure = function() {
    console.error("Google Maps authentication failed");
    const mapElement = document.getElementById('map');
    if (mapElement) {
      mapElement.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #f8f0e3; color: #8b4513; padding: 20px; text-align: center; cursor: pointer;">
          <div>
            <i class="fas fa-map-marker-alt" style="font-size: 2rem; margin-bottom: 10px;"></i>
            <p style="margin: 0 0 10px 0; font-weight: bold;">Joseph's Pot Restaurant</p>
            <p style="margin: 0; font-size: 0.9rem;">Ikenegbu Layout, Owerri</p>
            <p style="margin: 10px 0 0 0; font-size: 0.8rem; color: #a0522d;">
              <i class="fas fa-external-link-alt"></i> Click to open in Google Maps
            </p>
          </div>
        </div>
      `;
    }
  };
  // ========== END GOOGLE MAPS ERROR HANDLER ==========
</script>
<!-- JavaScript Ends Here -->
  <script>
    // Force CSS variable update after page load (ensures override of static CSS)
    (function() {
        const root = document.documentElement;
        root.style.setProperty('--brown', '<?php echo $appearance['primary_color']; ?>', 'important');
        root.style.setProperty('--brown-light', '<?php echo $appearance['primary_light']; ?>', 'important');
        root.style.setProperty('--brown-dark', '<?php echo $appearance['primary_dark']; ?>', 'important');
    })();
  </script>
</body>

</html>