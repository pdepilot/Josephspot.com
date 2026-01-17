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
    <title>Gallery - <?php echo !empty($restaurant_info['restaurant_name']) ? htmlspecialchars($restaurant_info['restaurant_name']) : "Joseph's Pot"; ?></title>
    <link rel="stylesheet" href="./CSS/gallery.css" />
    <link rel="icon" href="<?php echo $appearance['favicon_path']; ?>?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="./fontawesome-free-6.7.2-web/css/all.min.css">
    <link rel="preload" href="font.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="./CSS/gallery.css">
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
        <div class="container">
            <div class="logo">
                <a href="index.php"><img src="<?php echo $appearance['logo_path']; ?>?v=<?php echo time(); ?>" alt="Joseph's Pot Logo"></a>
            </div>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="menu.php">Menu</a>
                <a href="gallery.php" class="active">Gallery</a>
                <a href="index.php#eventContainer">Events</a>
                <a href="contact.php">Contact</a>
                <a href="order-online.php">Order Online</a>
                <a href="career.php">Career</a>
            </nav>
            <div class="social">
                <a href="https://www.facebook.com/@cruisewithjoe"><i class="fa-brands fa-facebook"></i></a>
                <a href="https://www.x.com/@cruisewithjoe"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="https://www.youtube.com/@cruisewithjoe"><i class="fab fa-youtube"></i></a>
                <a href="https://www.instagram.com/@cruisewithjoe"><i class="fab fa-instagram"></i></a>
            </div>
            <span class="menu-toggle" onclick="toggleMenu()"><i class="fa-solid fa-utensils"></i></span>
        </div>
    </header>

    <!-- Gallery Header -->
    <section class="gallery-header">
        <h1>Our Delicious Moments</h1>
        <p>Browse through our most mouthwatering dishes and exciting events.</p>
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="food">Food</button>
            <button class="filter-btn" data-filter="drinks">Drinks</button>
            <button class="filter-btn" data-filter="event">Events</button>
            <button class="filter-btn" data-filter="videos">Videos</button>
        </div>
    </section>

   <!-- Gallery Grid -->
<section class="gallery-grid" id="gallery">
    <?php
    // Include database connection
    require_once 'db_connection.php';
    
    // Fetch gallery items from database
    try {
        $sql = "SELECT * FROM gallery WHERE status = 'active' ORDER BY sort_order ASC, upload_date DESC";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();
        
        if (count($rows) > 0) {
            foreach ($rows as $row) {
                $category = $row['category'];
            $file_url = str_replace('../', './', $row['file_path']);
            
            // Use exact category from database for filtering
            $filter_category = $category; // This will be 'food', 'drinks', 'event', or 'videos'
            
            // Map categories to icons
            $icons = [
                'food' => 'fas fa-utensils',
                'drinks' => 'fas fa-glass-whiskey',
                'event' => 'fas fa-calendar-alt',
                'videos' => 'fas fa-play-circle'
            ];
            
            // Default labels based on category
            $default_titles = [
                'food' => 'Delicious Dish',
                'drinks' => 'Refreshing Drink',
                'event' => 'Special Event',
                'videos' => 'Cooking Video'
            ];
            
            $default_descriptions = [
                'food' => 'Our chef\'s special creation with fresh ingredients',
                'drinks' => 'Perfect beverage to complement your meal',
                'event' => 'Memorable gatherings at our restaurant',
                'videos' => 'Watch our culinary experts in action'
            ];
            
            $icon = $icons[$category] ?? 'fas fa-image';
            $display_title = !empty($row['title']) ? htmlspecialchars($row['title']) : 
                ($default_titles[$category] ?? 'Gallery Item');
            $display_description = !empty($row['description']) ? htmlspecialchars($row['description']) : 
                ($default_descriptions[$category] ?? '');
            
            if ($row['file_type'] === 'image') {
                echo '
                <div class="gallery-item ' . $category . '" data-category="' . $filter_category . '">
                    <img src="' . $file_url . '" loading="lazy" alt="' . $display_title . '">
                    <div class="gallery-label">
                        <div class="label-icon"><i class="' . $icon . '"></i></div>
                        <div class="label-title">' . $display_title . '</div>
                        <div class="label-description">' . $display_description . '</div>
                    </div>
                </div>';
            } else { // Video
                echo '
                <div class="gallery-item ' . $category . '" data-category="' . $filter_category . '">
                    <video src="' . $file_url . '" muted autoplay loop playsinline></video>
                    <div class="gallery-label">
                        <div class="label-icon"><i class="' . $icon . '"></i></div>
                        <div class="label-title">' . $display_title . '</div>
                        <div class="label-description">' . $display_description . '</div>
                    </div>
                </div>';
                }
            }
        } else {
            // Show a message when no gallery items exist
            echo '<div class="no-gallery-items" style="grid-column: 1/-1; text-align: center; padding: 50px;">
                    <i class="fas fa-image" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                    <h3 style="color: #666; margin-bottom: 10px;">Gallery Coming Soon</h3>
                    <p style="color: #999;">Our gallery is being updated with delicious images and videos. Please check back later!</p>
                  </div>';
        }
    } catch(PDOException $e) {
        // Show error message if database query fails
        echo '<div class="no-gallery-items" style="grid-column: 1/-1; text-align: center; padding: 50px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                <h3 style="color: #666; margin-bottom: 10px;">Unable to Load Gallery</h3>
                <p style="color: #999;">Please try again later.</p>
              </div>';
    }
    ?>
</section>

    <!-- Lightbox Modal -->
    <div class="lightbox" id="lightbox">
        <span class="close-btn" onclick="closeLightbox()">&times;</span>
        <span class="mute-btn" id="muteBtn" onclick="toggleMute()"><i class="fas fa-volume-mute"></i></span>
        <div class="lightbox-content">
            <img id="lightbox-img" src="" alt="Full Image" />
            <video id="lightbox-video" controls autoplay muted loop></video>
            <div class="lightbox-label" id="lightbox-label">
                <div class="lightbox-title" id="lightbox-title"></div>
                <div class="lightbox-description" id="lightbox-description"></div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-glass">
            <div class="footer-glass-inner">
                <div class="footer-content">
                    <div class="footer-column">
                        <img src="<?php echo $appearance['logo_path']; ?>?v=<?php echo time(); ?>" alt="Joseph's Pot Logo" width="80px" />
                        <p>Authentic taste, unforgettable experience.<br>Serving happiness from Owerri, Nigeria.</p>
                        <div class="social-links">
                            <a href="https://facebook.com/@cruisewithjoe" target="_blank"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://instagram.com/@cruisewithjoe" target="_blank"><i class="fab fa-instagram"></i></a>
                            <a href="https://twitter.com/@cruisewithjoe" target="_blank"><i class="fab fa-twitter"></i></a>
                            <a href="https://youtube.com/@cruisewithjoe" target="_blank"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>

                    <div class="footer-column">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li><a href="about.php">About</a></li>
                            <li><a href="menu.php">Menu</a></li>
                            <li><a href="index.php#eventContainer">Events</a></li>
                            <li><a href="contact.php">Contact us</a></li>
                            <li><a href="order online.php">Order Online</a></li>
                        </ul>
                    </div>

                    <div class="footer-column">
                        <h4><i class="fas fa-clock"></i> Opening Hours</h4>
                        <p>
                            <?php if (!empty($restaurant_info['opening_hours'])): ?>
                                <?php echo nl2br(htmlspecialchars($restaurant_info['opening_hours'])); ?>
                            <?php else: ?>
                                Monday – Friday: 08:30 AM – 9:00 PM<br>
                                Saturday: 08:00 AM – 09:00 PM<br>
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
                            <a href="https://maps.google.com?q=<?php echo urlencode(!empty($restaurant_info['restaurant_name']) ? $restaurant_info['restaurant_name'] . ' Owerri' : "Joseph's Pot Owerri"); ?>" target="_blank">📍 <span>Get Directions</span></a>
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
        <img src="https://cdn-icons-png.flaticon.com/512/124/124034.png" alt="WhatsApp" />
    </a>

   <script>
    // Force CSS variable update after page load (ensures override of static CSS)
    (function() {
        const root = document.documentElement;
        root.style.setProperty('--brown', '<?php echo $appearance['primary_color']; ?>', 'important');
        root.style.setProperty('--brown-light', '<?php echo $appearance['primary_light']; ?>', 'important');
        root.style.setProperty('--brown-dark', '<?php echo $appearance['primary_dark']; ?>', 'important');
    })();

    // Scroll Navbar Effect - fixed missing event listener
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Hamburger Menu Toggle - fixed non-functional function
    window.toggleMenu = function() {
        const navLinks = document.querySelector('.nav-links');
        if (navLinks) {
            navLinks.classList.toggle('active');
        }
    };

    // Gallery Filtering Functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Get all filter buttons and gallery items
        const filterButtons = document.querySelectorAll('.filter-btn');
        const galleryItems = document.querySelectorAll('.gallery-item');
        const galleryContainer = document.getElementById('gallery');
        
        // Create no items message element
        const noItemsMessage = document.createElement('div');
        noItemsMessage.className = 'no-items-message';
        noItemsMessage.style.gridColumn = '1/-1';
        noItemsMessage.style.textAlign = 'center';
        noItemsMessage.style.padding = '50px';
        noItemsMessage.style.display = 'none';
        noItemsMessage.innerHTML = `
            <i class="fas fa-image" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
            <h3 style="color: #666; margin-bottom: 10px;">No items in this category</h3>
            <p style="color: #999;">Check back soon for more content!</p>
        `;
        galleryContainer.appendChild(noItemsMessage);
        
        // Filter function
        function filterGallery(category) {
            let visibleItems = 0;
            
            galleryItems.forEach(item => {
                // Get the item's category
                const itemCategory = item.getAttribute('data-category');
                
                // Show or hide based on filter
                if (category === 'all') {
                    item.style.display = 'block';
                    visibleItems++;
                } else if (itemCategory === category) {
                    item.style.display = 'block';
                    visibleItems++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide no items message
            if (visibleItems === 0 && category !== 'all') {
                noItemsMessage.style.display = 'block';
                
                // Update message based on category
                const categoryNames = {
                    'food': 'food',
                    'drinks': 'drinks',
                    'event': 'event',
                    'videos': 'video'
                };
                
                const iconClass = {
                    'food': 'fa-utensils',
                    'drinks': 'fa-glass-whiskey',
                    'event': 'fa-calendar-alt',
                    'videos': 'fa-play-circle'
                };
                
                const categoryName = categoryNames[category] || 'this category';
                const icon = iconClass[category] || 'fa-image';
                
                noItemsMessage.querySelector('i').className = `fas ${icon}`;
                noItemsMessage.querySelector('h3').textContent = `No ${categoryName} items yet`;
                noItemsMessage.querySelector('p').textContent = `Check back soon for our ${categoryName} gallery!`;
            } else {
                noItemsMessage.style.display = 'none';
            }
            
            // Add fade animation for visible items
            setTimeout(() => {
                galleryItems.forEach(item => {
                    if (item.style.display === 'block') {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.9)';
                    }
                });
            }, 10);
        }
        
        // Add click event to filter buttons
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Get filter value
                const filterValue = this.getAttribute('data-filter');
                
                // Filter gallery items
                filterGallery(filterValue);
            });
        });
        
        // Initialize with all items showing
        filterGallery('all');
        
        // Lightbox functionality
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        const lightboxVideo = document.getElementById('lightbox-video');
        const lightboxTitle = document.getElementById('lightbox-title');
        const lightboxDescription = document.getElementById('lightbox-description');
        const muteBtn = document.getElementById('muteBtn');
        
        // Open lightbox when gallery item is clicked
        galleryItems.forEach(item => {
            item.addEventListener('click', function() {
                const img = this.querySelector('img');
                const video = this.querySelector('video');
                const title = this.querySelector('.label-title').textContent;
                const description = this.querySelector('.label-description').textContent;
                
                // Reset both media elements
                lightboxImg.style.display = 'none';
                lightboxVideo.style.display = 'none';
                
                if (img) {
                    lightboxImg.src = img.src;
                    lightboxImg.style.display = 'block';
                    lightboxVideo.pause();
                } else if (video) {
                    lightboxVideo.src = video.src;
                    lightboxVideo.style.display = 'block';
                    lightboxVideo.play();
                    muteBtn.style.display = 'block';
                }
                
                lightboxTitle.textContent = title;
                lightboxDescription.textContent = description;
                lightbox.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        });
        
        // Close lightbox
        window.closeLightbox = function() {
            lightbox.style.display = 'none';
            lightboxVideo.pause();
            muteBtn.style.display = 'none';
            document.body.style.overflow = 'auto';
        };
        
        // Toggle mute for video
        window.toggleMute = function() {
            if (lightboxVideo.muted) {
                lightboxVideo.muted = false;
                muteBtn.innerHTML = '<i class="fas fa-volume-up"></i>';
            } else {
                lightboxVideo.muted = true;
                muteBtn.innerHTML = '<i class="fas fa-volume-mute"></i>';
            }
        };
        
        // Close lightbox when clicking outside
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
        
        // Close lightbox with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && lightbox.style.display === 'flex') {
                closeLightbox();
            }
        });
        
        // Scroll to top button functionality
        const scrollTopBtn = document.getElementById('scrollTopBtn');
        
        if (scrollTopBtn) {
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    scrollTopBtn.style.display = 'block';
                } else {
                    scrollTopBtn.style.display = 'none';
                }
            });
            
            scrollTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
        
        // Close mobile menu when clicking outside - added missing functionality
        document.addEventListener('click', function(e) {
            const navLinks = document.querySelector('.nav-links');
            const menuToggle = document.querySelector('.menu-toggle');
            
            if (navLinks && navLinks.classList.contains('active')) {
                if (!navLinks.contains(e.target) && !menuToggle.contains(e.target)) {
                    navLinks.classList.remove('active');
                }
            }
        });
    });
</script>
</body>

</html>