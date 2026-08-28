<?php
$pageTitle = 'Create Custom Field - Advanced Task Tracker';
$isEdit     = false;
$field      = [];
$optionsRaw = '';
$formAction = URL_ROOT . '/custom-fields/create';
ob_start();
include __DIR__ . '/_form.php';
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
