<?php

/**
 * Babybib HTML Header
 * ====================
 */

// Security Headers (must be before any output)
require_once __DIR__ . '/security-headers.php';

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

// Load language file (may already be loaded by session.php bootstrap)
if (!isset($currentLang)) {
    $currentLang = getCurrentLanguage();
}
if (!isset($lang)) {
    $langFile = __DIR__ . '/../lang/' . $currentLang . '.php';
    $lang = file_exists($langFile) ? require $langFile : require __DIR__ . '/../lang/th.php';
}

// __() is defined in session.php — guard against re-definition
if (!function_exists('__')) {
    function __($key) { global $lang; return $lang[$key] ?? $key; }
}

// Page title
$pageTitle = isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME;
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo __('tagline'); ?>">
    <meta name="author" content="Babybib">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <?php
    $isAdminArea = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
    $fontFamily = ($currentLang === 'th' && $isAdminArea) ? "['Tahoma', 'Inter', 'sans-serif']" : "['Inter', 'sans-serif']";
    ?>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: <?php echo $fontFamily; ?>,
                    },
                    colors: {
                        vercel: {
                            black: '#000000',
                            white: '#ffffff',
                            gray: {
                                100: '#fafafa',
                                200: '#eaeaea',
                                300: '#999999',
                                400: '#888888',
                                500: '#666666',
                                600: '#444444',
                                700: '#333333',
                                800: '#111111',
                            },
                            blue: '#0070f3',
                            red: '#ee0000',
                            amber: '#f5a623',
                            emerald: '#50e3c2',
                        },
                        primary: '#000000', // Vercel primary is black
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/animations.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/components.css">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo SITE_URL; ?>/assets/images/favicon.svg">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">

    <!-- Scripts -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>

    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>

<body class="<?php echo $currentLang === 'en' ? 'lang-en' : ''; ?>">
    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>