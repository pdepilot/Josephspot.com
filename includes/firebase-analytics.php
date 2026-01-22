<?php
/**
 * Firebase Analytics Tracking Script Include
 * Add this to all public pages to enable Firebase Analytics tracking
 */

// Load Firebase config
$firebase_config = require __DIR__ . '/../config/firebase_config.php';

// Only output tracking script if Firebase is configured
$isConfigured = !empty($firebase_config['apiKey']) && 
                !empty($firebase_config['projectId']) && 
                !empty($firebase_config['appId']);

if ($isConfigured) {
    ?>
    <!-- Firebase Analytics -->
    <script type="module">
        // Import Firebase modules
        import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
        import { getAnalytics } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-analytics.js';

        // Firebase configuration
        const firebaseConfig = {
            apiKey: '<?php echo htmlspecialchars($firebase_config['apiKey']); ?>',
            authDomain: '<?php echo htmlspecialchars($firebase_config['authDomain']); ?>',
            projectId: '<?php echo htmlspecialchars($firebase_config['projectId']); ?>',
            storageBucket: '<?php echo htmlspecialchars($firebase_config['storageBucket']); ?>',
            messagingSenderId: '<?php echo htmlspecialchars($firebase_config['messagingSenderId']); ?>',
            appId: '<?php echo htmlspecialchars($firebase_config['appId']); ?>',
            measurementId: '<?php echo htmlspecialchars($firebase_config['measurementId']); ?>'
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const analytics = getAnalytics(app);

        // Make analytics available globally for custom events
        window.firebaseAnalytics = analytics;
    </script>
    <?php
}
