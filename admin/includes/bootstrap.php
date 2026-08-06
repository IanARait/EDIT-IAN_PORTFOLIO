<?php
/**
 * Admin PHP initialization (no HTML output).
 * Include this BEFORE any POST handling so redirects work.
 */
require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
require_once dirname(dirname(__DIR__)) . '/classes/Database.php';
require_once dirname(dirname(__DIR__)) . '/classes/Auth.php';
require_once dirname(dirname(__DIR__)) . '/classes/Project.php';
require_once dirname(dirname(__DIR__)) . '/classes/Message.php';
require_once dirname(dirname(__DIR__)) . '/classes/Category.php';
require_once dirname(dirname(__DIR__)) . '/classes/Skill.php';
require_once dirname(dirname(__DIR__)) . '/classes/SkillCategory.php';
require_once dirname(dirname(__DIR__)) . '/classes/Service.php';
require_once dirname(dirname(__DIR__)) . '/classes/Testimonial.php';
require_once dirname(dirname(__DIR__)) . '/classes/Setting.php';

$basename = basename($_SERVER['PHP_SELF']);
if ($basename !== 'login.php' && $basename !== 'ajax.php') {
    Auth::check();
}
