<?php
// src/db.php
$database_path = __DIR__ . '/../data/db.sqlite';
$pdo = new PDO("sqlite:$database_path");

// Гарантируем UTF-8 для SQLite
$pdo->exec("PRAGMA encoding = 'UTF-8';");
$pdo->exec("PRAGMA foreign_keys = ON;");

// Настройки PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

// Функция для безопасного вывода (встроена прямо здесь)
function e($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>