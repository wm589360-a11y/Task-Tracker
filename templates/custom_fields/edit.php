<?php
$pageTitle  = 'Edit Custom Field - Advanced Task Tracker';
$isEdit     = true;
// $field and $optionsRaw are injected by the controller
$formAction = '/Task-Tracker/public/custom-fields/edit/' . $field['id'];
ob_start();
include __DIR__ . '/_form.php';
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>
