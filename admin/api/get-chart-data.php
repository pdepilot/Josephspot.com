<?php
/**
 * API Endpoint: Get Chart Data
 * Returns data for Orders Overview and Revenue Distribution charts
 */

session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../db_config.php';

try {
    $period = isset($_GET['period']) ? $_GET['period'] : 'week';
    
    // Calculate date range based on period
    $now = new DateTime();
    $startDate = null;
    
    switch ($period) {
        case 'week':
            $startDate = clone $now;
            $startDate->modify('-7 days');
            break;
        case 'month':
            $startDate = clone $now;
            $startDate->modify('-1 month');
            break;
        case 'year':
            $startDate = clone $now;
            $startDate->modify('-1 year');
            break;
        default:
            $startDate = clone $now;
            $startDate->modify('-7 days');
    }
    
    $startDateStr = $startDate->format('Y-m-d 00:00:00');
    
    // Get Orders Overview Data (orders over time)
    $ordersData = [];
    
    if ($period === 'week') {
        // Group by day for week view
        for ($i = 6; $i >= 0; $i--) {
            $date = clone $now;
            $date->modify("-{$i} days");
            $dateStr = $date->format('Y-m-d');
            $dateStart = $date->format('Y-m-d 00:00:00');
            $dateEnd = $date->format('Y-m-d 23:59:59');
            
            $sql = "SELECT COUNT(*) as count 
                    FROM orders 
                    WHERE created_at >= :date_start AND created_at <= :date_end";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':date_start' => $dateStart, ':date_end' => $dateEnd]);
            $result = $stmt->fetch();
            
            $ordersData[] = [
                'label' => $date->format('D'),
                'date' => $dateStr,
                'count' => (int)$result['count']
            ];
        }
    } elseif ($period === 'month') {
        // Group by week for month view
        $weeks = [];
        $currentWeekStart = clone $startDate;
        $currentWeekStart->modify('monday this week');
        
        while ($currentWeekStart <= $now) {
            $weekEnd = clone $currentWeekStart;
            $weekEnd->modify('+6 days');
            if ($weekEnd > $now) $weekEnd = clone $now;
            
            $weekStartStr = $currentWeekStart->format('Y-m-d 00:00:00');
            $weekEndStr = $weekEnd->format('Y-m-d 23:59:59');
            
            $sql = "SELECT COUNT(*) as count 
                    FROM orders 
                    WHERE created_at >= :week_start AND created_at <= :week_end";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':week_start' => $weekStartStr, ':week_end' => $weekEndStr]);
            $result = $stmt->fetch();
            
            $weeks[] = [
                'label' => $currentWeekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                'count' => (int)$result['count']
            ];
            
            $currentWeekStart->modify('+7 days');
        }
        $ordersData = $weeks;
    } elseif ($period === 'year') {
        // Group by month for year view
        $months = [];
        $currentMonth = clone $startDate;
        $currentMonth->modify('first day of this month');
        
        while ($currentMonth <= $now) {
            $monthEnd = clone $currentMonth;
            $monthEnd->modify('last day of this month');
            if ($monthEnd > $now) $monthEnd = clone $now;
            
            $monthStartStr = $currentMonth->format('Y-m-d 00:00:00');
            $monthEndStr = $monthEnd->format('Y-m-d 23:59:59');
            
            $sql = "SELECT COUNT(*) as count 
                    FROM orders 
                    WHERE created_at >= :month_start AND created_at <= :month_end";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':month_start' => $monthStartStr, ':month_end' => $monthEndStr]);
            $result = $stmt->fetch();
            
            $months[] = [
                'label' => $currentMonth->format('M Y'),
                'count' => (int)$result['count']
            ];
            
            $currentMonth->modify('+1 month');
        }
        $ordersData = $months;
    }
    
    // Get Revenue Distribution Data (by payment method)
    $revenueSql = "SELECT 
                        payment_method,
                        SUM(total_amount) as total_revenue,
                        COUNT(*) as order_count
                    FROM orders
                    WHERE created_at >= :start_date
                    GROUP BY payment_method";
    $revenueStmt = $pdo->prepare($revenueSql);
    $revenueStmt->execute([':start_date' => $startDateStr]);
    $revenueResults = $revenueStmt->fetchAll();
    
    // Format revenue data
    $revenueData = [
        'cod' => ['label' => 'Cash on Delivery', 'revenue' => 0, 'count' => 0],
        'bank' => ['label' => 'Bank Transfer', 'revenue' => 0, 'count' => 0],
        'paystack' => ['label' => 'Paystack', 'revenue' => 0, 'count' => 0],
        'flutterwave' => ['label' => 'Flutterwave', 'revenue' => 0, 'count' => 0]
    ];
    
    foreach ($revenueResults as $row) {
        $method = $row['payment_method'];
        if (isset($revenueData[$method])) {
            $revenueData[$method]['revenue'] = (float)$row['total_revenue'];
            $revenueData[$method]['count'] = (int)$row['order_count'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'orders_overview' => [
            'labels' => array_column($ordersData, 'label'),
            'data' => array_column($ordersData, 'count')
        ],
        'revenue_distribution' => [
            'labels' => array_column($revenueData, 'label'),
            'data' => array_column($revenueData, 'revenue'),
            'counts' => array_column($revenueData, 'count')
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>

