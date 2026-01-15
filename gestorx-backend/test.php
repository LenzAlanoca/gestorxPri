<?php
// test.php - Prueba básica
echo '<h1>¡Backend funcionando!</h1>';
echo '<p>Ubicación: ' . __DIR__ . '</p>';
echo '<p>Fecha: ' . date('Y-m-d H:i:s') . '</p>';
echo '<p>PHP Version: ' . phpversion() . '</p>';

// Prueba PDO
try {
    $pdo_test = new PDO('mysql:host=localhost', 'root', '');
    echo '<p style="color: green;">✅ PDO funciona</p>';
} catch (Exception $e) {
    echo '<p style="color: red;">❌ PDO error: ' . $e->getMessage() . '</p>';
}
