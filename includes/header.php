<?php

/**
 * Babybib HTML Header
 * ====================
 */

// Security Headers (must be before any output)
require_once __DIR__ . '/security-headers.php';

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/functions.php';

// Load language file
$currentLang = getCurrentLanguage();
$langFile = __DIR__ . '/../lang/' . $currentLang . '.php';
$lang = file_exists($langFile) ? require $langFile : require __DIR__ . '/../lang/th.php';

// Helper function for translations
function __($key)
{
    global $lang;
    return $lang[$key] ?? $key;
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
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>

<body class="<?php echo $currentLang === 'en' ? 'lang-en' : ''; ?>">
    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>