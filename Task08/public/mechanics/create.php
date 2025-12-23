<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../src/db.php';

$message = '';

if ($_POST) {
    $full_name = trim($_POST['full_name'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $commission_rate = floatval($_POST['commission_rate'] ?? 15.0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $hire_date = $_POST['hire_date'] ?? date('Y-m-d');

    if ($full_name && $specialization) {
        $stmt = $pdo->prepare("
            INSERT INTO mechanic (full_name, specialization, commission_rate, is_active, hire_date)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$full_name, $specialization, $commission_rate, $is_active, $hire_date]);
        header('Location: ../index.php');
        exit;
    } else {
        $message = 'Заполните обязательные поля!';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Добавить мастера</title></head>
<body>
    <h1>Добавить мастера</h1>
    <?php if ($message): ?>
        <p style="color: red"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <p>
            <label>ФИО: <input type="text" name="full_name" required></label>
        </p>
        <p>
            <label>Специализация: <input type="text" name="specialization" required></label>
        </p>
        <p>
            <label>Комиссия (%): <input type="number" step="0.1" min="0" max="100" name="commission_rate" value="15.0"></label>
        </p>
        <p>
            <label><input type="checkbox" name="is_active" checked> Активен</label>
        </p>
        <p>
            <label>Дата приёма: <input type="date" name="hire_date" value="<?= date('Y-m-d') ?>"></label>
        </p>
        <p>
            <button type="submit">Сохранить</button>
            <a href="../index.php">← Назад</a>
        </p>
    </form>
</body>
</html>