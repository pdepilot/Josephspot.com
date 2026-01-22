<?php
// Load appearance settings from database
require_once __DIR__ . '/includes/appearance_settings.php';

// Load restaurant information from database
require_once __DIR__ . '/includes/restaurant_info.php';

// Load careers functions
require_once __DIR__ . '/includes/careers-functions.php';

// Fetch active jobs from database
$activeJobs = [];
try {
    $activeJobs = getJobs(['active_only' => true]);
} catch (Exception $e) {
    error_log("Error fetching jobs: " . $e->getMessage());
    $activeJobs = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo !empty($restaurant_info['restaurant_name']) ? htmlspecialchars($restaurant_info['restaurant_name']) : "Joseph's Pot"; ?> Careers - Join our culinary team in Owerri. Exciting opportunities in Nigerian cuisine, hospitality, and restaurant management.">
    <meta name="keywords" content="restaurant careers, culinary jobs Owerri, hospitality jobs, chef positions, Nigerian cuisine careers">
    <meta name="author" content="<?php echo !empty($restaurant_info['restaurant_name']) ? htmlspecialchars($restaurant_info['restaurant_name']) : "Joseph's Pot"; ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="Careers at <?php echo !empty($restaurant_info['restaurant_name']) ? htmlspecialchars($restaurant_info['restaurant_name']) : "Joseph's Pot"; ?>">
    <meta property="og:description" content="Join our passionate team and grow your career in authentic Nigerian cuisine">
    <meta property="og:image" content="<?php echo $appearance['logo_path']; ?>">
    <meta property="og:url" content="https://josephspot.com/careers.php">
    <meta property="og:type" content="website">

    <link rel="icon" href="<?php echo $appearance['favicon_path']; ?>?v=<?php echo time(); ?>">
    <title>Careers - <?php echo !empty($restaurant_info['restaurant_name']) ? htmlspecialchars($restaurant_info['restaurant_name']) : "Joseph's Pot"; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="./fontawesome-free-6.7.2-web/css/all.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;800&family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="CSS/index.css">
    
    <!-- Firebase Analytics -->
    <?php require_once __DIR__ . '/includes/firebase-analytics.php'; ?>
    
    <!-- PHP Analytics Tracker -->
    <script src="includes/analytics-tracker.js"></script>
    
    <!-- Careers Page Specific CSS -->
    <style>
        /* ===== CAREERS PAGE SPECIFIC STYLES ===== */
        
        /* Career Hero Section - REDUCED HEIGHT */
        .career-hero {
            position: relative;
            height: 60vh;
            min-height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--white);
            overflow: hidden;
        }

        /* Hero Background with Multiple Layers */
        .hero-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .hero-background-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            animation: zoomIn 20s ease-in-out infinite alternate;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                135deg,
                rgba(139, 69, 19, 0.85) 0%,
                rgba(210, 105, 30, 0.75) 50%,
                rgba(255, 228, 181, 0.6) 100%
            );
            z-index: 2;
        }

        .hero-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 2px, transparent 2px),
                radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.1) 2px, transparent 2px);
            background-size: 50px 50px;
            z-index: 3;
            opacity: 0.3;
        }

        .career-hero-content {
            position: relative;
            z-index: 4;
            max-width: 800px;
            padding: 2rem;
            animation: fadeInUp 1s ease-out;
        }

        .career-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.2rem;
            font-family: 'Playfair Display', serif;
            text-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            letter-spacing: 1px;
        }

        .career-hero-subtitle {
            font-size: 1.3rem;
            font-weight: 300;
            margin-bottom: 2rem;
            opacity: 0.95;
            line-height: 1.6;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .career-stats {
            display: flex;
            justify-content: center;
            gap: 3rem;
            margin-top: 2.5rem;
            flex-wrap: wrap;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-item {
            text-align: center;
            position: relative;
        }

        .stat-item:not(:last-child)::after {
            content: '';
            position: absolute;
            right: -1.5rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 40px;
            background: rgba(255, 255, 255, 0.3);
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--accent);
            display: block;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .stat-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: 0.9;
            font-weight: 500;
        }

        /* Scroll Indicator */
        .scroll-indicator {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 4;
            animation: bounce 2s infinite;
        }

        .scroll-indicator i {
            font-size: 1.5rem;
            color: var(--white);
            opacity: 0.8;
        }

        @keyframes zoomIn {
            0% {
                transform: scale(1);
            }
            100% {
                transform: scale(1.05);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateX(-50%) translateY(0);
            }
            40% {
                transform: translateX(-50%) translateY(-10px);
            }
            60% {
                transform: translateX(-50%) translateY(-5px);
            }
        }

        /* Why Join Us Section */
        .why-join-section {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, var(--pale-orange) 0%, var(--pale-orange-light) 100%);
            position: relative;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-size: 3rem;
            color: var(--brown-dark);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--accent);
            border-radius: 2px;
        }

        .section-header p {
            font-size: 1.2rem;
            color: var(--brown);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .benefit-card {
            background: var(--white);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(139, 69, 19, 0.1);
        }

        .benefit-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--brown));
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }

        .benefit-card:hover::before {
            transform: translateX(0);
        }

        .benefit-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            border-color: var(--accent);
        }

        .benefit-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--accent), var(--brown-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: var(--white);
            font-size: 1.8rem;
            box-shadow: 0 5px 15px rgba(210, 105, 30, 0.3);
        }

        .benefit-card h3 {
            font-size: 1.5rem;
            color: var(--brown-dark);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .benefit-card p {
            color: var(--text);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        /* Job Openings Section */
        .jobs-section {
            padding: 5rem 2rem;
            background: var(--white);
            position: relative;
        }

        .jobs-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100px;
            background: linear-gradient(to bottom, var(--pale-orange-light), transparent);
            z-index: 1;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
            position: relative;
            z-index: 2;
        }

        .section-header h2 {
            font-size: 3rem;
            color: var(--brown-dark);
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--accent);
            border-radius: 2px;
        }

        .section-header p {
            font-size: 1.2rem;
            color: var(--brown);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .job-filters {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 2;
        }

        .filter-btn {
            padding: 0.8rem 1.8rem;
            background: var(--pale-orange);
            border: 2px solid transparent;
            border-radius: 30px;
            font-weight: 500;
            color: var(--brown);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .filter-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: var(--brown);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .filter-btn.active,
        .filter-btn:hover {
            color: var(--white);
            border-color: var(--brown);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.2);
        }

        .filter-btn.active::before,
        .filter-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .filter-btn span {
            position: relative;
            z-index: 1;
        }

        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .job-card {
            background: var(--white);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(139, 69, 19, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .job-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent), var(--brown-light));
            border-radius: 0 16px 0 100px;
            opacity: 0.1;
        }

        .job-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            border-color: var(--accent);
        }

        .job-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 0.4rem 1rem;
            background: var(--brown-light);
            color: var(--white);
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }

        .job-badge.full-time {
            background: linear-gradient(135deg, var(--accent), #e74c3c);
        }

        .job-badge.part-time {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }

        .job-badge.internship {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }

        .job-title {
            font-size: 1.8rem;
            color: var(--brown-dark);
            margin-bottom: 0.5rem;
            font-weight: 700;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .job-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--accent);
            border-radius: 2px;
        }

        .job-department {
            color: var(--accent);
            font-weight: 500;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .job-department i {
            font-size: 1rem;
        }

        .job-details {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .job-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .job-detail i {
            color: var(--accent);
        }

        .job-description {
            color: var(--text);
            line-height: 1.6;
            margin-bottom: 2rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .job-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .view-job-btn,
        .apply-btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            font-family: inherit;
            position: relative;
            overflow: hidden;
        }

        .view-job-btn {
            background: transparent;
            border: 2px solid var(--brown);
            color: var(--brown);
        }

        .view-job-btn:hover {
            background: var(--brown);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.2);
        }

        .apply-btn {
            background: linear-gradient(135deg, var(--accent), var(--brown));
            border: none;
            color: var(--white);
            box-shadow: 0 4px 10px rgba(210, 105, 30, 0.3);
        }

        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(210, 105, 30, 0.4);
        }

        /* Job Details Modal */
        .job-details-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 10001;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(8px);
            padding: 1rem;
        }

        .job-details-content {
            background: var(--white);
            border-radius: 20px;
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: modalSlideIn 0.4s ease;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
        }

        .job-details-header {
            padding: 2.5rem;
            background: linear-gradient(135deg, var(--brown-dark) 0%, var(--brown) 100%);
            color: var(--white);
            border-radius: 20px 20px 0 0;
            position: relative;
        }

        .job-details-header h3 {
            font-size: 2.2rem;
            margin: 0 0 0.5rem 0;
            color: var(--white);
            font-weight: 700;
        }

        .job-details-header .job-type {
            display: inline-block;
            padding: 0.4rem 1.2rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
        }

        .job-details-meta {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .job-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }

        .job-meta-item i {
            color: var(--accent);
            font-size: 1.1rem;
        }

        .close-job-details {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--white);
            font-size: 2rem;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-job-details:hover {
            background: var(--accent);
            transform: rotate(90deg);
        }

        .job-details-body {
            padding: 2.5rem;
        }

        .job-section {
            margin-bottom: 2.5rem;
        }

        .job-section h4 {
            font-size: 1.5rem;
            color: var(--brown-dark);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--pale-orange);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .job-section h4 i {
            color: var(--accent);
        }

        .job-description-full {
            color: var(--text);
            line-height: 1.8;
            font-size: 1.05rem;
            margin-bottom: 1.5rem;
        }

        .qualifications-list,
        .responsibilities-list,
        .benefits-list {
            list-style: none;
            padding: 0;
        }

        .qualifications-list li,
        .responsibilities-list li,
        .benefits-list li {
            padding: 0.75rem 0 0.75rem 2rem;
            position: relative;
            color: var(--text);
            line-height: 1.6;
            border-bottom: 1px solid rgba(139, 69, 19, 0.1);
        }

        .qualifications-list li:before,
        .responsibilities-list li:before,
        .benefits-list li:before {
            content: '';
            position: absolute;
            left: 0;
            top: 1.1rem;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent);
        }

        .job-details-footer {
            padding: 2rem 2.5rem;
            border-top: 1px solid rgba(139, 69, 19, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .salary-info {
            font-size: 1.2rem;
            color: var(--brown-dark);
            font-weight: 600;
        }

        .salary-info i {
            color: var(--accent);
            margin-right: 0.5rem;
        }

        /* Application Modal */
        .application-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: var(--white);
            border-radius: 20px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: modalSlideIn 0.4s ease;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, var(--brown) 0%, var(--brown-dark) 100%);
            color: var(--white);
            border-radius: 20px 20px 0 0;
        }

        .modal-header h3 {
            font-size: 1.8rem;
            color: var(--white);
            margin: 0;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 2rem;
            color: var(--white);
            cursor: pointer;
            transition: color 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close-modal:hover {
            color: var(--accent);
            background: rgba(255, 255, 255, 0.1);
        }

        .modal-body {
            padding: 2rem;
        }

        /* Application Form */
        .application-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--brown);
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 1rem;
            border: 2px solid rgba(139, 69, 19, 0.2);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
            color: #333;
        }
        
        .form-group textarea {
            color: #333;
        }
        
        .form-group input[type="text"]::placeholder,
        .form-group textarea::placeholder {
            color: #999;
            opacity: 1;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(210, 105, 30, 0.1);
            color: #333;
        }

        .file-upload {
            border: 2px dashed rgba(139, 69, 19, 0.3);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload:hover {
            border-color: var(--accent);
            background: rgba(210, 105, 30, 0.05);
        }

        .file-upload input[type="file"] {
            display: none;
        }

        .upload-icon {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }

        .upload-text {
            color: var(--text);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .upload-hint {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .selected-files {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem;
            background: rgba(139, 69, 19, 0.05);
            border-radius: 5px;
        }

        .remove-file {
            color: #e74c3c;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1.2rem;
        }

        .form-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .form-checkbox input[type="checkbox"] {
            margin-top: 0.25rem;
        }

        .form-checkbox label {
            font-size: 0.9rem;
            color: var(--text);
            line-height: 1.4;
        }

        .form-checkbox a {
            color: var(--accent);
            text-decoration: none;
        }

        .form-checkbox a:hover {
            text-decoration: underline;
        }

        .submit-application {
            background: linear-gradient(135deg, var(--accent), var(--brown));
            color: var(--white);
            border: none;
            padding: 1.2rem 2rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .submit-application:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(210, 105, 30, 0.3);
        }

        .submit-application:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Team Culture Section */
        .culture-section {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, var(--brown) 0%, var(--brown-dark) 100%);
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .culture-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path fill="rgba(255,255,255,0.05)" d="M50,0 C77.6142375,0 100,22.3857625 100,50 C100,77.6142375 77.6142375,100 50,100 C22.3857625,100 0,77.6142375 0,50 C0,22.3857625 22.3857625,0 50,0 Z M50,20 C63.254834,20 74,30.745166 74,44 C74,57.254834 63.254834,68 50,68 C36.745166,68 26,57.254834 26,44 C26,30.745166 36.745166,20 50,20 Z"></path></svg>');
            background-size: 200px;
            opacity: 0.1;
        }

        .culture-content {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .culture-quote {
            font-size: 2rem;
            font-style: italic;
            margin-bottom: 3rem;
            position: relative;
            padding: 0 2rem;
            line-height: 1.6;
        }

        .culture-quote::before,
        .culture-quote::after {
            content: '"';
            font-size: 4rem;
            color: var(--accent);
            position: absolute;
            opacity: 0.5;
        }

        .culture-quote::before {
            top: -20px;
            left: 0;
        }

        .culture-quote::after {
            bottom: -40px;
            right: 0;
        }

        .culture-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 4rem;
        }

        .culture-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            transition: transform 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .culture-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--accent);
        }

        .culture-item h4 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--white);
        }

        .culture-item p {
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .career-hero {
                height: 55vh;
                min-height: 450px;
            }
            
            .career-hero h1 {
                font-size: 2.8rem;
            }
            
            .career-hero-subtitle {
                font-size: 1.1rem;
            }
            
            .section-header h2 {
                font-size: 2.5rem;
            }
            
            .benefits-grid,
            .jobs-grid {
                grid-template-columns: 1fr;
            }
            
            .job-filters {
                justify-content: flex-start;
                overflow-x: auto;
                padding-bottom: 1rem;
            }
            
            .filter-btn {
                white-space: nowrap;
            }
            
            .stat-item:not(:last-child)::after {
                display: none;
            }
            
            .job-details-content {
                width: 95%;
            }
            
            .job-details-header h3 {
                font-size: 1.8rem;
            }
            
            .job-details-meta {
                gap: 1rem;
            }
        }

        @media (max-width: 768px) {
            .career-hero {
                height: 50vh;
                min-height: 400px;
            }
            
            .career-hero h1 {
                font-size: 2.3rem;
            }
            
            .career-stats {
                gap: 2rem;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
            
            .modal-content {
                width: 95%;
                margin: 1rem;
            }
            
            .job-actions {
                flex-direction: column;
            }
            
            .view-job-btn,
            .apply-btn {
                width: 100%;
                justify-content: center;
            }
            
            .culture-quote {
                font-size: 1.5rem;
                padding: 0 1rem;
            }
            
            .job-details-header {
                padding: 1.5rem;
            }
            
            .job-details-body {
                padding: 1.5rem;
            }
            
            .job-details-footer {
                padding: 1.5rem;
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            .career-hero {
                height: 45vh;
                min-height: 350px;
            }
            
            .career-hero h1 {
                font-size: 1.8rem;
            }
            
            .career-hero-subtitle {
                font-size: 1rem;
            }
            
            .section-header h2 {
                font-size: 2rem;
            }
            
            .modal-body {
                padding: 1.5rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .career-stats {
                gap: 1.5rem;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .stat-label {
                font-size: 0.8rem;
            }
            
            .job-details-header h3 {
                font-size: 1.5rem;
            }
            
            .job-meta-item {
                font-size: 0.9rem;
            }
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.8s ease-out forwards;
            opacity: 0;
        }

        .fade-in-delay-1 {
            animation-delay: 0.2s;
        }

        .fade-in-delay-2 {
            animation-delay: 0.4s;
        }

        .fade-in-delay-3 {
            animation-delay: 0.6s;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
            from {
                opacity: 0;
                transform: translateY(20px);
            }
        }

        /* Success/Error Messages */
        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            color: white;
            z-index: 10001;
            animation: slideInRight 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            max-width: 400px;
        }

        .message.success {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }

        .message.error {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Loading Spinner */
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--white);
            animation: spin 1s ease-in-out infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
    <!-- Preloader (Copied from index.php) -->
    <div class="preloader">
      <div class="ingredients">
        <div class="ingredient">
          <img
            src="https://pngimg.com/uploads/tomato/tomato_PNG12563.png"
            alt="Tomato"
          />
        </div>
        <div class="ingredient">
          <img src="./images/chilli1-removebg-preview.png" alt="Chili" />
        </div>
        <div class="ingredient">
          <img src="./images/basil-removebg-preview.png" alt="Basil" />
        </div>
        <div class="ingredient">
          <img src="./images/garlic-removebg-preview.png" alt="Garlic" />
        </div>
        <div class="ingredient">
          <img src="./images/onioins-removebg-preview.png" alt="Onion" />
        </div>

        <div class="knife-container">
          <div class="knife">
            <div class="knife-edge"></div>
            <div class="knife-blade"></div>
            <div class="knife-handle"></div>
          </div>
        </div>
        <div class="chopping-board"></div>
      </div>

      <div class="loading-text">Preparing Your Joseph's Pot Experience</div>
      <div class="loading-subtext">
        Fresh ingredients are being chopped just for you
      </div>

      <div class="progress-container">
        <div class="progress-bar" id="progress-bar"></div>
      </div>
      <div class="percentage" id="percentage">0%</div>
    </div>
    <div class="content" style="opacity: 0; transition: opacity 1s ease;"></div>

    <!-- Navbar (Copied from index.php) -->
    <header class="navbar" id="navbar">
        <div class="containerr">
          <div class="logo">
           <a href="index.php"><img src="./images/logo3.png" alt="logo" loading="lazy"></a>
          </div>
          <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="menu.php">Menu</a>
            <a href="contact.php">Contact</a>
            <a href="gallery.php">Gallery</a>
            <a href="#eventContainer">Events</a>
            <a href="./order-online.php">Order Online</a>
            <a href="careers.php" class="active">Careers</a>
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

    <!-- Career Hero Section -->
    <section class="career-hero">
        <!-- Background with Image -->
        <div class="hero-background">
            <img src="./images/611274538_866211769471498_3515934356623108686_n.jpeg" 
                 alt="Professional kitchen team preparing food" 
                 class="hero-background-image">
            <div class="hero-overlay"></div>
            <div class="hero-pattern"></div>
        </div>
        
        <div class="career-hero-content" data-aos="fade-up">
            <h1>Join Our Culinary Family</h1>
            <p class="career-hero-subtitle">
                Be part of something extraordinary. At Joseph's Pot, we don't just serve food — we create experiences, 
                preserve traditions, and build lasting memories. Grow your career with Nigeria's finest culinary team.
            </p>
            <div class="career-stats">
                <div class="stat-item">
                    <span class="stat-number">50+</span>
                    <span class="stat-label">Team Members</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">15</span>
                    <span class="stat-label">Open Positions</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">98%</span>
                    <span class="stat-label">Satisfaction Rate</span>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- Why Join Us Section -->
    <section class="why-join-section">
        <div class="section-header" data-aos="fade-up">
            <h2>Why Join Joseph's Pot?</h2>
            <p>We believe in nurturing talent, celebrating creativity, and building a community where everyone thrives.</p>
        </div>
        
        <div class="benefits-grid">
            <div class="benefit-card" data-aos="fade-up" data-aos-delay="100">
                <div class="benefit-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3>Continuous Learning</h3>
                <p>Regular training sessions, workshops with master chefs, and opportunities to explore new culinary techniques.</p>
            </div>
            
            <div class="benefit-card" data-aos="fade-up" data-aos-delay="200">
                <div class="benefit-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Health & Wellness</h3>
                <p>Comprehensive health insurance, mental wellness programs, and nutritious staff meals provided daily.</p>
            </div>
            
            <div class="benefit-card" data-aos="fade-up" data-aos-delay="300">
                <div class="benefit-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Career Growth</h3>
                <p>Clear career progression paths, mentorship programs, and opportunities for leadership development.</p>
            </div>
            
            <div class="benefit-card" data-aos="fade-up" data-aos-delay="400">
                <div class="benefit-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Family Culture</h3>
                <p>We're more than colleagues — we're family. Regular team-building events and a supportive work environment.</p>
            </div>
            
            <div class="benefit-card" data-aos="fade-up" data-aos-delay="500">
                <div class="benefit-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h3>Recognition & Rewards</h3>
                <p>Monthly recognition programs, performance bonuses, and celebrations of personal and professional milestones.</p>
            </div>
            
            <div class="benefit-card" data-aos="fade-up" data-aos-delay="600">
                <div class="benefit-icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <h3>Work-Life Balance</h3>
                <p>Flexible scheduling options, paid time off, and respect for personal time and family commitments.</p>
            </div>
        </div>
    </section>

    <!-- Job Openings Section -->
    <section class="jobs-section" id="job-openings">
        <div class="section-header" data-aos="fade-up">
            <h2>Current Openings</h2>
            <p>Explore exciting opportunities to join our passionate team</p>
        </div>
        
        <div class="job-filters" data-aos="fade-up">
            <button class="filter-btn active" data-filter="all">
                <span>All Positions</span>
            </button>
            <button class="filter-btn" data-filter="kitchen">
                <span>Kitchen</span>
            </button>
            <button class="filter-btn" data-filter="service">
                <span>Waiters/Waitresses</span>
            </button>
            <button class="filter-btn" data-filter="management">
                <span>Management</span>
            </button>
            <button class="filter-btn" data-filter="internship">
                <span>Internships</span>
            </button>
        </div>
        
        <div class="jobs-grid">
            <?php 
            // Helper function to map department to category for filtering
            function getJobCategory($department) {
                $categoryMap = [
                    'Kitchen' => 'kitchen',
                    'Service' => 'service',
                    'Management' => 'management',
                    'Front of House' => 'service',
                    'Back of House' => 'kitchen'
                ];
                return $categoryMap[$department] ?? 'management';
            }
            
            // Helper function to get badge class from job type
            function getJobBadgeClass($jobType) {
                $badgeMap = [
                    'Full Time' => 'full-time',
                    'Part Time' => 'part-time',
                    'Contract' => 'contract',
                    'Internship' => 'internship'
                ];
                return $badgeMap[$jobType] ?? 'full-time';
            }
            
            // Helper function to get department icon
            function getDepartmentIcon($department) {
                $iconMap = [
                    'Kitchen' => 'fa-utensils',
                    'Service' => 'fa-concierge-bell',
                    'Management' => 'fa-bullhorn',
                    'Front of House' => 'fa-user-tie',
                    'Back of House' => 'fa-utensil-spoon'
                ];
                return $iconMap[$department] ?? 'fa-briefcase';
            }
            
            if (empty($activeJobs)): ?>
                <div class="no-jobs-message" style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--text-light);">
                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <h3>No Job Openings at the Moment</h3>
                    <p>We're not actively hiring right now, but we'd love to hear from you! Please check back later or submit your resume for future opportunities.</p>
                </div>
            <?php else: 
                $delay = 0;
                foreach ($activeJobs as $job): 
                    $jobCategory = getJobCategory($job['department']);
                    $badgeClass = getJobBadgeClass($job['job_type']);
                    $departmentIcon = getDepartmentIcon($job['department']);
                    $description = htmlspecialchars(substr($job['description'], 0, 150)) . '...';
                    $location = htmlspecialchars($job['location']);
                    $salary = !empty($job['salary_range']) ? htmlspecialchars($job['salary_range']) : 'Competitive Salary';
            ?>
            <div class="job-card" data-category="<?php echo $jobCategory; ?>" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                <span class="job-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($job['job_type']); ?></span>
                <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                <div class="job-department">
                    <i class="fas <?php echo $departmentIcon; ?>"></i>
                    <?php echo htmlspecialchars($job['department']); ?>
                </div>
                <div class="job-details">
                    <div class="job-detail">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo $location; ?>
                    </div>
                    <?php if (!empty($job['application_deadline'])): ?>
                    <div class="job-detail">
                        <i class="fas fa-calendar-alt"></i>
                        Deadline: <?php echo date('M d, Y', strtotime($job['application_deadline'])); ?>
                    </div>
                    <?php endif; ?>
                    <div class="job-detail">
                        <i class="fas fa-money-bill-wave"></i>
                        <?php echo $salary; ?>
                    </div>
                </div>
                <p class="job-description">
                    <?php echo $description; ?>
                </p>
                <div class="job-actions">
                    <button class="view-job-btn" onclick="viewJobDetails(<?php echo $job['id']; ?>)">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                    <button class="apply-btn" onclick="openApplicationModal(<?php echo $job['id']; ?>)">
                        <i class="fas fa-paper-plane"></i> Apply Now
                    </button>
                </div>
            </div>
            <?php 
                $delay += 100;
                endforeach; 
            endif; 
            ?>
        </div>
    </section>

    <!-- Team Culture Section -->
    <section class="culture-section">
        <div class="culture-content" data-aos="fade-up">
            <div class="culture-quote">
                "At Joseph's Pot, we don't just cook food — we create memories, preserve traditions, 
                and build a family that extends beyond the kitchen doors."
            </div>
            <h3>Our Work Culture</h3>
            
            <div class="culture-grid">
                <div class="culture-item" data-aos="fade-up" data-aos-delay="100">
                    <h4>Collaborative Spirit</h4>
                    <p>We believe in teamwork and support each other to achieve excellence.</p>
                </div>
                
                <div class="culture-item" data-aos="fade-up" data-aos-delay="200">
                    <h4>Innovation Focus</h4>
                    <p>Encouraging creative ideas and new approaches to traditional cuisine.</p>
                </div>
                
                <div class="culture-item" data-aos="fade-up" data-aos-delay="300">
                    <h4>Continuous Growth</h4>
                    <p>Investing in our team's development through training and mentorship.</p>
                </div>
                
                <div class="culture-item" data-aos="fade-up" data-aos-delay="400">
                    <h4>Work-Life Harmony</h4>
                    <p>Respecting personal time while pursuing professional excellence.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Job Details Modal -->
    <div id="jobDetailsModal" class="job-details-modal">
        <div class="job-details-content">
            <div class="job-details-header">
                <button class="close-job-details" onclick="closeJobDetailsModal()">&times;</button>
                <h3 id="modalJobTitle">Job Title</h3>
                <div class="job-type" id="modalJobType">Full Time</div>
                <div class="job-details-meta">
                    <div class="job-meta-item">
                        <i class="fas fa-utensils"></i>
                        <span id="modalJobDepartment">Kitchen Department</span>
                    </div>
                    <div class="job-meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span id="modalJobLocation">Owerri, Imo State</span>
                    </div>
                    <div class="job-meta-item">
                        <i class="fas fa-clock"></i>
                        <span id="modalJobExperience">5+ Years Experience</span>
                    </div>
                    <div class="job-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span id="modalJobPosted">Posted: 2 weeks ago</span>
                    </div>
                </div>
            </div>
            
            <div class="job-details-body">
                <div class="job-section">
                    <h4><i class="fas fa-align-left"></i> Job Description</h4>
                    <p id="modalJobDescription" class="job-description-full">
                        Full job description will appear here...
                    </p>
                </div>
                
                <div class="job-section">
                    <h4><i class="fas fa-graduation-cap"></i> Qualifications & Requirements</h4>
                    <ul id="modalJobQualifications" class="qualifications-list">
                        <!-- Qualifications will be populated by JavaScript -->
                    </ul>
                </div>
                
                <div class="job-section">
                    <h4><i class="fas fa-tasks"></i> Key Responsibilities</h4>
                    <ul id="modalJobResponsibilities" class="responsibilities-list">
                        <!-- Responsibilities will be populated by JavaScript -->
                    </ul>
                </div>
                
                <div class="job-section">
                    <h4><i class="fas fa-gift"></i> What We Offer</h4>
                    <ul id="modalJobBenefits" class="benefits-list">
                        <!-- Benefits will be populated by JavaScript -->
                    </ul>
                </div>
            </div>
            
            <div class="job-details-footer">
                <div class="salary-info">
                    <i class="fas fa-money-bill-wave"></i>
                    <span id="modalJobSalary">Competitive Salary + Benefits</span>
                </div>
                <button class="apply-btn" id="applyFromDetails" onclick="applyFromJobDetails()">
                    <i class="fas fa-paper-plane"></i> Apply for this Position
                </button>
            </div>
        </div>
    </div>

    <!-- Application Modal -->
    <div id="applicationModal" class="application-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Apply for Position</h3>
                <button class="close-modal" onclick="closeApplicationModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="applicationForm" class="application-form" enctype="multipart/form-data">
                    <input type="hidden" id="jobId" name="jobId">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name *</label>
                            <input type="text" id="firstName" name="firstName" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="lastName">Last Name *</label>
                            <input type="text" id="lastName" name="lastName" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="experience">Years of Experience *</label>
                            <input type="number" id="experience" name="experience" min="0" max="50" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="currentRole">Current/Most Recent Role</label>
                            <input type="text" id="currentRole" name="currentRole">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="coverLetter">Cover Letter *</label>
                        <textarea id="coverLetter" name="coverLetter" rows="5" 
                                  placeholder="Tell us why you're interested in this position and what makes you a great fit..." required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Upload Documents *</label>
                        <div class="file-upload" onclick="document.getElementById('cvUpload').click()">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="upload-text">
                                Click to upload your CV and Cover Letter
                            </div>
                            <div class="upload-hint">
                                PDF, DOC, DOCX files only (Max 5MB each)
                            </div>
                            <input type="file" id="cvUpload" name="cv" accept=".pdf,.doc,.docx" multiple onchange="handleFileUpload(this)">
                        </div>
                        <div id="selectedFiles" class="selected-files"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="portfolio">Portfolio Link (Optional)</label>
                        <input type="text" id="portfolio" name="portfolio" placeholder="https://your-portfolio.com">
                    </div>
                    
                    <div class="form-checkbox">
                        <input type="checkbox" id="privacyPolicy" name="privacyPolicy" required>
                        <label for="privacyPolicy">
                            I agree to the <a href="privacy-policy.php" target="_blank">Privacy Policy</a> and consent to having my data processed for recruitment purposes.
                        </label>
                    </div>
                    
                    <div class="form-checkbox">
                        <input type="checkbox" id="futureOpportunities" name="futureOpportunities">
                        <label for="futureOpportunities">
                            Keep my application on file for future opportunities that match my profile.
                        </label>
                    </div>
                    
                    <button type="submit" class="submit-application" id="submitApplicationBtn">
                        <span id="submitText">Submit Application</span>
                        <div id="submitSpinner" class="spinner" style="display: none;"></div>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-glass">
            <div class="footer-glass-inner">
                <div class="footer-content">
                    <div class="footer-column">
                        <img src="./images/logo3.png" loading="lazy" alt="Logo" height="80px">
                        <p>Authentic taste, unforgettable experience.<br>Serving happiness from Owerri, Nigeria.</p>
                        <div class="social-links">
                            <a href="https://www.facebook.com/@cruisewithjoe" target="_blank"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://www.instagram.com/@josephs_pot"><i class="fab fa-instagram"></i></a>
                            <a href="https://www.x.com/@cruisewithjoe" target="_blank"><i class="fab fa-twitter"></i></a>
                            <a href="https://tiktok.com/@josephspot" target="_blank"><i class="fab fa-tiktok"></i></a>
                            <a href="https://www.youtube.com/@cruisewithjoe"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>

                    <div class="footer-column">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="index.php">Home</a></li>
                            <li><a href="about.php">About</a></li>
                            <li><a href="menu.php">Menu</a></li>
                            <li><a href="gallery.php">Gallery</a></li>
                            <li><a href="#eventContainer">Events</a></li>
                            <li><a href="contact.php">Contact</a></li>
                            <li><a href="order-online.php">Order Online</a></li>
                            <li><a href="careers.php">Careers</a></li>
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
                        <h4><i class="fas fa-map-marker-alt"></i>Visit Us</h4>
                        <p>
                            <?php if (!empty($restaurant_info['restaurant_address'])): ?>
                                <?php echo nl2br(htmlspecialchars($restaurant_info['restaurant_address'])); ?><br>
                            <?php else: ?>
                                123 Food Street,<br>
                                Ikenegbu Layout, Owerri<br>
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

    <!-- Scroll to Top Button -->
    <button id="scrollTopBtn" aria-label="Scroll to Top">
      ↑
    </button>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // Preloader functionality
        document.addEventListener("DOMContentLoaded", function () {
            const progressBar = document.getElementById("progress-bar");
            const percentage = document.getElementById("percentage");
            const preloader = document.querySelector(".preloader");
            const content = document.querySelector(".content");

            if (progressBar && percentage && preloader) {
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 20 + 10;
                    if (progress >= 100) {
                        progress = 100;
                        clearInterval(interval);
                        setTimeout(() => {
                            preloader.style.opacity = "0";
                            setTimeout(() => {
                                preloader.style.display = "none";
                                if (content) {
                                    content.style.display = "block";
                                    content.style.opacity = "1";
                                }
                            }, 2000);
                        }, 2000);
                    }
                    progressBar.style.width = progress + "%";
                    percentage.textContent = Math.round(progress) + "%";
                }, 80);
            }

            // Initialize navbar scroll effect
            const navbar = document.getElementById("navbar");
            if (navbar) {
                window.addEventListener("scroll", function () {
                    if (window.scrollY > 50) {
                        navbar.classList.add("scrolled");
                    } else {
                        navbar.classList.remove("scrolled");
                    }
                });
            }

            // Scroll to top button
            const scrollBtn = document.getElementById("scrollTopBtn");
            if (scrollBtn) {
                window.onscroll = function () {
                    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                        scrollBtn.style.display = "block";
                    } else {
                        scrollBtn.style.display = "none";
                    }
                };
                scrollBtn.onclick = function () {
                    window.scrollTo({ top: 0, behavior: "smooth" });
                };
            }

            // Smooth scroll for scroll indicator
            const scrollIndicator = document.querySelector('.scroll-indicator');
            if (scrollIndicator) {
                scrollIndicator.addEventListener('click', function() {
                    window.scrollTo({
                        top: window.innerHeight,
                        behavior: 'smooth'
                    });
                });
            }
        });

        // Mobile menu toggle
        function toggleMenu() {
            const navLinks = document.querySelector(".nav-links");
            if (navLinks) {
                navLinks.classList.toggle("active");
            }
        }

        // Job Filtering with improved animations
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const jobCards = document.querySelectorAll('.job-card');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => {
                        btn.classList.remove('active');
                        btn.style.color = ''; // Reset color
                    });
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    const filterValue = this.getAttribute('data-filter');
                    
                    // Filter job cards with smooth animation
                    jobCards.forEach(card => {
                        const category = card.getAttribute('data-category');
                        
                        if (filterValue === 'all' || category === filterValue) {
                            // Show card with animation
                            card.style.display = 'block';
                            setTimeout(() => {
                                card.style.opacity = '1';
                                card.style.transform = 'translateY(0) scale(1)';
                            }, 50);
                        } else {
                            // Hide card with animation
                            card.style.opacity = '0';
                            card.style.transform = 'translateY(20px) scale(0.95)';
                            setTimeout(() => {
                                card.style.display = 'none';
                            }, 300);
                        }
                    });
                });
            });
        });

        // Job Details Data - Generated from Database
        const jobDetailsData = <?php 
            if (!empty($activeJobs)) {
                $jobDataArray = [];
                foreach ($activeJobs as $job) {
                    // Parse requirements and responsibilities (assuming they're stored as text with bullet points or newlines)
                    $requirements = !empty($job['requirements']) ? $job['requirements'] : '';
                    $responsibilities = !empty($job['responsibilities']) ? $job['responsibilities'] : '';
                    $benefits = !empty($job['benefits']) ? $job['benefits'] : '';
                    
                    // Convert newline-separated text to arrays
                    $requirementsList = array_filter(array_map('trim', explode("\n", str_replace(['•', '-', '*'], '', $requirements))));
                    $responsibilitiesList = array_filter(array_map('trim', explode("\n", str_replace(['•', '-', '*'], '', $responsibilities))));
                    $benefitsList = array_filter(array_map('trim', explode("\n", str_replace(['•', '-', '*'], '', $benefits))));
                    
                    // If no items found, create default arrays
                    if (empty($requirementsList)) {
                        $requirementsList = ['See job description for requirements'];
                    }
                    if (empty($responsibilitiesList)) {
                        $responsibilitiesList = ['See job description for responsibilities'];
                    }
                    if (empty($benefitsList)) {
                        $benefitsList = ['Competitive salary and benefits'];
                    }
                    
                    $postedText = "Posted: " . date('F j, Y', strtotime($job['created_at'] ?? 'now'));
                    $experienceText = !empty($job['requirements']) ? 'See requirements below' : 'Experience preferred';
                    $salary = !empty($job['salary_range']) ? htmlspecialchars($job['salary_range'], ENT_QUOTES) : 'Competitive Salary';
                    
                    $jobDataArray[$job['id']] = [
                        'title' => $job['title'],
                        'type' => $job['job_type'],
                        'department' => $job['department'],
                        'location' => $job['location'],
                        'experience' => $experienceText,
                        'posted' => $postedText,
                        'salary' => $salary,
                        'description' => $job['description'],
                        'qualifications' => array_values($requirementsList),
                        'responsibilities' => array_values($responsibilitiesList),
                        'benefits' => array_values($benefitsList)
                    ];
                }
                echo json_encode($jobDataArray, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
            } else {
                echo '{}';
            }
        ?>;

        // Job Details Modal Functions
        function viewJobDetails(jobId) {
            if (!jobDetailsData || typeof jobDetailsData !== 'object') {
                console.error('Job details data not available');
                return;
            }
            // Convert jobId to string since JSON keys are strings
            const jobData = jobDetailsData[String(jobId)];
            if (!jobData) {
                console.error('Job not found:', jobId);
                return;
            }
            
            // Populate modal with job data
            document.getElementById('modalJobTitle').textContent = jobData.title;
            document.getElementById('modalJobType').textContent = jobData.type;
            document.getElementById('modalJobDepartment').textContent = jobData.department;
            document.getElementById('modalJobLocation').textContent = jobData.location;
            document.getElementById('modalJobExperience').textContent = jobData.experience;
            document.getElementById('modalJobPosted').textContent = jobData.posted;
            document.getElementById('modalJobSalary').textContent = jobData.salary;
            document.getElementById('modalJobDescription').textContent = jobData.description;
            
            // Populate qualifications
            const qualificationsList = document.getElementById('modalJobQualifications');
            qualificationsList.innerHTML = '';
            jobData.qualifications.forEach(qual => {
                const li = document.createElement('li');
                li.textContent = qual;
                qualificationsList.appendChild(li);
            });
            
            // Populate responsibilities
            const responsibilitiesList = document.getElementById('modalJobResponsibilities');
            responsibilitiesList.innerHTML = '';
            jobData.responsibilities.forEach(resp => {
                const li = document.createElement('li');
                li.textContent = resp;
                responsibilitiesList.appendChild(li);
            });
            
            // Populate benefits
            const benefitsList = document.getElementById('modalJobBenefits');
            benefitsList.innerHTML = '';
            jobData.benefits.forEach(benefit => {
                const li = document.createElement('li');
                li.textContent = benefit;
                benefitsList.appendChild(li);
            });
            
            // Set apply button
            const applyBtn = document.getElementById('applyFromDetails');
            applyBtn.setAttribute('onclick', `openApplicationModal(${jobId})`);
            
            // Show modal
            const modal = document.getElementById('jobDetailsModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Add animation
            setTimeout(() => {
                modal.style.opacity = '1';
            }, 10);
        }
        
        function closeJobDetailsModal() {
            const modal = document.getElementById('jobDetailsModal');
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }, 300);
        }
        
        function applyFromJobDetails() {
            const modal = document.getElementById('jobDetailsModal');
            const jobTitle = document.getElementById('modalJobTitle').textContent;
            
            // Close details modal
            closeJobDetailsModal();
            
            // Open application modal after a short delay
            setTimeout(() => {
                // Find job ID from title
                let jobId = 0;
                if (jobDetailsData && typeof jobDetailsData === 'object') {
                    for (const id in jobDetailsData) {
                        if (jobDetailsData[id] && jobDetailsData[id].title === jobTitle) {
                            jobId = id;
                            break;
                        }
                    }
                }
                
                if (jobId > 0) {
                    openApplicationModal(jobId);
                }
            }, 300);
        }

        // Job Application Modal
        let selectedFiles = [];
        
        function openApplicationModal(jobId) {
            const modal = document.getElementById('applicationModal');
            const jobIdInput = document.getElementById('jobId');
            
            // Set the job ID
            jobIdInput.value = jobId;
            
            // Get job title for modal header
            // Convert jobId to string since JSON keys are strings
            const jobIdStr = String(jobId);
            if (jobDetailsData && typeof jobDetailsData === 'object' && jobDetailsData[jobIdStr]) {
                const jobData = jobDetailsData[jobIdStr];
                // Update modal title
                const modalTitle = modal.querySelector('h3');
                if (modalTitle && jobData.title) {
                    modalTitle.textContent = `Apply for: ${jobData.title}`;
                }
            }
            
            // Reset form
            document.getElementById('applicationForm').reset();
            selectedFiles = [];
            updateSelectedFiles();
            
            // Show modal with animation
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Add animation class
            setTimeout(() => {
                modal.style.opacity = '1';
            }, 10);
        }
        
        function closeApplicationModal() {
            const modal = document.getElementById('applicationModal');
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }, 300);
        }
        
        function handleFileUpload(input) {
            const files = Array.from(input.files);
            
            // Validate file types and sizes
            const validFiles = files.filter(file => {
                const validTypes = ['application/pdf', 'application/msword', 
                                   'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!validTypes.includes(file.type)) {
                    showMessage('Invalid file type. Please upload PDF or Word documents only.', 'error');
                    return false;
                }
                
                if (file.size > maxSize) {
                    showMessage(`File ${file.name} is too large. Maximum size is 5MB.`, 'error');
                    return false;
                }
                
                return true;
            });
            
            // Add valid files to selected files
            selectedFiles.push(...validFiles);
            updateSelectedFiles();
            
            // Clear file input
            input.value = '';
        }
        
        function updateSelectedFiles() {
            const container = document.getElementById('selectedFiles');
            if (!container) return;
            
            container.innerHTML = '';
            
            if (selectedFiles.length === 0) {
                return;
            }
            
            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.style.animation = 'fadeIn 0.3s ease-out forwards';
                fileItem.style.opacity = '0';
                
                const fileInfo = document.createElement('span');
                fileInfo.textContent = `${file.name} (${formatFileSize(file.size)})`;
                
                const removeBtn = document.createElement('button');
                removeBtn.className = 'remove-file';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.title = 'Remove file';
                removeBtn.onclick = () => {
                    // Add fade out animation
                    fileItem.style.opacity = '0';
                    fileItem.style.transform = 'translateX(-20px)';
                    setTimeout(() => {
                        selectedFiles.splice(index, 1);
                        updateSelectedFiles();
                    }, 300);
                };
                
                fileItem.appendChild(fileInfo);
                fileItem.appendChild(removeBtn);
                container.appendChild(fileItem);
                
                // Trigger animation
                setTimeout(() => {
                    fileItem.style.opacity = '1';
                    fileItem.style.transform = 'translateX(0)';
                }, 10);
            });
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Form Submission
        const applicationForm = document.getElementById('applicationForm');
        if (applicationForm) {
            applicationForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = document.getElementById('submitApplicationBtn');
                const submitText = document.getElementById('submitText');
                const spinner = document.getElementById('submitSpinner');
                
                // Disable button and show spinner
                submitBtn.disabled = true;
                if (submitText) submitText.style.display = 'none';
                if (spinner) spinner.style.display = 'block';
                
                // Validate required files
                if (selectedFiles.length === 0) {
                    showMessage('Please upload your CV and/or cover letter.', 'error');
                    submitBtn.disabled = false;
                    if (submitText) submitText.style.display = 'inline';
                    if (spinner) spinner.style.display = 'none';
                    return;
                }
                
                try {
                    // Create FormData object and map fields to API format
                    const formData = new FormData();
                    
                    // Map form fields to API fields
                    const jobId = document.getElementById('jobId').value;
                    const firstName = document.getElementById('firstName').value.trim();
                    const lastName = document.getElementById('lastName').value.trim();
                    
                    formData.append('job_id', jobId);
                    formData.append('applicant_name', firstName + ' ' + lastName);
                    formData.append('applicant_email', document.getElementById('email').value.trim());
                    formData.append('applicant_phone', document.getElementById('phone').value.trim());
                    formData.append('cover_letter', document.getElementById('coverLetter').value.trim());
                    formData.append('years_experience', document.getElementById('experience').value);
                    
                    const currentRole = document.getElementById('currentRole').value.trim();
                    if (currentRole) {
                        formData.append('current_position', currentRole);
                    }
                    
                    // Add resume file (first file only, as API expects single resume)
                    if (selectedFiles.length > 0 && selectedFiles[0]) {
                        formData.append('resume', selectedFiles[0]);
                    }
                    
                    // Send to careers API
                    const response = await fetch('./api/careers-api.php?action=create_application', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (!result.success) {
                        throw new Error(result.message || 'Failed to submit application');
                    }
                    
                    showMessage('Application submitted successfully! We\'ll contact you soon.', 'success');
                    
                    // Reset form and close modal after success
                    setTimeout(() => {
                        closeApplicationModal();
                        this.reset();
                        selectedFiles = [];
                        updateSelectedFiles();
                    }, 2000);
                    
                } catch (error) {
                    showMessage('Error submitting application. Please try again.', 'error');
                } finally {
                    // Re-enable button
                    submitBtn.disabled = false;
                    if (submitText) submitText.style.display = 'inline';
                    if (spinner) spinner.style.display = 'none';
                }
            });
        }
        
        
        // Show message function
        function showMessage(text, type) {
            // Remove existing messages
            const existingMessages = document.querySelectorAll('.message');
            existingMessages.forEach(msg => msg.remove());
            
            // Create new message
            const message = document.createElement('div');
            message.className = `message ${type}`;
            message.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${text}</span>
            `;
            
            document.body.appendChild(message);
            
            // Remove message after 5 seconds
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 300);
            }, 5000);
        }
        
        // Close modal when clicking outside
        const jobDetailsModal = document.getElementById('jobDetailsModal');
        if (jobDetailsModal) {
            jobDetailsModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeJobDetailsModal();
                }
            });
        }
        
        const applicationModal = document.getElementById('applicationModal');
        if (applicationModal) {
            applicationModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeApplicationModal();
                }
            });
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (jobDetailsModal.style.display === 'flex') {
                    closeJobDetailsModal();
                }
                if (applicationModal.style.display === 'flex') {
                    closeApplicationModal();
                }
            }
        });
    </script>
</body>
</html>