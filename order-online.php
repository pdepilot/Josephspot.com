<?php
// Load appearance settings from database
require_once __DIR__ . '/includes/appearance_settings.php';

// Load restaurant information from database
require_once __DIR__ . '/includes/restaurant_info.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order Online | <?php echo !empty($restaurant_info['restaurant_name']) ? htmlspecialchars($restaurant_info['restaurant_name']) : "Joseph's Pot"; ?></title>
    
    <!-- FIXED: Correct FontAwesome path -->
    <link
      rel="stylesheet"
      href="./fontawesome-free-6.7.2-web/css/all.min.css"
      onerror="console.error('Failed to load FontAwesome');"
    />
    
    <!-- <link rel="preload" href="font.woff2" as="font" type="font/woff2" crossorigin> -->
     <link rel="icon" href="<?php echo $appearance['favicon_path']; ?>?v=<?php echo time(); ?>">
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap"
      rel="stylesheet"
      onerror="console.error('Failed to load Google Fonts');"
    />
    <link rel="stylesheet" href="./CSS/order-online.css">
    <!-- Note: QRCode and html2pdf scripts moved to body for better loading -->
    <!-- Dynamic Theme Colors (must come after CSS to override) -->
    <style id="dynamic-theme-colors">
        /* Override CSS variables - using :root */
        :root {
            --brown: <?php echo $appearance['primary_color']; ?>;
            --brown-light: <?php echo $appearance['primary_light']; ?>;
            --brown-dark: <?php echo $appearance['primary_dark']; ?>;
        }
    </style>
    <script>
        // Force CSS variable update after page load (ensures override of static CSS)
        (function() {
            const root = document.documentElement;
            root.style.setProperty('--brown', '<?php echo $appearance['primary_color']; ?>', 'important');
            root.style.setProperty('--brown-light', '<?php echo $appearance['primary_light']; ?>', 'important');
            root.style.setProperty('--brown-dark', '<?php echo $appearance['primary_dark']; ?>', 'important');
        })();
    </script>
  </head>
  <body>

   <!-- Navbar -->
    <header class="navbar" id="navbar">
      <div class="containerr">
        <div class="logo">
          <a href=""><img src="<?php echo $appearance['logo_path']; ?>?v=<?php echo time(); ?>" alt="logo" /></a>
        </div>
        <nav class="nav-links">
          <a href="index.php">Home</a>
          <a href="about.php">About</a>
          <a href="menu.php">Menu</a>
          <a href="gallery.php">Gallery</a>
          <a href="index.php#eventContainer">Events</a>
          <a href="contact.php">Contact</a>
          <a href="order-online.php" class="active">Order Online</a>
          <a href="./career.php">Career</a>
        </nav>

        <div class="cart-icon-container" id="cartIconContainer">
          <i class="fas fa-shopping-cart" id="cartIcon"></i>
          <span class="cart-count">0</span>
        </div>
        <div class="social">
          <a href="https://www.facebook.com/@cruisewithjoe"
            ><i class="fa-brands fa-facebook"></i
          ></a>
          <a href="https://www.x.com/@cruisewithjoe"><i class="fa-brands fa-x-twitter"></i></a>
          <a href="https://www.youtube.com/@cruisewithjoe"
            ><i class="fab fa-youtube"></i
          ></a>
          <a href="https://www.instagram.com/@cruisewithjoe"
            ><i class="fab fa-instagram"></i
          ></a>
        </div>


        <span class="menu-toggle" id="menuToggle"
          ><i class="fa-solid fa-utensils"></i
        ></span>
      </div>
    </header>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Empty Cart Prompt -->
    <div class="empty-cart-prompt" id="emptyCartPrompt">
      <div class="empty-cart-container">
        <i class="fas fa-shopping-cart"></i>
        <h3>Your Cart is Empty</h3>
        <p>Please add items to your cart before proceeding to checkout.</p>
        <div class="empty-cart-actions">
          <button class="btn" id="closeEmptyCart">Close</button>
          <button class="btn" id="browseMenu">Browse Menu</button>
        </div>
      </div>
    </div>

    <main class="main-content">
      <section class="hero">
        <div class="container">
          <h2>Igbo Cultural Cuisines Crafted with Passion</h2>
          <p>
            Experience the perfect blend of tradition and innovation in every
            bite
          </p>
          <!-- FIXED: Added onclick handler for CTA button -->
          <a href="#menu" class="btn" id="exploreMenuBtn">Explore Menu</a>
        </div>
      </section>

      <section id="menu" class="menu-section">
        <div class="container">
          <h2 class="section-title">Our Menu</h2>
          <div class="category-buttons">
            <button class="category-btn active" data-category="all">All</button>
            <button class="category-btn" data-category="soups">Soups</button>
            <button class="category-btn" data-category="starters">Starters</button>
            <button class="category-btn" data-category="main courses">
              Main Courses
            </button>
            <button class="category-btn" data-category="noodles">
              Noodles
            </button>
            <button class="category-btn" data-category="drinks">Drinks</button>
          </div>
          <div class="menu-items" id="menuItems">
            <!-- Menu items will be loaded here -->
          </div>
        </div>
      </section>
    </main>

    <!-- Checkout Modal -->
    <div class="checkout-modal" id="checkoutModal">
      <div class="checkout-container">
        <div class="checkout-header">
          <h2>Your Order</h2>
          <button class="close-checkout">&times;</button>
        </div>

        <div class="checkout-body">
          <div class="cart-items" id="cartItems">
            <!-- Cart items will be loaded here -->
          </div>

          <div class="cart-summary">
            <div class="summary-row">
              <span>Subtotal:</span>
              <span id="subtotal">₦0.00</span>
            </div>
            <div class="summary-row">
              <span>Delivery Fee:</span>
              <span id="deliveryFee">₦1,500.00</span>
            </div>
            <div class="summary-row total">
              <span>Total:</span>
              <span id="totalAmount">₦1,500.00</span>
            </div>

            <div class="checkout-actions">
              <button class="btn btn-clear" id="clearCart">Clear Cart</button>
              <button class="btn btn-checkout" id="proceedToCheckout">
                Checkout
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="customer-details-modal" id="customerDetailsModal">
      <div class="customer-details-container">
        <div class="customer-details-header">
          <h2>Customer Information</h2>
          <button class="close-customer-details">&times;</button>
        </div>

        <form id="customerDetailsForm">
          <div class="form-group">
            <label for="fullName">Full Name</label>
            <input type="text" id="fullName" required />
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" required />
          </div>

          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" required />
          </div>

          <div class="form-group">
            <label for="state">State</label>
            <select id="state" required>
              <option value="" disabled selected>Select your state</option>
              <option value="Abia">Abia</option>
              <option value="Adamawa">Adamawa</option>
              <option value="Akwa Ibom">Akwa Ibom</option>
              <option value="Anambra">Anambra</option>
              <option value="Bauchi">Bauchi</option>
              <option value="Bayelsa">Bayelsa</option>
              <option value="Benue">Benue</option>
              <option value="Borno">Borno</option>
              <option value="Cross River">Cross River</option>
              <option value="Delta">Delta</option>
              <option value="Ebonyi">Ebonyi</option>
              <option value="Edo">Edo</option>
              <option value="Ekiti">Ekiti</option>
              <option value="Enugu">Enugu</option>
              <option value="FCT">Federal Capital Territory</option>
              <option value="Gombe">Gombe</option>
              <option value="Imo">Imo</option>
              <option value="Jigawa">Jigawa</option>
              <option value="Kaduna">Kaduna</option>
              <option value="Kano">Kano</option>
              <option value="Katsina">Katsina</option>
              <option value="Kebbi">Kebbi</option>
              <option value="Kogi">Kogi</option>
              <option value="Kwara">Kwara</option>
              <option value="Lagos">Lagos</option>
              <option value="Nasarawa">Nasarawa</option>
              <option value="Niger">Niber</option>
              <option value="Ogun">Ogun</option>
              <option value="Ondo">Ondo</option>
              <option value="Osun">Osun</option>
              <option value="Oyo">Oyo</option>
              <option value="Plateau">Plateau</option>
              <option value="Rivers">Rivers</option>
              <option value="Sokoto">Sokoto</option>
              <option value="Taraba">Taraba</option>
              <option value="Yobe">Yobe</option>
              <option value="Zamfara">Zamfara</option>
            </select>
          </div>

          <div class="form-group">
            <label for="address">Delivery Address</label>
            <textarea id="address" rows="3" required></textarea>
          </div>

          <div class="form-group">
            <label for="deliveryNotes">Delivery Instructions (Optional)</label>
            <textarea id="deliveryNotes" rows="2"></textarea>
          </div>

          <div class="payment-methods">
            <h3>Payment Method</h3>
            <div class="payment-options">
              <label class="payment-option">
                <input type="radio" name="paymentMethod" value="cod" checked />
                <span>Cash on Delivery</span>
              </label>

              <label class="payment-option">
                <input type="radio" name="paymentMethod" value="bank" />
                <span>Bank Transfer</span>
              </label>

              <label class="payment-option">
                <input type="radio" name="paymentMethod" value="paystack" />
                <span>Paystack</span>
              </label>

              <label class="payment-option">
                <input type="radio" name="paymentMethod" value="flutterwave" />
                <span>Flutterwave</span>
              </label>
            </div>

            <div class="bank-details" id="bankDetails">
              <h4>Bank Transfer Details</h4>
              <div class="bank-info">
                <p><strong>Bank Name:</strong> Zenith Bank</p>
                <p><strong>Account Name:</strong>Joseph's Pot Ltd</p>
                <p>
                  <strong>Account Number:</strong>
                  <span id="accountNumber">1012345678</span>
                  <button class="copy-btn" id="copyAccountNumber">Copy</button>
                </p>
              </div>
              <div class="proof-view-modal" id="proofViewModal">
                <div class="proof-view-container">
                  <div class="proof-view-header">
                    <h2>Proof of Payment</h2>
                    <button class="close-proof-view">&times;</button>
                  </div>
                  <div class="proof-view-body"> 
                    <img id="proofImage" src="" alt="Proof of Payment" style="max-width: 100%; max-height: 70vh; object-fit: contain;" />
                  </div>
                </div>
                
              </div>

              
              
              <div class="proof-upload">
                <label for="proofUpload">Upload Proof of Payment:</label>
                <input type="file" id="proofUpload" accept="image/*,.pdf" />
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-back" id="backToCart">
              Back to Cart
            </button>
            <button type="submit" class="btn btn-submit">Submit Order</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Receipt Modal -->
    <div class="receipt-modal" id="receiptModal">
      <div class="receipt-container">
        <div class="receipt-header">
          <div class="receipt-logo">
            <img
              src="<?php echo $appearance['logo_path']; ?>?v=<?php echo time(); ?>"
              alt="Joseph's Pot Logo"
              class="logo-img"
            />
            <h2>Joseph's<span>Pot</span></h2>
          </div>
          <div class="receipt-meta">
            <p>Order #<span id="receiptOrderId">GD12345</span></p>
            <p><span id="receiptDate">July 30, 2025</span></p>
          </div>
        </div>

        <div class="receipt-body">
          <div class="customer-info">
            <h3>Customer Details</h3>
            <p>
              <strong>Name:</strong>
              <span id="receiptCustomerName">John Doe</span>
            </p>
            <p>
              <strong>Email:</strong>
              <span id="receiptCustomerEmail">johndoe@example.com</span>
            </p>
            <p>
              <strong>Phone:</strong>
              <span id="receiptCustomerPhone">+1234567890</span>
            </p>
            <p>
              <strong>State:</strong>
              <span id="receiptCustomerState">Lagos</span>
            </p>
            <p>
              <strong>Address:</strong>
              <span id="receiptCustomerAddress"
                >123 Main St, City, Country</span
              >
            </p>
          </div>

          <div class="order-summary">
            <h3>Order Summary</h3>
            <table class="receipt-items" id="receiptItems">
              <tr>
                <th class="item-name">Item</th>
                <th class="item-qty">Qty</th>
                <th class="item-price">Price</th>
              </tr>
              <!-- Receipt items will be loaded here -->
            </table>

            <div class="receipt-totals">
              <div class="total-row">
                <span>Subtotal:</span>
                <span id="receiptSubtotal">₦0.00</span>
              </div>
              <div class="total-row">
                <span>Delivery Fee:</span>
                <span id="receiptDeliveryFee">₦1,500.00</span>
              </div>
              <div class="total-row grand-total">
                <span>Total:</span>
                <span id="receiptTotal">₦1,500.00</span>
              </div>
            </div>

            <div class="payment-info">
              <h3>Payment Information</h3>
              <p>
                Method: <span id="receiptPaymentMethod">Cash on Delivery</span>
              </p>
              <div id="receiptPaymentDetails">
                <!-- Payment details will be shown here -->
              </div>
              <div class="payment-status">
                <div class="status-stamp paid">PAID</div>
              </div>
            </div>
          </div>

          <div class="receipt-qr" id="receiptQrCode"></div>
        </div>

        <div class="receipt-footer">
          <p class="thank-you">Thank you for your order!</p>
          <p class="watermark">
            Joseph's Pot © 2025 | This is a computer generated receipt
          </p>

          <div class="receipt-actions">
            <button class="btn btn-print" id="printReceipt">
              <i class="fas fa-print"></i> Print
            </button>
            <button class="btn btn-download" id="downloadReceipt">
              <i class="fas fa-download"></i> Download
            </button>
            <button class="btn btn-share whatsapp" id="shareWhatsApp">
              <i class="fab fa-whatsapp"></i> Share
            </button>
            <button class="btn btn-share email" id="shareEmail">
              <i class="fas fa-envelope"></i> Email
            </button>
            <button class="btn btn-close" id="closeReceipt">
              <i class="fas fa-times"></i> Close
            </button>
          </div>
        </div>
      </div>
    </div>


    <!-- Footer Section -->
    <footer class="footer">
      <div class="footer-glass">
        <div class="footer-glass-inner">
          <div class="footer-content">
            <div class="footer-column">
              <img src="<?php echo $appearance['logo_path']; ?>?v=<?php echo time(); ?>" alt="Joseph's Pot Logo" width="60px" height="60px" />
              <p>
                Authentic taste, unforgettable experience.<br />Serving
                happiness from Owerri, Nigeria.
              </p>
              <div class="social-links">
                <a href="https://facebook.com" target="_blank"
                  ><i class="fab fa-facebook-f"></i
                ></a>
                <a href="https://instagram.com" target="_blank"
                  ><i class="fab fa-instagram"></i
                ></a>
                <a href="https://twitter.com" target="_blank"
                  ><i class="fab fa-twitter"></i
                ></a>
                <a href="https://tiktok.com" target="_blank"
                  ><i class="fab fa-tiktok"></i
                ></a>
              </div>
            </div>

            <div class="footer-column">
              <h4>Quick Links</h4>
              <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="GALLERY.php">Gallery</a></li>
                <li><a href="contact.php">Contact</a></li>
                
              </ul>
            </div>

            <div class="footer-column">
              <h4><i class="fas fa-clock"></i> Opening Hours</h4>
              <p>
                <?php if (!empty($restaurant_info['opening_hours'])): ?>
                    <?php echo nl2br(htmlspecialchars($restaurant_info['opening_hours'])); ?>
                <?php else: ?>
                    Monday – Friday: 08:30 AM – 9:00 PM<br />
                    Saturday: 08:00 AM – 09:00 PM<br />
                    Sunday: 12:00 PM – 09:00 PM
                <?php endif; ?>
              </p>
            </div>

            <div class="footer-column">
              <h4><i class="fas fa-map-marker-alt"></i> Visit Us</h4>
              <p>
                <?php if (!empty($restaurant_info['restaurant_address'])): ?>
                    <?php echo nl2br(htmlspecialchars($restaurant_info['restaurant_address'])); ?><br>
                <?php else: ?>
                    Plot 120,<br>
                    Ikenegbu Layout by Maris Junction, Owerri<br>
                    Imo State, Nigeria<br>
                <?php endif; ?>
                <a
                  href="https://maps.google.com?q=<?php echo urlencode(!empty($restaurant_info['restaurant_name']) ? $restaurant_info['restaurant_name'] . ' Owerri' : "Joseph's Pot Owerri"); ?>"
                  target="_blank"
                  >📍 <span> Get Directions</span></a
                >
              </p>
            </div>
          </div>

          <div class="footer-bottom">
            <p>&copy; 2025 <?php echo !empty($restaurant_info['restaurant_name']) ? htmlspecialchars($restaurant_info['restaurant_name']) : "Joseph's Pot"; ?>. All Rights Reserved | Developed by ERIBS Tech</p>
          </div>
        </div>
      </div>
    </footer>

    <button id="scrollTopBtn" aria-label="Scroll to Top">
      ↑
    </button>

    <!-- WhatsApp Chat Bubble -->
    <a href="https://wa.me/<?php echo !empty($restaurant_info['restaurant_phone']) ? preg_replace('/[^0-9]/', '', $restaurant_info['restaurant_phone']) : '2348104344994'; ?>" class="whatsapp-chat" target="_blank">
      <img
        src="https://cdn-icons-png.flaticon.com/512/124/124034.png"
        alt="WhatsApp"
      />
    </a>

    <!-- External Scripts (loaded asynchronously to prevent blocking) -->
    <script>
      // Load external scripts with proper error handling
      function loadScript(src, onError) {
        return new Promise((resolve) => {
          const script = document.createElement('script');
          script.src = src;
          script.async = true;
          script.onload = resolve;
          script.onerror = () => {
            console.error('Failed to load script:', src);
            if (onError) onError();
            resolve(); // Resolve anyway to prevent blocking
          };
          document.head.appendChild(script);
        });
      }
      
      // Load scripts asynchronously (non-blocking - load independently)
      (function() {
        // Load QRCode (used only in receipt generation, has null checks in JS)
        loadScript('https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js', () => { window.QRCode = null; });
        
        // Load html2pdf (used only in download receipt, has null checks in JS)
        loadScript('https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js', () => { window.html2pdf = null; });
        
        // Load AOS and initialize when ready
        loadScript('https://unpkg.com/aos@2.3.1/dist/aos.js', () => { window.AOS = null; }).then(() => {
          // Initialize AOS when it loads
          function initAOS() {
            if (typeof AOS !== 'undefined') {
              try {
                AOS.init();
              } catch (e) {
                console.error('Error initializing AOS:', e);
              }
            }
          }
          if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAOS);
          } else {
            initAOS();
          }
        });
      })();
    </script>
    
    <!-- FIXED: Main JavaScript Code -->
    <script>
    // Scroll effect for navbar
    window.addEventListener("scroll", function () {
      const navbar = document.getElementById("navbar");
      if (window.scrollY > 50) {
        navbar.classList.add("scrolled");
      } else {
        navbar.classList.remove("scrolled");
      }
    });

    // Mobile Menu Toggle Function
    function toggleMenu() {
      const navLinks = document.querySelector(".nav-links");
      const menuToggle = document.querySelector(".menu-toggle");

      if (!navLinks || !menuToggle) return;

      // Toggle menu visibility
      navLinks.classList.toggle("active");

      // Toggle hamburger/close icon
      const icon = menuToggle.querySelector("i");
      if (navLinks.classList.contains("active")) {
        icon.classList.remove("fa-utensils");
        icon.classList.add("fa-xmark");
        icon.style.transform = "rotate(180deg)";
      } else {
        icon.classList.remove("fa-xmark");
        icon.classList.add("fa-utensils");
        icon.style.transform = "rotate(0deg)";
      }
    }

    // Fix hamburger click event listener
    document.addEventListener('DOMContentLoaded', function() {
      // Ensure menu toggle button works
      const menuToggle = document.getElementById('menuToggle');
      if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
          e.stopPropagation();
          toggleMenu();
        });
      }

      // Close menu when clicking outside
      document.addEventListener('click', function(e) {
        const navLinks = document.querySelector('.nav-links');
        const menuToggle = document.querySelector('.menu-toggle');
        
        if (navLinks && navLinks.classList.contains('active')) {
          if (!navLinks.contains(e.target) && !menuToggle.contains(e.target)) {
            navLinks.classList.remove('active');
            const icon = menuToggle.querySelector("i");
            if (icon) {
              icon.classList.remove("fa-xmark");
              icon.classList.add("fa-utensils");
              icon.style.transform = "rotate(0deg)";
            }
          }
        }
      });

      // Close menu when clicking on a nav link
      document.querySelectorAll(".nav-links a").forEach((link) => {
        link.addEventListener("click", () => {
          const navLinks = document.querySelector('.nav-links');
          if (window.innerWidth <= 768 && navLinks && navLinks.classList.contains('active')) {
            toggleMenu();
          }
        });
      });

      // FIXED: Scroll to Top Button functionality
      const scrollBtn = document.getElementById("scrollTopBtn");
      if (scrollBtn) {
        // Show/hide button based on scroll position
        window.addEventListener('scroll', function() {
          if (window.scrollY > 300) {
            scrollBtn.style.display = "block";
          } else {
            scrollBtn.style.display = "none";
          }
        });

        // Scroll to top when clicked
        scrollBtn.addEventListener('click', function() {
          window.scrollTo({
            top: 0,
            behavior: "smooth"
          });
        });
      }

      // Rest of your existing code...
      // Add event listener for CTA button
      const exploreMenuBtn = document.getElementById("exploreMenuBtn");
      if (exploreMenuBtn) {
        exploreMenuBtn.addEventListener("click", function (e) {
          e.preventDefault();
          const menuSection = document.getElementById("menu");
          if (menuSection) {
            menuSection.scrollIntoView({ behavior: "smooth" });
          }
        });
      }

      // 1. Menu Data - Will be loaded from database
      let menuItems = [];

      // Initialize with hardcoded items as fallback (will be replaced by database)
      const fallbackMenuItems = [
        {
          id: 1,
          title: "Ofe Owerri",
          description:
            "Filled With Snails, Stockfishes, Dryfishes, Lots of Protein comes with a complimentary swallow",
          price: 27000,
          category: "soups",
          image: "./images/instagram image1.jpg",
        },
        {
          id: 2,
          title: "Ofe Nsala White Soup",
          description:
            "Thickened With Pure White Yam, Filled With Snails, And Lots of Proteins",
          price: 23000,
          category: "soups",
          image: "./images/nsala.jpeg",
        },
        {
          id: 3,
          title: "Egusi Soup",
          description: "Melon seed soup with assorted meat and fish",
          price: 8500,
          category: "soups",
          image: "./images/egusi.jpg",
        },
        {
          id: 4,
          title: "Semo",
          description: "Smooth semovita wrap",
          price: 1500,
          category: "soups",
          image: "./images/semo.jpeg",
        },
        {
          id: 5,
          title: "Garri",
          description: "Smooth garri wrap",
          price: 1500,
          category: "soups",
          image: "./images/garri.JPG",
        },
        {
          id: 6,
          title: "Fufu",
          description: "Smooth fufu wrap",
          price: 1500,
          category: "soups",
          image: "./images/fufu.jpg",
        },
        {
          id: 7,
          title: "Poundo Yam",
          description: "Smooth poundo yam wrap",
          price: 3000,
          category: "soups",
          image: "./images/poundo.jpg",
        },
        {
          id: 8,
          title: "Oat Swallow",
          description: "Smooth oat swallow wrap",
          price: 3000,
          category: "soups",
          image: "./images/oat.jpg",
        },
        {
          id: 9,
          title: "Plaintain Flour Swallow",
          description: "Smooth plaintain flour swallow wrap",
          price: 3000,
          category: "soups",
          image: "./images/plantain.jpg",
        },
        {
          id: 10,
          title: "Nkwobi",
          description: "Goat Meat Sauced with Igbo Traditional Spiced and ugba",
          price: 8000,
          category: "starters",
          image: "./images/IM16.jpg",
        },
        {
          id: 11,
          title: "Abacha",
          description:
            "African Salad made from fermented African Oil Bean Seed, garnished with kpomo, Ugba, fish and spices",
          price: 7000,
          category: "starters",
          image: "./images/Abacha1.jpg",
        },
        {
          id: 12,
          title: "Isi Ewu Ukwu (Large Size)",
          description:
            "Large goat head dish cooked with Joseph's pot special sauce with chips (Beware of bones)",
          price: 20000,
          category: "starters",
          image: "./images/isi ewu.jpg",
        },
        {
          id: 13,
          title: "Isi Ewu (Medium Size)",
          description:
            "Medium goat head dish cooked with Joseph's pot special sauce with chips (Beware of bones)",
          price: 17000,
          category: "starters",
          image: "./images/isi ewu small.jpg",
        },
        {
          id: 14,
          title: "Goat Legs & Periwinkle",
          description: "Special fried rice with mixed vegetables and beef",
          price: 5000,
          category: "starters",
          image: "./images/goat leg.jpg",
        },
        {
          id: 15,
          title: "Peppered Snails",
          description: "Spicy snails cooked in a rich pepper sauce",
          price: 5000,
          category: "starters",
          image: "./images/Peppered-Snail-jumbo.jpg",
        },
        {
          id: 16,
          title: "Jollof Rice",
          description: "Classic Nigerian jollof rice with chicken and plantain",
          price: 8000,
          category: "main courses",
          image: "./images/IM10.jpg",
        },
        {
          id: 17,
          title: "Fried Rice",
          description: "Special fried rice with mixed vegetables and beef",
          price: 4000,
          category: "main courses",
          image: "./images/IM1.jpg",
        },
        {
          id: 18,
          title: "Bitterleaf Soup",
          description: "Smooth pounded yam or Fufu with soup filled with Happiness",
          price: 4000,
          category: "main courses",
          image: "./images/ogbonno soup.jpg",
        },
        {
          id: 19,
          title: "Nkwobi",
          description: "Goat Meat Sauced with Igbo Traditional Spiced and ugba",
          price: 3800,
          category: "main courses",
          image: "./images/nkwobi.jpeg",
        },
        {
          id: 20,
          title: "Chicken & Chips Max",
          description: "Crispy chicken served with golden fries",
          price: 15000,
          category: "noodles",
          image: "./images/IM42.jpg",
        },
        {
          id: 21,
          title: "Indomie Noodles",
          description: "Special indomie noodles with fried egg and sausages",
          price: 6000,
          category: "noodles",
          image: "./images/noodles.jpeg",
        },
        {
          id: 22,
          title: "Spaghetti",
          description: "Jollof spaghetti with chicken and vegetables",
          price: 7000,
          category: "noodles",
          image: "./images/spaghetti.jpg",
        },
        {
          id: 23,
          title: "Joe's Secret ",
          description:
            "Quick noodles with vegetables and eggs mixed with fried plantain",
          price: 25000,
          category: "noodles",
          image: "./images/2021-09-06.webp",
        },
        {
          id: 24,
          title: "Yam Noodles",
          description: "Healthy hotdog noodles with stir-fry vegetables",
          price: 3000,
          category: "noodles",
          image: "./images/noodle and hotdog.jpg",
        },
        {
          id: 25,
          title: "palm-Wine",
          description: "Refreshing Igbo Palm-wine drink",
          price: 10000,
          category: "drinks",
          image: "./images/IM30.jpg",
        },
        {
          id: 26,
          title: "Sprite",
          description: "Iced or Cold sprite drink",
          price: 1300,
          category: "drinks",
          image: "./images/images (10).jpeg",
        },
        {
          id: 27,
          title: "Fanta Orange ",
          description: "Iced or Cold Fanta drink",
          price: 1300,
          category: "drinks",
          image: "./images/images (8).jpeg",
        },
        {
          id: 28,
          title: "Coca Cola",
          description: "Iced or Cold Coca Cola drink",
          price: 1300,
          category: "drinks",
          image: "./images/images (9).jpeg",
        },
        {
          id: 29,
          title: "Malt,Fayrouz",
          description: "Iced or Cold Choice of Malt, Fayrouz drink",
          price: 1500,
          category: "drinks",
          image: "./images/malts.png",
        },
        {
          id: 30,
          title: "Small Bottled Water",
          description: "Iced or Cold Choice of bottled water",
          price: 500,
          category: "drinks",
          image: "./images/images (11).jpeg",
        },
        {
          id: 31,
          title: "Big Eva Bottled Water",
          description: "Iced or Cold Choice of bottled water",
          price: 2000,
          category: "drinks",
          image: "./images/IM43.png",
        },
        {
          id: 32,
          title: "Heineken",
          description: "Iced or Cold Heineken, G.Stout, Despirado Beer drink",
          price: 3500,
          category: "drinks",
          image: "https://res.cloudinary.com/dl4hjr1p2/image/upload/v1762852049/heineken_lbukim.jpg",
        },
        {
          id: 33,
          title: "Star Radler",
          description: "Iced or Cold Star Radler beer drink",
          price: 3500,
          category: "drinks",
          image: "./images/IM33.png",
        },
      ];

      // 2. DOM Elements
      const menuItemsContainer = document.getElementById("menuItems");
      const cartIcon = document.getElementById("cartIcon");
      const cartIconContainer = document.getElementById("cartIconContainer");
      const cartCount = document.querySelector(".cart-count");
      const categoryButtons = document.querySelectorAll(".category-btn");

      // Track current filter category
      let currentCategory = "all";
      const checkoutModal = document.getElementById("checkoutModal");
      const closeCheckout = document.querySelector(".close-checkout");
      const cartItemsContainer = document.getElementById("cartItems");
      const subtotalElement = document.getElementById("subtotal");
      const totalAmountElement = document.getElementById("totalAmount");
      const clearCartBtn = document.getElementById("clearCart");
      const proceedToCheckoutBtn = document.getElementById("proceedToCheckout");
      const customerDetailsModal = document.getElementById("customerDetailsModal");
      const closeCustomerDetails = document.querySelector(
        ".close-customer-details"
      );
      const customerDetailsForm = document.getElementById("customerDetailsForm");
      const backToCartBtn = document.getElementById("backToCart");
      const paymentOptions = document.querySelectorAll(
        'input[name="paymentMethod"]'
      );
      const bankDetailsSection = document.getElementById("bankDetails");
      const copyAccountNumberBtn = document.getElementById("copyAccountNumber");
      const receiptModal = document.getElementById("receiptModal");
      const receiptItemsContainer = document.getElementById("receiptItems");
      const receiptSubtotal = document.getElementById("receiptSubtotal");
      const receiptTotal = document.getElementById("receiptTotal");
      const receiptCustomerName = document.getElementById("receiptCustomerName");
      const receiptCustomerEmail = document.getElementById("receiptCustomerEmail");
      const receiptCustomerPhone = document.getElementById("receiptCustomerPhone");
      const receiptCustomerState = document.getElementById("receiptCustomerState");
      const receiptCustomerAddress = document.getElementById(
        "receiptCustomerAddress"
      );
      const receiptPaymentMethod = document.getElementById("receiptPaymentMethod");
      const receiptPaymentDetails = document.getElementById(
        "receiptPaymentDetails"
      );
      const receiptOrderId = document.getElementById("receiptOrderId");
      const receiptDate = document.getElementById("receiptDate");
      const receiptQrCode = document.getElementById("receiptQrCode");
      const printReceiptBtn = document.getElementById("printReceipt");
      const downloadReceiptBtn = document.getElementById("downloadReceipt");
      const shareWhatsAppBtn = document.getElementById("shareWhatsApp");
      const shareEmailBtn = document.getElementById("shareEmail");
      const closeReceiptBtn = document.getElementById("closeReceipt");
      // Toast and Empty Cart Elements
      const toastContainer = document.getElementById("toastContainer");
      const emptyCartPrompt = document.getElementById("emptyCartPrompt");
      const closeEmptyCartBtn = document.getElementById("closeEmptyCart");
      const browseMenuBtn = document.getElementById("browseMenu");
      // Proof of Payment Modal Elements
      const proofViewModal = document.getElementById("proofViewModal");
      const closeProofView = document.querySelector(".close-proof-view");
      const proofImage = document.getElementById("proofImage");

      // 3. Cart State
      let cart = JSON.parse(localStorage.getItem("cart")) || [];

      // Load menu items from database
      async function loadMenuItems() {
        try {
          console.log("📡 Loading menu items from API...");
          const apiUrl = "./api/get-menu-items.php";
          console.log("API URL:", apiUrl);
          
          const response = await fetch(apiUrl);

          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }

          const data = await response.json();
          console.log("API Response:", data);

          if (data.success && data.items) {
            console.log(`✅ Loaded ${data.items.length} menu items from database`);
            if (data.debug) {
              console.log("Debug info:", data.debug);
              if (data.debug.total_items_in_db > 0 && data.items.length === 0) {
                console.warn(
                  '⚠️ Items exist in database but none are marked as "Available". Check admin panel and set items to "Available" status.'
                );
              }
            }
            menuItems = data.items;
            // Render with current category filter
            renderMenuItems(currentCategory);
          } else {
            console.error(
              "❌ Failed to load menu items:",
              data.message || "Unknown error"
            );
            console.error("API returned:", data);
            // DO NOT use fallbackMenuItems - show empty menu instead
            menuItems = [];
            renderMenuItems(currentCategory);
          }
        } catch (error) {
          console.error("❌ Error loading menu items:", error);
          console.error("Error details:", {
            message: error.message,
            stack: error.stack,
          });
          // DO NOT use fallbackMenuItems - show empty menu instead
          menuItems = [];
          renderMenuItems(currentCategory);
        }
      }

      // 4. Initialize App
      async function init() {
        console.log("🚀 Initializing app");

        // Setup event listeners first
        setupEventListeners();

        // Load menu items from database (replaces fallback data)
        console.log("📡 About to call loadMenuItems()...");
        try {
          await loadMenuItems();
          console.log("✅ loadMenuItems() completed. menuItems length:", menuItems.length);
        } catch (error) {
          console.error("❌ Error in loadMenuItems():", error);
          menuItems = []; // Ensure empty array, not fallback
        }

        // Update cart count
        updateCartCount();

        // Setup other event listeners with null checks
        if (closeEmptyCartBtn) {
          closeEmptyCartBtn.addEventListener("click", closeEmptyCartPrompt);
        }
        if (browseMenuBtn) {
          browseMenuBtn.addEventListener("click", function () {
            closeEmptyCartPrompt();
            const menuSection = document.getElementById("menu");
            if (menuSection) {
              menuSection.scrollIntoView({ behavior: "smooth" });
            }
          });
        }
      }

      // 5. Render Menu Items - FIXED: Properly load and display menu items
      function renderMenuItems(category = "all") {
        // Update current category
        currentCategory = category;

        if (!menuItemsContainer) return;
        menuItemsContainer.innerHTML = "";

        console.log(`Rendering menu items for category: ${category}`);
        console.log(`Total menu items: ${menuItems.length}`);

        // Filter items by category (case-insensitive comparison)
        const filteredItems = menuItems.filter((item) => {
          if (category === "all") return true;
          
          const itemCategory = (item.category || "").toLowerCase().trim();
          const filterCategory = category.toLowerCase().trim();
          
          // Handle category name variations
          if (filterCategory === "main courses" || filterCategory === "main course") {
            return itemCategory === "main courses" || itemCategory === "main course";
          }
          if (filterCategory === "soups") {
            return itemCategory === "soups" || itemCategory === "soup";
          }
          if (filterCategory === "starters") {
            return itemCategory === "starters" || itemCategory === "starter" || itemCategory === "appetizers";
          }
          if (filterCategory === "noodles") {
            return itemCategory === "noodles" || itemCategory === "noodle";
          }
          if (filterCategory === "drinks") {
            return itemCategory === "drinks" || itemCategory === "drink" || itemCategory === "beverages";
          }
          
          return itemCategory === filterCategory;
        });

        console.log(
          `Rendering ${filteredItems.length} items for category: ${category}`
        );

        if (filteredItems.length === 0) {
          menuItemsContainer.innerHTML =
            '<p class="no-items">No items in this category</p>';
          return;
        }

        filteredItems.forEach((item) => {
          const menuItemElement = document.createElement("div");
          menuItemElement.className = "menu-item";
          // Use a data URI placeholder if no image (prevents 404 errors and infinite loops)
          const placeholderImage = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300'%3E%3Crect fill='%23ddd' width='400' height='300'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dy='.3em' fill='%23999' font-family='sans-serif' font-size='18'%3ENo Image%3C/text%3E%3C/svg%3E";
          const imageSrc = item.image || placeholderImage;
          
          // FIXED: Proper item ID handling
          const itemId = item.id || Math.floor(Math.random() * 1000000);
          
          menuItemElement.innerHTML = `
            <img src="${imageSrc}" alt="${
            item.title
          }" class="menu-item-img" onerror="this.onerror=null; this.src='${placeholderImage}';">
            <div class="menu-item-content">
              <h3 class="menu-item-title">${item.title || "Untitled Item"}</h3>
              <p class="menu-item-desc">${item.description || ""}</p>
              <div class="menu-item-footer">
                <span class="menu-item-price">₦${(
                  item.price || 0
                ).toLocaleString()}</span>
                <button class="add-to-cart" data-id="${itemId}">Add to Cart</button>
              </div>
            </div>
          `;
          menuItemsContainer.appendChild(menuItemElement);
        });
      }

      // 6. Setup Event Listeners
      function setupEventListeners() {
        // Add null checks to prevent errors if elements don't exist
        if (categoryButtons && categoryButtons.length > 0) {
          categoryButtons.forEach((button) => {
            button.addEventListener("click", function () {
              categoryButtons.forEach((btn) => btn.classList.remove("active"));
              this.classList.add("active");
              const selectedCategory = this.dataset.category || "all";
              console.log("Category button clicked:", selectedCategory);
              renderMenuItems(selectedCategory);
            });
          });
        }

        if (menuItemsContainer) {
          menuItemsContainer.addEventListener("click", function (e) {
            if (e.target.classList.contains("add-to-cart")) {
              const itemId = parseInt(e.target.getAttribute("data-id"));
              console.log("Add to cart clicked for item ID:", itemId);
              addToCart(itemId);
            }
          });
        }

        // FIXED: Enhanced cart icon click handling
        if (cartIconContainer) {
          cartIconContainer.addEventListener("click", function (e) {
            e.stopPropagation();
            console.log("Cart icon clicked");
            if (cart.length === 0) {
              showEmptyCartPrompt();
            } else {
              openCheckoutModal();
            }
          });
        }

        // Also allow clicking directly on the cart icon
        if (cartIcon) {
          cartIcon.addEventListener("click", function (e) {
            e.stopPropagation();
            console.log("Cart icon clicked directly");
            if (cart.length === 0) {
              showEmptyCartPrompt();
            } else {
              openCheckoutModal();
            }
          });
        }

        if (closeCheckout) {
          closeCheckout.addEventListener("click", closeCheckoutModal);
        }
        if (clearCartBtn) {
          clearCartBtn.addEventListener("click", clearCart);
        }
        if (proceedToCheckoutBtn) {
          proceedToCheckoutBtn.addEventListener("click", openCustomerDetails);
        }
        if (closeCustomerDetails) {
          closeCustomerDetails.addEventListener("click", closeCustomerDetailsModal);
        }
        if (backToCartBtn) {
          backToCartBtn.addEventListener("click", function () {
            closeCustomerDetailsModal();
            openCheckoutModal();
          });
        }
        if (paymentOptions && paymentOptions.length > 0) {
          paymentOptions.forEach((option) => {
            option.addEventListener("change", handlePaymentMethodChange);
          });
        }
        if (copyAccountNumberBtn) {
          copyAccountNumberBtn.addEventListener("click", copyAccountNumber);
        }
        if (customerDetailsForm) {
          customerDetailsForm.addEventListener("submit", handleFormSubmission);
        }
        if (printReceiptBtn) {
          printReceiptBtn.addEventListener("click", printReceipt);
        }
        if (downloadReceiptBtn) {
          downloadReceiptBtn.addEventListener("click", downloadReceipt);
        }
        if (shareWhatsAppBtn) {
          shareWhatsAppBtn.addEventListener("click", shareViaWhatsApp);
        }
        if (shareEmailBtn) {
          shareEmailBtn.addEventListener("click", shareViaEmail);
        }
        if (closeReceiptBtn) {
          closeReceiptBtn.addEventListener("click", closeReceiptModal);
        }
      }

      // 7. Cart Functions
      function addToCart(itemId) {
        console.log("Adding item to cart:", itemId);
        
        // Find the menu item
        const menuItem = menuItems.find((item) => item.id === itemId);
        if (!menuItem) {
          console.error("Menu item not found for ID:", itemId);
          showToast("Error: Item not found", true);
          return;
        }

        const existingItem = cart.find((item) => item.id === itemId);

        if (existingItem) {
          existingItem.quantity += 1;
        } else {
          cart.push({
            ...menuItem,
            quantity: 1,
          });
        }

        updateCart();
        animateCartIcon();

        showToast(`Added ${menuItem.title} to cart`);
      }

      function updateCart() {
        localStorage.setItem("cart", JSON.stringify(cart));
        updateCartCount();
        renderCartItems();
        calculateTotals();
      }

      function updateCartCount() {
        if (!cartCount) return;
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
        cartCount.textContent = totalItems;
        cartCount.style.display = totalItems > 0 ? "flex" : "none";
      }

      function renderCartItems() {
        if (!cartItemsContainer) return;
        cartItemsContainer.innerHTML = "";

        if (cart.length === 0) {
          cartItemsContainer.innerHTML =
            '<p class="empty-cart">Your cart is empty</p>';
          return;
        }

        cart.forEach((item) => {
          const cartItemElement = document.createElement("div");
          cartItemElement.className = "cart-item";
          cartItemElement.innerHTML = `
            <img src="${item.image}" alt="${item.title}" class="cart-item-img">
            <div class="cart-item-details">
              <h4 class="cart-item-title">${item.title}</h4>
              <p class="cart-item-price">₦${(
                item.price * item.quantity
              ).toLocaleString()}</p>
            </div>
            <div class="quantity-control">
              <button class="quantity-btn minus" data-id="${item.id}">-</button>
              <span class="quantity">${item.quantity}</span>
              <button class="quantity-btn plus" data-id="${item.id}">+</button>
            </div>
            <button class="remove-item" data-id="${item.id}">
              <i class="fas fa-trash"></i>
            </button>
          `;
          cartItemsContainer.appendChild(cartItemElement);
        });

        document.querySelectorAll(".quantity-btn.minus").forEach((btn) => {
          btn.addEventListener("click", () =>
            updateQuantity(parseInt(btn.dataset.id), -1)
          );
        });

        document.querySelectorAll(".quantity-btn.plus").forEach((btn) => {
          btn.addEventListener("click", () =>
            updateQuantity(parseInt(btn.dataset.id), 1)
          );
        });

        document.querySelectorAll(".remove-item").forEach((btn) => {
          btn.addEventListener("click", () => removeItem(parseInt(btn.dataset.id)));
        });
      }

      function updateQuantity(itemId, change) {
        const itemIndex = cart.findIndex((item) => item.id === itemId);
        if (itemIndex !== -1) {
          cart[itemIndex].quantity += change;
          if (cart[itemIndex].quantity <= 0) {
            cart.splice(itemIndex, 1);
          }
          updateCart();
        }
      }

      function removeItem(itemId) {
        cart = cart.filter((item) => item.id !== itemId);
        updateCart();
      }

      function clearCart() {
        cart = [];
        updateCart();
        closeCheckoutModal();
      }

      function calculateTotals() {
        const subtotal = cart.reduce(
          (total, item) => total + item.price * item.quantity,
          0
        );
        const deliveryFee = 1500;
        const total = subtotal + deliveryFee;

        if (subtotalElement) {
          subtotalElement.textContent = `₦${subtotal.toLocaleString()}`;
        }
        if (totalAmountElement) {
          totalAmountElement.textContent = `₦${total.toLocaleString()}`;
        }
      }

      // 8. Modal Functions
      function openCheckoutModal() {
        if (!checkoutModal) return;
        checkoutModal.style.display = "flex";
        document.body.style.overflow = "hidden";
        renderCartItems();
        calculateTotals();
      }

      function closeCheckoutModal() {
        if (!checkoutModal) return;
        checkoutModal.style.display = "none";
        document.body.style.overflow = "auto";
      }

      function openCustomerDetails() {
        if (cart.length === 0) return;
        if (checkoutModal) checkoutModal.style.display = "none";
        if (customerDetailsModal) customerDetailsModal.style.display = "flex";
      }

      function closeCustomerDetailsModal() {
        if (!customerDetailsModal) return;
        customerDetailsModal.style.display = "none";
        document.body.style.overflow = "auto";
      }

      function openReceiptModal() {
        if (customerDetailsModal) customerDetailsModal.style.display = "none";
        if (receiptModal) receiptModal.style.display = "flex";
      }

      function closeReceiptModal() {
        if (!receiptModal) return;
        receiptModal.style.display = "none";
        document.body.style.overflow = "auto";
      }

      // 9. Payment Handling
      function handlePaymentMethodChange(e) {
        if (bankDetailsSection) {
          bankDetailsSection.style.display =
            e.target.value === "bank" ? "block" : "none";
        }
        const proofUploadInput = document.getElementById("proofUpload");
        if (proofUploadInput) {
          if (e.target.value === "bank") {
            proofUploadInput.required = true;
          } else {
            proofUploadInput.required = false;
          }
        }
      }

      function copyAccountNumber() {
        const accountNumberEl = document.getElementById("accountNumber");
        if (!accountNumberEl || !copyAccountNumberBtn) return;
        const accountNumber = accountNumberEl.textContent;
        navigator.clipboard.writeText(accountNumber).then(() => {
          const originalText = copyAccountNumberBtn.textContent;
          copyAccountNumberBtn.textContent = "Copied!";
          setTimeout(() => (copyAccountNumberBtn.textContent = originalText), 2000);
        });
      }

      // 10. Helper Functions for Order Submission
      function readFileAsBase64(file) {
        return new Promise((resolve, reject) => {
          if (!file || !file.type.startsWith("image/")) {
            reject(
              new Error("Invalid file type. Please upload an image (PNG, JPEG).")
            );
            return;
          }
          if (file.size > 2 * 1024 * 1024) {
            reject(new Error("Image size exceeds 2MB limit."));
            return;
          }
          const reader = new FileReader();
          reader.onload = () => resolve(reader.result);
          reader.onerror = () => reject(new Error("Failed to read file"));
          reader.readAsDataURL(file);
        });
      }

      async function saveOrder(
        formData,
        subtotal,
        totalAmount,
        status = "pending"
      ) {
        const now = new Date();
        let proofOfPayment = null;

        if (
          formData.paymentMethod === "bank" &&
          document.getElementById("proofUpload").files[0]
        ) {
          try {
            const file = document.getElementById("proofUpload").files[0];
            proofOfPayment = await readFileAsBase64(file);
          } catch (error) {
            console.error("Error reading proof of payment:", error);
            showToast(error.message, true);
            return null;
          }
        }

        // Prepare order data for API
        const orderData = {
          customerName: formData.fullName,
          customerEmail: formData.email,
          customerPhone: formData.phone,
          customerState: formData.state,
          deliveryAddress: formData.address,
          deliveryInstructions: formData.deliveryNotes || null,
          items: cart.map((item) => ({
            name: item.title,
            price: item.price,
            quantity: item.quantity,
          })),
          subtotal: subtotal,
          deliveryFee: 1500,
          totalAmount: totalAmount,
          paymentMethod: formData.paymentMethod,
          paymentProof: proofOfPayment,
          paymentStatus: status === "pending" ? "pending" : "completed",
        };

        try {
          // Send order to server
          const response = await fetch("submit-order.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(orderData),
          });

          const result = await response.json();

          if (!result.success) {
            throw new Error(result.message || "Failed to save order");
          }

          // Return order object with server-generated ID
          return {
            id: result.order_id,
            order_id: result.order_id,
            customerName: result.order.customerName,
            customerEmail: result.order.customerEmail,
            customerPhone: result.order.customerPhone,
            customerAddress: formData.address,
            items: [...cart],
            subtotal: subtotal,
            deliveryFee: 1500,
            total: totalAmount,
            paymentMethod: formData.paymentMethod,
            proofOfPayment: proofOfPayment,
            date: now.toISOString(),
            status: result.order.status,
          };
        } catch (error) {
          console.error("Error saving order:", error);
          showToast(
            error.message || "Failed to save order. Please try again.",
            true
          );
          return null;
        }
      }

      function processPaystackPayment(formData, subtotal, amount) {
        const submitButton = document.querySelector(".btn-submit");
        const formInputs = customerDetailsForm.querySelectorAll(
          "input, textarea, select, button"
        );

        const handler = PaystackPop.setup({
          key: "pk_test_26f8c2230ec7838bcf82ad3e199674e777ccfac0",
          email: formData.email,
          amount: amount,
          currency: "NGN",
          ref: "GD-" + Date.now(),
          callback: async function (response) {
            formData.paymentReference = response.reference;
            const order = await saveOrder(formData, subtotal, amount / 100, "completed");
            if (order) {
              cart = [];
              updateCart();
              openReceiptModal();
              generateReceipt(formData, order.items, subtotal, amount / 100, order.id);
              showToast("Order placed successfully!", false);
            }
            submitButton.textContent = "Submit Order";
            submitButton.disabled = false;
            formInputs.forEach((input) => {
              input.disabled = false;
            });
          },
          onClose: function () {
            showToast("Payment window closed. Please try again.", true);
            submitButton.textContent = "Submit Order";
            submitButton.disabled = false;
            formInputs.forEach((input) => {
              input.disabled = false;
            });
          },
        });
        handler.openIframe();
      }

      function processFlutterwavePayment(formData, subtotal, amount) {
        const submitButton = document.querySelector(".btn-submit");
        const formInputs = customerDetailsForm.querySelectorAll(
          "input, textarea, select, button"
        );

        FlutterwaveCheckout({
          public_key: "FLWPUBK_TEST-598a8b4cadcb2c02ca9b177034b11e16-X",
          tx_ref: "GD-" + Date.now(),
          amount: amount / 100,
          currency: "NGN",
          payment_options: "card,mobilemoney,ussd",
          customer: {
            email: formData.email,
            phone_number: formData.phone,
            name: formData.fullName,
          },
          callback: async function (response) {
            formData.paymentReference = response.tx_ref;
            const order = await saveOrder(formData, subtotal, amount / 100, "completed");
            if (order) {
              cart = [];
              updateCart();
              openReceiptModal();
              generateReceipt(formData, order.items, subtotal, amount / 100, order.id);
              showToast("Order placed successfully!", false);
            }
            submitButton.textContent = "Submit Order";
            submitButton.disabled = false;
            formInputs.forEach((input) => {
              input.disabled = false;
            });
          },
          onclose: function () {
            showToast("Payment window closed. Please try again.", true);
            submitButton.textContent = "Submit Order";
            submitButton.disabled = false;
            formInputs.forEach((input) => {
              input.disabled = false;
            });
          },
          customizations: {
            title: "Joseph's Pot",
            description: "Payment for your delicious order",
            logo: "https://via.placeholder.com/100x100?text=JP",
          },
        });
      }

      // 11. Form Handling
      async function handleFormSubmission(e) {
        e.preventDefault();

        const formData = {
          fullName: document.getElementById("fullName").value,
          email: document.getElementById("email").value,
          phone: document.getElementById("phone").value,
          state: document.getElementById("state").value,
          address: document.getElementById("address").value,
          deliveryNotes: document.getElementById("deliveryNotes").value,
          paymentMethod: document.querySelector(
            'input[name="paymentMethod"]:checked'
          ).value,
          proofUpload: document.getElementById("proofUpload").files[0]
            ? "Uploaded"
            : "Not provided",
        };

        const subtotal = cart.reduce(
          (total, item) => total + item.price * item.quantity,
          0
        );
        const totalAmount = subtotal + 1500;

        if (
          formData.paymentMethod === "bank" &&
          !document.getElementById("proofUpload").files[0]
        ) {
          showToast("Please upload proof of payment for bank transfer.", true);
          return;
        }

        const submitButton = document.querySelector(".btn-submit");
        const originalText = submitButton.textContent;
        submitButton.innerHTML =
          '<i class="fas fa-spinner fa-spin"></i> Processing...';
        submitButton.disabled = true;

        const formInputs = customerDetailsForm.querySelectorAll(
          "input, textarea, select, button"
        );
        formInputs.forEach((input) => {
          input.disabled = true;
        });

        try {
          if (formData.paymentMethod === "paystack") {
            await loadPaystackScript();
            processPaystackPayment(formData, subtotal, totalAmount * 100);
            return;
          } else if (formData.paymentMethod === "flutterwave") {
            await loadFlutterwaveScript();
            processFlutterwavePayment(formData, subtotal, totalAmount * 100);
            return;
          } else {
            // For COD and Bank Transfer, save order to backend
            const order = await saveOrder(
              formData,
              subtotal,
              totalAmount,
              "pending"
            );
            if (!order) {
              throw new Error("Order creation failed");
            }
            cart = [];
            updateCart();
            openReceiptModal();
            generateReceipt(formData, order.items, subtotal, totalAmount, order.id);
            showToast("Order placed successfully!", false);
          }
        } catch (error) {
          console.error("Payment processing error:", error);
          showToast("Payment processing failed. Please try again.", true);
        } finally {
          submitButton.textContent = originalText;
          submitButton.disabled = false;
          formInputs.forEach((input) => {
            input.disabled = false;
          });
        }
      }

      function generateReceipt(
        formData = {},
        items = [],
        subtotal = 0,
        totalAmount = 0,
        orderId = null
      ) {
        if (!orderId) {
          orderId = "GD" + Math.floor(10000 + Math.random() * 90000);
        }
        const now = new Date();

        const currentDateTime = now.toLocaleDateString("en-US", {
          weekday: "long",
          year: "numeric",
          month: "long",
          day: "numeric",
          hour: "2-digit",
          minute: "2-digit"
        });

        receiptOrderId.textContent = orderId;
        receiptDate.textContent = currentDateTime;
        receiptQrCode.innerHTML = "";

        receiptCustomerName.textContent = formData.fullName || "Walk-in Customer";
        receiptCustomerEmail.textContent = formData.email || "Not provided";
        receiptCustomerPhone.textContent = formData.phone || "N/A";
        receiptCustomerState.textContent = formData.state || "Not specified";
        receiptCustomerAddress.textContent = formData.address || "In-store pickup";

        receiptItemsContainer.innerHTML = `
          <tr>
            <th class="item-name">Item</th>
            <th class="item-qty">Qty</th>
            <th class="item-price">Price</th>
          </tr>
        `;

        let calculatedSubtotal = 0;
        if (items.length > 0) {
          items.forEach((item) => {
            const itemTotal = item.price * item.quantity;
            calculatedSubtotal += itemTotal;
            receiptItemsContainer.innerHTML += `
              <tr>
                <td>${item.title}</td>
                <td class="item-qty">${item.quantity}</td>
                <td class="item-price">₦${itemTotal.toLocaleString()}</td>
              </tr>
            `;
          });
        } else {
          calculatedSubtotal = subtotal;
        }

        const deliveryFee = 1500;
        receiptItemsContainer.innerHTML += `
          <tr>
            <td colspan="2">Delivery Fee</td>
            <td class="item-price">₦${deliveryFee.toLocaleString()}</td>
          </tr>
        `;

        const total = calculatedSubtotal + deliveryFee;
        receiptSubtotal.textContent = `₦${calculatedSubtotal.toLocaleString()}`;
        receiptTotal.textContent = `₦${total.toLocaleString()}`;

        if (formData.paymentMethod) {
          let paymentDetails = "";
          switch (formData.paymentMethod) {
            case "cod":
              receiptPaymentMethod.textContent = "Cash on Delivery";
              paymentDetails = "<p>Payment to be collected upon delivery</p>";
              break;
            case "bank":
              receiptPaymentMethod.textContent = "Bank Transfer";
              paymentDetails = `
                <p><strong>Bank Name:</strong> Zenith Bank</p>
                <p><strong>Account Name:</strong> Joseph's Pot Ltd</p>
                <p><strong>Account Number:</strong> 1012345678</p>
                ${
                  formData.proofUpload === "Uploaded"
                    ? "<p><strong>Proof Uploaded:</strong> Yes</p>"
                    : ""
                }
              `;
              break;
            case "paystack":
              receiptPaymentMethod.textContent = "Paystack";
              paymentDetails = `
                <p>Paid via Paystack payment gateway</p>
                ${
                  formData.paymentReference
                    ? `<p><strong>Reference:</strong> ${formData.paymentReference}</p>`
                    : ""
                }
              `;
              break;
            case "flutterwave":
              receiptPaymentMethod.textContent = "Flutterwave";
              paymentDetails = `
                <p>Paid via Flutterwave payment gateway</p>
                ${
                  formData.paymentReference
                    ? `<p><strong>Reference:</strong> ${formData.paymentReference}</p>`
                    : ""
                }
              `;
              break;
            default:
              receiptPaymentMethod.textContent = "Unknown";
              paymentDetails = "<p>Payment method not specified</p>";
          }
          receiptPaymentDetails.innerHTML = paymentDetails;
        }

        // Generate QR code if library is loaded
        if (typeof QRCode !== 'undefined') {
          try {
            const qrCodeElement = document.createElement("canvas");
            receiptQrCode.appendChild(qrCodeElement);
            QRCode.toCanvas(
              qrCodeElement,
              `Order ID: ${orderId}\nDate: ${currentDateTime}\nTotal: ₦${total.toLocaleString()}`,
              {
                width: 150,
                margin: 2,
                color: { dark: "#292f36", light: "#ffffff" },
                errorCorrectionLevel: "H",
              }
            );
          } catch (error) {
            console.error("QR code generation failed:", error);
          }
        }
      }

      function printReceipt() {
        const printContent = document.querySelector(".receipt-container").innerHTML;
        const originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        openReceiptModal();
      }

      function downloadReceipt() {
        if (typeof html2pdf === 'undefined') {
          showToast("PDF download library not loaded. Please refresh the page and try again.", true);
          console.error("html2pdf library not available");
          return;
        }
        const element = document
          .querySelector(".receipt-container")
          .cloneNode(true);
        const actions = element.querySelector(".receipt-actions");
        if (actions) actions.remove();

        const opt = {
          margin: 10,
          filename: `receipt_${receiptOrderId.textContent}.pdf`,
          image: { type: "jpeg", quality: 0.98 },
          html2canvas: { scale: 2 },
          jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
        };

        try {
          html2pdf().from(element).set(opt).save();
        } catch (error) {
          console.error("PDF generation failed:", error);
          showToast("Failed to generate PDF. Please try again.", true);
        }
      }

      function shareViaWhatsApp() {
        const orderId = receiptOrderId.textContent;
        const total = receiptTotal.textContent;
        const message = `My order from Joseph's Pot - Order #${orderId} - Total: ${total}`;
        const encodedMessage = encodeURIComponent(message);

        const whatsappUrl = `https://api.whatsapp.com/send?text=${encodedMessage}`;
        window.open(whatsappUrl, "_blank");
      }

      function shareViaEmail() {
        const orderId = receiptOrderId.textContent;
        const total = receiptTotal.textContent;
        window.open(
          `mailto:?subject=${encodeURIComponent(
            `My Order #${orderId} from Joseph's Pot`
          )}&body=${encodeURIComponent(
            `Hi,\n\nHere's my order details:\nOrder ID: ${orderId}\nTotal: ${total}\n\nThank you!`
          )}`,
          "_blank"
        );
      }

      function loadPaystackScript() {
        return new Promise((resolve) => {
          if (window.PaystackPop) return resolve();
          const script = document.createElement("script");
          script.src = "https://js.paystack.co/v1/inline.js";
          script.onload = resolve;
          document.head.appendChild(script);
        });
      }

      function loadFlutterwaveScript() {
        return new Promise((resolve) => {
          if (window.FlutterwaveCheckout) return resolve();
          const script = document.createElement("script");
          script.src = "https://checkout.flutterwave.com/v3.js";
          script.onload = resolve;
          document.head.appendChild(script);
        });
      }

      function animateCartIcon() {
        if (cartIcon) {
          cartIcon.style.transform = "scale(1.2)";
          setTimeout(() => {
            cartIcon.style.transform = "scale(1)";
          }, 300);
        }
      }

      function showToast(message, isError = false) {
        if (!toastContainer) return;
        
        const toast = document.createElement("div");
        toast.className = isError ? "toast error" : "toast";
        toast.innerHTML = `
          <i class="fas ${
            isError ? "fa-exclamation-circle" : "fa-check-circle"
          }"></i>
          <span>${message}</span>
        `;
        toastContainer.appendChild(toast);
        setTimeout(() => {
          if (toast.parentNode === toastContainer) {
            toast.remove();
          }
        }, 3000);
      }

      function showEmptyCartPrompt() {
        if (emptyCartPrompt) {
          emptyCartPrompt.style.display = "flex";
          document.body.style.overflow = "hidden";
        }
      }

      function closeEmptyCartPrompt() {
        if (emptyCartPrompt) {
          emptyCartPrompt.style.display = "none";
          document.body.style.overflow = "auto";
        }
      }

      // Initialize the App
      init();
    });

    // WhatsApp link
    const whatsappNumber = "2349064296917";
    const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(
      "Hello, I would like to place an order"
    )}`;
    </script>
  </body>
</html>