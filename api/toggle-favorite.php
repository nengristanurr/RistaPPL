<?php
/**
 * PawMate - API Toggle Favorite (MySQL)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/auth.php';

if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'redirect' => 'login.php',
        'message' => 'Silakan masuk akun terlebih dahulu untuk menyimpan hewan favorit.'
    ]);
    exit;
}

$user = getCurrentUser();
$petId = $_POST['pet_id'] ?? '';

if (empty($petId)) {
    echo json_encode(['success' => false, 'message' => 'ID Hewan tidak valid.']);
    exit;
}

$db = getDB();

// Cek apakah hewan ada
$stmtPet = $db->prepare("SELECT name FROM pets WHERE id = ?");
$stmtPet->execute([$petId]);
$pet = $stmtPet->fetch();
$petName = $pet ? $pet['name'] : 'Hewan';

// Cek status favorit
$stmtCheck = $db->prepare("SELECT id FROM favorites WHERE user_id = ? AND pet_id = ?");
$stmtCheck->execute([$user['id'], $petId]);
$existing = $stmtCheck->fetch();

if ($existing) {
    // Hapus dari favorit
    $stmtDel = $db->prepare("DELETE FROM favorites WHERE user_id = ? AND pet_id = ?");
    $stmtDel->execute([$user['id'], $petId]);
    echo json_encode([
        'success' => true,
        'is_favorite' => false,
        'pet_name' => $petName
    ]);
} else {
    // Tambahkan ke favorit
    $stmtAdd = $db->prepare("INSERT INTO favorites (user_id, pet_id) VALUES (?, ?)");
    $stmtAdd->execute([$user['id'], $petId]);
    echo json_encode([
        'success' => true,
        'is_favorite' => true,
        'pet_name' => $petName
    ]);
}
