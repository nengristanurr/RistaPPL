<?php
/**
 * PawMate - Reusable HTML Header
 */
$pageTitle = isset($pageTitle) ? $pageTitle . " - PawMate" : "PawMate - Platform Adopsi Hewan Modern & Terpercaya";
$pageDesc = isset($pageDesc) ? $pageDesc : "Platform adopsi anjing dan kucing modern, aman, dan penuh kasih sayang.";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <?php if (isset($includeAdminCss) && $includeAdminCss): ?>
  <link rel="stylesheet" href="assets/css/admin.css">
  <?php endif; ?>
  <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body>
