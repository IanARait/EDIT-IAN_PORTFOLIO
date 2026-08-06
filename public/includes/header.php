<?php
require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
require_once dirname(dirname(__DIR__)) . '/classes/Database.php';
require_once dirname(dirname(__DIR__)) . '/classes/Project.php';
require_once dirname(dirname(__DIR__)) . '/classes/Category.php';
require_once dirname(dirname(__DIR__)) . '/classes/Service.php';
require_once dirname(dirname(__DIR__)) . '/classes/Testimonial.php';
require_once dirname(dirname(__DIR__)) . '/classes/Message.php';
require_once dirname(dirname(__DIR__)) . '/classes/Setting.php';
require_once dirname(dirname(__DIR__)) . '/classes/Skill.php';
require_once dirname(dirname(__DIR__)) . '/classes/SkillCategory.php';
require_once dirname(dirname(__DIR__)) . '/classes/Auth.php';

$settings = new Setting();
$siteName = $settings->get('site_name') ?: 'Video Editor Portfolio';
$siteTagline = $settings->get('site_tagline') ?: 'Professional Video Editor';

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $settings->get('site_description') ?: 'Professional Video Editor Portfolio - Creating High-Converting Video Content' ?>">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' . sanitize($siteName) : sanitize($siteName) . ' - ' . sanitize($siteTagline) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&family=Press+Start+2P&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/style.css" rel="stylesheet">
    <link href="<?= ASSETS_URL ?>/css/arcade.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎬</text></svg>">
</head>
<body>
    <div class="loading-screen" id="loadingScreen">
        <div class="loader"></div>
    </div>
    <script>
    setTimeout(function(){ var ls=document.getElementById('loadingScreen'); if(ls){ls.classList.add('hidden');setTimeout(function(){ls.style.display='none';},600);} }, 3000);
    </script>
