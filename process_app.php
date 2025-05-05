<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: login.php");
    exit;
}

// Function to generate app HTML for main index.html
function generateAppHTML($appName, $imagePath, $bonusAmount, $minWithdraw, $landingPageUrl) {
    return '<li class="app-item_dont_copy_created_by_allappstore_com">
        <div class="app-icon_dont_copy_created_by_allappstore_com-container">
            <img src="' . $imagePath . '" alt="' . $appName . ' Logo" class="app-icon_dont_copy_created_by_allappstore_com" />
        </div>
        <div class="app-details_dont_copy_created_by_allappstore_com">
            <h6 class="app-title_dont_copy_created_by_allappstore_com">' . $appName . '</h6>
            <span class="bonus-amount_dont_copy_created_by_allappstore_com"><i class="fa-solid fa-gift"></i> Sign Up Bonus ₹' . $bonusAmount . '<br></span>
            <span class="withdrawal-amount_dont_copy_created_by_allappstore_com"><i class="fa-solid fa-building-columns"></i> Min. Withdraw ₹' . $minWithdraw . '</span>
        </div>
        <a href="' . $landingPageUrl . '">
            <button class="download-button_dont_copy_created_by_allappstore_com"><i class="fa-solid fa-download"></i> Download</button>
        </a>
    </li>';
}

// Function to generate related apps HTML for landing pages
function generateRelatedAppsHTML($currentAppName) {
    $appsDataFile = 'apps_data.json';
    $relatedAppsHTML = '';
    
    if (file_exists($appsDataFile)) {
        $appsData = json_decode(file_get_contents($appsDataFile), true);
        
        // Shuffle the apps to get random related apps
        shuffle($appsData);
        
        // Get up to 5 related apps (excluding the current app)
        $count = 0;
        foreach ($appsData as $app) {
            if ($app['name'] !== $currentAppName && $count < 5) {
                $relatedAppsHTML .= '<li class="app-item_dont_copy_created_by_allappstore_com">
                    <div class="app-icon_dont_copy_created_by_allappstore_com-container">
                        <img src="' . $app['image'] . '" alt="' . $app['name'] . ' Logo" class="app-icon_dont_copy_created_by_allappstore_com" />
                    </div>
                    <div class="app-details_dont_copy_created_by_allappstore_com">
                        <h6 class="app-title_dont_copy_created_by_allappstore_com">' . $app['name'] . '</h6>
                        <span class="bonus-amount_dont_copy_created_by_allappstore_com"><i class="fa-solid fa-gift"></i> Sign Up Bonus ₹' . $app['bonus'] . '<br></span>
                        <span class="withdrawal-amount_dont_copy_created_by_allappstore_com"><i class="fa-solid fa-building-columns"></i> Min. Withdraw ₹' . $app['withdraw'] . '</span>
                    </div>
                    <a href="' . $app['landing_page'] . '">
                        <button class="download-button_dont_copy_created_by_allappstore_com"><i class="fa-solid fa-download"></i> Download</button>
                    </a>
                </li>';
                $count++;
            }
        }
    }
    
    return $relatedAppsHTML;
}

// Function to generate landing page HTML with embedded CSS
function generateLandingPageHTML($appName, $imagePath, $bonusAmount, $minWithdraw, $downloadLink) {
    // Get the template HTML
    $templateHTML = file_get_contents('landing-page-template.html');
    
    // Generate related apps HTML
    $relatedAppsHTML = generateRelatedAppsHTML($appName);
    
    // Replace placeholders with actual app data
    $templateHTML = str_replace('{{APP_NAME}}', $appName, $templateHTML);
    $templateHTML = str_replace('{{APP_IMAGE}}', $imagePath, $templateHTML);
    $templateHTML = str_replace('{{BONUS_AMOUNT}}', $bonusAmount, $templateHTML);
    $templateHTML = str_replace('{{MIN_WITHDRAW}}', $minWithdraw, $templateHTML);
    $templateHTML = str_replace('{{DOWNLOAD_LINK}}', $downloadLink, $templateHTML);
    $templateHTML = str_replace('{{RELATED_APPS}}', $relatedAppsHTML, $templateHTML);
    
    return $templateHTML;
}

// Handle file upload
function handleFileUpload($fileInputName) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $uploadDir = 'uploads/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = basename($_FILES[$fileInputName]['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Generate unique filename
    $uniqueName = uniqid() . '.' . $fileExt;
    $targetPath = $uploadDir . $uniqueName;
    
    // Check if file is an image
    $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($fileExt, $validExtensions)) {
        return false;
    }
    
    // Move uploaded file
    if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetPath)) {
        return $targetPath;
    }
    
    return false;
}

// Generate a random landing page filename with shorter numbers (3-5 digits)
function generateLandingPageFilename() {
    // Generate a random number between 100 and 99999 (3-5 digits)
    $randomNum = mt_rand(100, 99999);
    return "index-{$randomNum}.html";
}

// Add new app
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $appName = $_POST['app_name'];
    $bonusAmount = $_POST['bonus_amount'];
    $minWithdraw = $_POST['min_withdraw'];
    $downloadLink = $_POST['download_link'];
    $addToAllApps = isset($_POST['add_to_all_apps']);
    $addToNewApps = isset($_POST['add_to_new_apps']);
    
    // Handle logo upload
    $imagePath = handleFileUpload('app_logo');
    
    if ($imagePath) {
        // Generate landing page filename
        $landingPageFilename = generateLandingPageFilename();
        
        // Check if file already exists, if so, generate a new one
        while (file_exists($landingPageFilename)) {
            $landingPageFilename = generateLandingPageFilename();
        }
        
        // Generate landing page HTML
        $landingPageHTML = generateLandingPageHTML($appName, $imagePath, $bonusAmount, $minWithdraw, $downloadLink);
        
        // Save landing page HTML to file
        file_put_contents($landingPageFilename, $landingPageHTML);
        
        // Generate app HTML for main index.html
        $appHTML = generateAppHTML($appName, $imagePath, $bonusAmount, $minWithdraw, $landingPageFilename);
        
        // Read index.html
        $htmlContent = file_get_contents('index.html');
        
        // Add to All Apps section
        if ($addToAllApps) {
            $allAppsPattern = '/<div id="new" class="content active">\s*<ul class="app-list_dont_copy_created_by_allappstore_com">/s';
            $htmlContent = preg_replace($allAppsPattern, '<div id="new" class="content active">' . PHP_EOL . '    <ul class="app-list_dont_copy_created_by_allappstore_com">' . PHP_EOL . '    ' . $appHTML, $htmlContent);
        }
        
        // Add to New Apps section
        if ($addToNewApps) {
            $newAppsPattern = '/<div id="old" class="content">\s*<ul class="app-list_dont_copy_created_by_allappstore_com">/s';
            $htmlContent = preg_replace($newAppsPattern, '<div id="old" class="content">' . PHP_EOL . '    <ul class="app-list_dont_copy_created_by_allappstore_com">' . PHP_EOL . '    ' . $appHTML, $htmlContent);
        }
        
        // Write updated content back to file
        file_put_contents('index.html', $htmlContent);
        
        // Store app data in a database or file for future reference
        $appData = [
            'id' => time(),
            'name' => $appName,
            'image' => $imagePath,
            'bonus' => $bonusAmount,
            'withdraw' => $minWithdraw,
            'download_link' => $downloadLink,
            'landing_page' => $landingPageFilename
        ];
        
        // Store app data (using a simple JSON file for this example)
        $appsDataFile = 'apps_data.json';
        $appsData = file_exists($appsDataFile) ? json_decode(file_get_contents($appsDataFile), true) : [];
        $appsData[] = $appData;
        file_put_contents($appsDataFile, json_encode($appsData, JSON_PRETTY_PRINT));
        
        // Redirect back to admin panel
        header('Location: admin.php?tab=add&success=1&landing_page=' . urlencode($landingPageFilename));
        exit;
    } else {
        // Error handling
        header('Location: admin.php?tab=add&error=upload');
        exit;
    }
}

// Delete app
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $appId = (int)$_GET['id'];
    
    // Read index.html
    $htmlContent = file_get_contents('index.html');
    
    // Find all app items
    preg_match_all('/<li class="app-item_dont_copy_created_by_allappstore_com">(.*?)<\/li>/s', $htmlContent, $matches);
    
    if (!empty($matches[0]) && isset($matches[0][$appId - 1])) {
        $appHTML = $matches[0][$appId - 1];
        
        // Extract landing page URL
        preg_match('/<a href="([^"]*)"/', $appHTML, $urlMatch);
        $landingPageUrl = !empty($urlMatch) ? $urlMatch[1] : '';
        
        // Remove app from HTML
        $htmlContent = str_replace($appHTML, '', $htmlContent);
        
        // Write updated content back to file
        file_put_contents('index.html', $htmlContent);
        
        // Delete landing page file if it exists
        if (!empty($landingPageUrl) && file_exists($landingPageUrl)) {
            unlink($landingPageUrl);
        }
        
        // Update apps data
        $appsDataFile = 'apps_data.json';
        if (file_exists($appsDataFile)) {
            $appsData = json_decode(file_get_contents($appsDataFile), true);
            // Remove the app with the matching landing page
            $appsData = array_filter($appsData, function($app) use ($landingPageUrl) {
                return $app['landing_page'] !== $landingPageUrl;
            });
            file_put_contents($appsDataFile, json_encode(array_values($appsData), JSON_PRETTY_PRINT));
        }
    }
    
    // Redirect back to admin panel
    header('Location: admin.php?tab=manage&success=1');
    exit;
}

// Modify app
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modify') {
    $appId = (int)$_POST['app_id'];
    $appName = $_POST['app_name'];
    $bonusAmount = $_POST['bonus_amount'];
    $minWithdraw = $_POST['min_withdraw'];
    $downloadLink = $_POST['download_link'];
    
    // Read index.html
    $htmlContent = file_get_contents('index.html');
    
    // Find all app items
    preg_match_all('/<li class="app-item_dont_copy_created_by_allappstore_com">(.*?)<\/li>/s', $htmlContent, $matches);
    
    if (!empty($matches[0]) && isset($matches[0][$appId - 1])) {
        $oldAppHTML = $matches[0][$appId - 1];
        
        // Extract existing landing page URL
        preg_match('/<a href="([^"]*)"/', $oldAppHTML, $urlMatch);
        $oldLandingPageUrl = !empty($urlMatch) ? $urlMatch[1] : '';
        
        // Handle logo upload if provided
        if (isset($_FILES['app_logo']) && $_FILES['app_logo']['error'] === UPLOAD_ERR_OK) {
            $imagePath = handleFileUpload('app_logo');
        } else {
            // Extract existing image path
            preg_match('/<img src="([^"]*)"/', $oldAppHTML, $imgMatch);
            $imagePath = !empty($imgMatch) ? $imgMatch[1] : '';
        }
        
        // Generate new landing page filename or use existing one
        if (!empty($oldLandingPageUrl) && file_exists($oldLandingPageUrl)) {
            $landingPageFilename = $oldLandingPageUrl;
        } else {
            $landingPageFilename = generateLandingPageFilename();
            // Check if file already exists, if so, generate a new one
            while (file_exists($landingPageFilename)) {
                $landingPageFilename = generateLandingPageFilename();
            }
        }
        
        // Generate landing page HTML
        $landingPageHTML = generateLandingPageHTML($appName, $imagePath, $bonusAmount, $minWithdraw, $downloadLink);
        
        // Save landing page HTML to file
        file_put_contents($landingPageFilename, $landingPageHTML);
        
        // Generate new app HTML for main index.html
        $newAppHTML = generateAppHTML($appName, $imagePath, $bonusAmount, $minWithdraw, $landingPageFilename);
        
        // Replace old app HTML with new one
        $htmlContent = str_replace($oldAppHTML, $newAppHTML, $htmlContent);
        
        // Write updated content back to file
        file_put_contents('index.html', $htmlContent);
        
        // Update app data
        $appsDataFile = 'apps_data.json';
        if (file_exists($appsDataFile)) {
            $appsData = json_decode(file_get_contents($appsDataFile), true);
            
            // Find and update the app with the matching landing page
            foreach ($appsData as &$app) {
                if ($app['landing_page'] === $oldLandingPageUrl) {
                    $app['name'] = $appName;
                    $app['image'] = $imagePath;
                    $app['bonus'] = $bonusAmount;
                    $app['withdraw'] = $minWithdraw;
                    $app['download_link'] = $downloadLink;
                    $app['landing_page'] = $landingPageFilename;
                    break;
                }
            }
            
            file_put_contents($appsDataFile, json_encode($appsData, JSON_PRETTY_PRINT));
        }
        
        // Redirect back to admin panel
        header('Location: admin.php?tab=modify&success=1&landing_page=' . urlencode($landingPageFilename));
        exit;
    } else {
        // Error handling
        header('Location: admin.php?tab=modify&error=notfound');
        exit;
    }
}

// Default redirect
header('Location: admin.php');
exit;