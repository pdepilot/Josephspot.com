<?php
// menu.php - Add at the very top
require_once 'db_connection.php';
require_once __DIR__ . '/includes/appearance_settings.php';

// Load restaurant information from database
require_once __DIR__ . '/includes/restaurant_info.php';

// Fetch menu items from database
try {
    $sql = "SELECT * FROM food_menu_manager WHERE is_available = 1 ORDER BY 
            CASE category 
                WHEN 'main-course' THEN 1
                WHEN 'proteins' THEN 2
                WHEN 'swallow' THEN 3
                WHEN 'bulk-orders' THEN 4
                WHEN 'breakfast' THEN 5
                WHEN 'lunch' THEN 6
                WHEN 'dinner' THEN 7
                WHEN 'drinks' THEN 8
                ELSE 9
            END, 
            CASE WHEN is_special = 1 THEN 0 ELSE 1 END,
            name";
    
    $stmt = $pdo->query($sql);
    $menu_items = $stmt->fetchAll();
    
    // Group items by category
    $items_by_category = [];
    foreach($menu_items as $item) {
        $category = $item['category'];
        if(!isset($items_by_category[$category])) {
            $items_by_category[$category] = [];
        }
        $items_by_category[$category][] = $item;
    }
    
} catch(PDOException $e) {
    // If database fails, use empty array
    $items_by_category = [];
    error_log("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($restaurant_info['restaurant_name']) ? htmlspecialchars($restaurant_info['restaurant_name']) : "Joseph's Pot"; ?> - Authentic Igbo Cuisine</title>
    <link rel="icon" href="<?php echo $appearance['favicon_path']; ?>?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="./fontawesome-free-6.7.2-web/css/all.min.css">
    <link rel="preload" href="font.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="./CSS/menu.css">
    
    <!-- Firebase Analytics -->
    <?php require_once __DIR__ . '/includes/firebase-analytics.php'; ?>
    
    <!-- PHP Analytics Tracker -->
    <script src="includes/analytics-tracker.js"></script>
    
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
                <a href="index.php"><img src="<?php echo $appearance['logo_path']; ?>?v=<?php echo time(); ?>" alt="Joseph's Pot Logo"></a>
            </div>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="menu.php" class="active">Menu</a>
                <a href="about.php">About</a>
                <a href="gallery.php">Gallery</a>
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

    <!-- Hero Section -->
    <section class="menu-hero">
        <h1>Joseph's Pot Restaurant</h1>
        <p>Experience the authentic taste of Igbo cuisine with our carefully crafted menu</p>
    </section>

    <!-- Menu Filter -->
    <div class="menu-filter">
        <button class="filter-btn active" data-filter="all">All Items</button>
        <button class="filter-btn" data-filter="breakfast">Breakfast</button>
        <!-- <button class="filter-btn" data-filter="lunch">Lunch</button> -->
        <!-- <button class="filter-btn" data-filter="dinner">Dinner</button> -->
        <button class="filter-btn" data-filter="main-course">Main Course</button>
        <button class="filter-btn" data-filter="proteins">Proteins</button>
        <button class="filter-btn" data-filter="swallow">Swallow</button>
        <button class="filter-btn" data-filter="bulk-orders">Bulk Orders</button>
        <button class="filter-btn" data-filter="drinks">Drinks</button>
    </div>

    <!-- Menu Container -->
    <div class="menu-wrapper">
        <!-- Main Course -->
        <?php if(isset($items_by_category['main-course'])): ?>
        <section class="menu-section fade-in" data-category="main-course">
            <div class="section-header">
                <h2><i class="fas fa-utensils"></i> MAIN COURSE</h2>
                <img src="./images/nsala.jpeg" alt="Main Course" class="section-image">
            </div>
            <div class="menu-items">
                <?php foreach($items_by_category['main-course'] as $item): 
                    $is_special = $item['is_special'];
                    $tags = !empty($item['tags']) ? explode(',', $item['tags']) : [];
                    $display_price = $item['display_price'] ?: '₦' . number_format($item['price']);
                ?>
                <div class="menu-item <?php echo $is_special ? 'special-item' : ''; ?>">
                    <div class="item-info">
                        <div class="item-name">
                            <?php if($item['icon']): ?>
                            <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($item['name']); ?>
                        </div>
                        <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php if(!empty($tags)): ?>
                        <div class="item-tags">
                            <?php foreach($tags as $tag): ?>
                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="item-price"><?php echo htmlspecialchars($display_price); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Proteins -->
        <?php if(isset($items_by_category['proteins'])): ?>
        <section class="menu-section fade-in" data-category="proteins">
            <div class="section-header">
                <h2><i class="fas fa-drumstick-bite"></i> PROTEINS</h2>
                <img src="./images/nkwobi.jpeg" alt="Protein Dishes" class="section-image">
            </div>
            <div class="menu-items">
                <?php foreach($items_by_category['proteins'] as $item): 
                    $is_special = $item['is_special'];
                    $tags = !empty($item['tags']) ? explode(',', $item['tags']) : [];
                    $display_price = $item['display_price'] ?: '₦' . number_format($item['price']);
                ?>
                <div class="menu-item <?php echo $is_special ? 'special-item' : ''; ?>">
                    <div class="item-info">
                        <div class="item-name">
                            <?php if($item['icon']): ?>
                            <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($item['name']); ?>
                        </div>
                        <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php if(!empty($tags)): ?>
                        <div class="item-tags">
                            <?php foreach($tags as $tag): ?>
                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="item-price"><?php echo htmlspecialchars($display_price); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Swallow -->
        <?php if(isset($items_by_category['swallow'])): ?>
        <section class="menu-section fade-in" data-category="swallow">
            <div class="section-header">
                <h2><i class="fas fa-bread-slice"></i> SWALLOW</h2>
                <img src="./images/fufu.jpg" alt="Swallow Dishes" class="section-image">
            </div>
            <div class="menu-items">
                <?php foreach($items_by_category['swallow'] as $item): 
                    $is_special = $item['is_special'];
                    $tags = !empty($item['tags']) ? explode(',', $item['tags']) : [];
                    $display_price = $item['display_price'] ?: '₦' . number_format($item['price']);
                ?>
                <div class="menu-item <?php echo $is_special ? 'special-item' : ''; ?>">
                    <div class="item-info">
                        <div class="item-name">
                            <?php if($item['icon']): ?>
                            <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($item['name']); ?>
                        </div>
                        <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php if(!empty($tags)): ?>
                        <div class="item-tags">
                            <?php foreach($tags as $tag): ?>
                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="item-price"><?php echo htmlspecialchars($display_price); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Bulk Orders -->
        <?php if(isset($items_by_category['bulk-orders'])): ?>
        <section class="menu-section fade-in" data-category="bulk-orders">
            <div class="section-header">
                <h2><i class="fas fa-people-carry"></i> BULK ORDERS</h2>
                <img src="./images/owerri.jpg.png" alt="Bulk Orders" class="section-image">
            </div>
            <div class="menu-items">
                <?php foreach($items_by_category['bulk-orders'] as $item): 
                    $is_special = $item['is_special'];
                    $tags = !empty($item['tags']) ? explode(',', $item['tags']) : [];
                    $display_price = $item['display_price'] ?: '₦' . number_format($item['price']);
                ?>
                <div class="menu-item <?php echo $is_special ? 'special-item' : ''; ?>">
                    <div class="item-info">
                        <div class="item-name">
                            <?php if($item['icon']): ?>
                            <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($item['name']); ?>
                        </div>
                        <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php if(!empty($tags)): ?>
                        <div class="item-tags">
                            <?php foreach($tags as $tag): ?>
                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="item-price"><?php echo htmlspecialchars($display_price); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Breakfast Section -->
        <?php if(isset($items_by_category['breakfast'])): ?>
        <section class="menu-section fade-in" data-category="breakfast">
            <div class="section-header">
                <h2><i class="fas fa-sun"></i> BREAKFAST NRI-UTUTU</h2>
                <img src="./images/brakefast1.jpg" alt="Breakfast" class="section-image">
            </div>
            <div class="menu-items">
                <?php foreach($items_by_category['breakfast'] as $item): 
                    $is_special = $item['is_special'];
                    $tags = !empty($item['tags']) ? explode(',', $item['tags']) : [];
                    $display_price = $item['display_price'] ?: '₦' . number_format($item['price']);
                ?>
                <div class="menu-item <?php echo $is_special ? 'special-item' : ''; ?>">
                    <div class="item-info">
                        <div class="item-name">
                            <?php if($item['icon']): ?>
                            <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($item['name']); ?>
                        </div>
                        <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php if(!empty($tags)): ?>
                        <div class="item-tags">
                            <?php foreach($tags as $tag): ?>
                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="item-price"><?php echo htmlspecialchars($display_price); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Lunch Section -->
        <?php if(isset($items_by_category['lunch'])): ?>
        <section class="menu-section fade-in" data-category="lunch">
            <div class="section-header">
                <h2><i class="fas fa-utensils"></i> LUNCH</h2>
                <img src="./images/nsala.jpeg" alt="Lunch" class="section-image">
            </div>
            <div class="menu-items">
                <?php foreach($items_by_category['lunch'] as $item): 
                    $is_special = $item['is_special'];
                    $tags = !empty($item['tags']) ? explode(',', $item['tags']) : [];
                    $display_price = $item['display_price'] ?: '₦' . number_format($item['price']);
                ?>
                <div class="menu-item <?php echo $is_special ? 'special-item' : ''; ?>">
                    <div class="item-info">
                        <div class="item-name">
                            <?php if($item['icon']): ?>
                            <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($item['name']); ?>
                        </div>
                        <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php if(!empty($tags)): ?>
                        <div class="item-tags">
                            <?php foreach($tags as $tag): ?>
                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="item-price"><?php echo htmlspecialchars($display_price); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Dinner Section -->
        <?php if(isset($items_by_category['dinner'])): ?>
        <section class="menu-section fade-in" data-category="dinner">
            <div class="section-header">
                <h2><i class="fas fa-moon"></i> DINNER</h2>
                <img src="./images/okra.jpeg" alt="Dinner" class="section-image">
            </div>
            <div class="menu-items">
                <?php foreach($items_by_category['dinner'] as $item): 
                    $is_special = $item['is_special'];
                    $tags = !empty($item['tags']) ? explode(',', $item['tags']) : [];
                    $display_price = $item['display_price'] ?: '₦' . number_format($item['price']);
                ?>
                <div class="menu-item <?php echo $is_special ? 'special-item' : ''; ?>">
                    <div class="item-info">
                        <div class="item-name">
                            <?php if($item['icon']): ?>
                            <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($item['name']); ?>
                        </div>
                        <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php if(!empty($tags)): ?>
                        <div class="item-tags">
                            <?php foreach($tags as $tag): ?>
                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="item-price"><?php echo htmlspecialchars($display_price); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Drinks section -->
        <?php if(isset($items_by_category['drinks'])): ?>
        <section class="menu-section fade-in" data-category="drinks">
            <div class="section-header">
                <h2><i class="fas fa-trophy"></i> Drinks</h2>
                <img src="./images/IM30.jpg" alt="Drinks" class="section-image">
            </div>
            <div class="menu-items">
                <?php foreach($items_by_category['drinks'] as $item): 
                    $is_special = $item['is_special'];
                    $tags = !empty($item['tags']) ? explode(',', $item['tags']) : [];
                    $display_price = $item['display_price'] ?: '₦' . number_format($item['price']);
                ?>
                <div class="menu-item <?php echo $is_special ? 'special-item' : ''; ?>">
                    <div class="item-info">
                        <div class="item-name">
                            <?php if($item['icon']): ?>
                            <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($item['name']); ?>
                        </div>
                        <p class="item-description"><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php if(!empty($tags)): ?>
                        <div class="item-tags">
                            <?php foreach($tags as $tag): ?>
                            <span class="tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="item-price"><?php echo htmlspecialchars($display_price); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    <!-- Footer -->
    <footer class="menu-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3><i class="fas fa-utensils"></i> <?php echo !empty($restaurant_info['restaurant_name']) ? htmlspecialchars($restaurant_info['restaurant_name']) : "Joseph's Pot"; ?></h3>
                <p>Authentic Igbo cuisine served with love and tradition. Experience the true taste of Eastern Nigeria.</p>
                <div class="qr-container">
                    <div class="qr-code">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://josephspot.com/menu" alt="Menu QR Code">
                    </div>
                    <p class="qr-text">Scan for Digital Menu</p>
                </div>
            </div>
            
            <div class="footer-section">
                <h3><i class="fas fa-clock"></i> Opening Hours</h3>
                <?php if (!empty($restaurant_info['opening_hours'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($restaurant_info['opening_hours'])); ?></p>
                <?php else: ?>
                    <p>Monday - Friday: 8:30 AM - 9:00 PM</p>
                    <p>Saturday: 8:00 AM - 9:00 PM</p>
                    <p>Sunday: 12:00 PM - 9:00 PM</p>
                <?php endif; ?>
            </div>
            
            <div class="footer-section">
                <h3><i class="fas fa-map-marker-alt"></i> Visit Us</h3>
                <?php if (!empty($restaurant_info['restaurant_address'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($restaurant_info['restaurant_address'])); ?></p>
                <?php else: ?>
                    <p>Plot 120, Ikenegbu Layout</p>
                    <p>Maris Junction, Owerri</p>
                    <p>Imo State, Nigeria</p>
                <?php endif; ?>
                <?php if (!empty($restaurant_info['restaurant_phone'])): ?>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($restaurant_info['restaurant_phone']); ?></p>
                <?php else: ?>
                    <p><i class="fas fa-phone"></i> 08104344994</p>
                <?php endif; ?>
                <div class="social-icons">
                    <a href="https://facebook.com/@cruisewithjoe"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://instagram.com/@cruisewithjoe"><i class="fab fa-instagram"></i></a>
                    <a href="https://twitter.com/@cruisewithjoe"><i class="fab fa-twitter"></i></a>
                    <a href="https://youtube.com/@cruisewithjoe"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 <?php echo !empty($restaurant_info['restaurant_name']) ? htmlspecialchars($restaurant_info['restaurant_name']) : "Joseph's Pot"; ?>. All Rights Reserved | Developed by ERIBS Tech</p>
        </div>
    </footer>

    <!-- Floating Action Buttons -->
    <button id="scrollTopBtn" aria-label="Scroll to Top">↑</button>
    <a href="https://wa.me/2348104344994" class="whatsapp-chat" target="_blank">
        <img src="https://cdn-icons-png.flaticon.com/512/124/124034.png" alt="WhatsApp">
    </a>

    <script src="./JAVASCRIPT/menu.js"></script>
</body>
</html>