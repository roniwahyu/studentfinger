<?php
/**
 * Example: How to integrate FingerprintBridge widget into main dashboard
 * 
 * This file shows how to integrate the FingerprintBridge module
 * into your main application dashboard.
 */

// In your main dashboard controller (e.g., app/Controllers/DashboardController.php)
?>

<!-- Example Dashboard View Integration -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Finger Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>
        
        <div class="row">
            <!-- Existing Dashboard Widgets -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Students
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">1,234</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Today's Attendance
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">987</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FingerprintBridge Widget Integration -->
            <?php
            // Load the fingerprint helper
            helper('fingerprint');
            
            // Check if FingerprintBridge module is available
            if (is_fingerprint_module_available()) {
                // Display the FingerprintBridge dashboard widget
                echo get_fingerprint_dashboard_widget();
            } else {
                // Display installation widget if module is not installed
                echo get_fingerprint_install_widget();
            }
            ?>

            <!-- More dashboard widgets can go here -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                    </div>
                    <div class="card-body">
                        <!-- Recent activity content -->
                        <p>Recent student activities and system events...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Alerts Section -->
        <div class="row">
            <div class="col-12">
                <?php
                // Get FingerprintBridge alerts
                $fingerprintAlerts = get_fingerprint_alerts();
                
                if (!empty($fingerprintAlerts)) {
                    echo '<div class="card shadow mb-4">';
                    echo '<div class="card-header py-3">';
                    echo '<h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-exclamation-triangle"></i> System Alerts</h6>';
                    echo '</div>';
                    echo '<div class="card-body">';
                    
                    foreach ($fingerprintAlerts as $alert) {
                        $alertClass = [
                            'info' => 'alert-info',
                            'warning' => 'alert-warning', 
                            'danger' => 'alert-danger',
                            'success' => 'alert-success'
                        ][$alert['type']] ?? 'alert-info';
                        
                        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
                        echo '<strong>' . htmlspecialchars($alert['title']) . ':</strong> ';
                        echo htmlspecialchars($alert['message']);
                        
                        if (!empty($alert['action_url'])) {
                            echo ' <a href="' . $alert['action_url'] . '" class="btn btn-sm btn-outline-' . $alert['type'] . ' ml-2">';
                            echo htmlspecialchars($alert['action_text'] ?? 'Action');
                            echo '</a>';
                        }
                        
                        echo '<button type="button" class="close" data-dismiss="alert">';
                        echo '<span>&times;</span>';
                        echo '</button>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
/**
 * Example Controller Code
 * 
 * Add this to your main DashboardController.php
 */
?>

<?php
/*
// In app/Controllers/DashboardController.php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index()
    {
        // Load fingerprint helper
        helper('fingerprint');
        
        $data = [
            'title' => 'Dashboard',
            'total_students' => $this->getStudentCount(),
            'today_attendance' => $this->getTodayAttendanceCount(),
            // Add FingerprintBridge data
            'fingerprint_stats' => get_fingerprint_summary_stats(),
            'fingerprint_alerts' => get_fingerprint_alerts(),
            'fingerprint_available' => is_fingerprint_module_available()
        ];
        
        return view('dashboard', $data);
    }
    
    private function getStudentCount()
    {
        // Your existing logic to get student count
        return 1234;
    }
    
    private function getTodayAttendanceCount()
    {
        // Your existing logic to get today's attendance
        return 987;
    }
}
*/
?>

<?php
/**
 * Example Menu Integration
 * 
 * Add this to your main navigation/menu system
 */
?>

<?php
/*
// In your main navigation view or helper

// Load fingerprint helper
helper('fingerprint');

// Get FingerprintBridge menu items
$fingerprintMenuItems = get_fingerprint_menu_items();

// Merge with your existing menu items
$mainMenuItems = [
    [
        'title' => 'Dashboard',
        'url' => 'dashboard',
        'icon' => 'fas fa-tachometer-alt'
    ],
    [
        'title' => 'Students',
        'url' => 'students',
        'icon' => 'fas fa-users'
    ],
    [
        'title' => 'Attendance',
        'url' => 'attendance',
        'icon' => 'fas fa-check-circle'
    ]
];

// Add FingerprintBridge menu items if module is available
if (!empty($fingerprintMenuItems)) {
    $mainMenuItems = array_merge($mainMenuItems, $fingerprintMenuItems);
}

// Sort by order if specified
usort($mainMenuItems, function($a, $b) {
    return ($a['order'] ?? 999) - ($b['order'] ?? 999);
});

// Render menu items
foreach ($mainMenuItems as $item) {
    // Render menu item HTML
    echo '<li class="nav-item">';
    echo '<a class="nav-link" href="' . base_url($item['url']) . '">';
    echo '<i class="' . $item['icon'] . '"></i> ' . $item['title'];
    
    // Show badge if available
    if (!empty($item['badge'])) {
        echo ' <span class="badge ' . $item['badge']['class'] . '" title="' . $item['badge']['title'] . '">';
        echo $item['badge']['text'];
        echo '</span>';
    }
    
    echo '</a>';
    echo '</li>';
}
*/
?>
