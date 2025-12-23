<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../src/db.php';

$id = (int)($_GET['id'] ?? 0);
$mechanic_id = (int)($_GET['mechanic_id'] ?? 0);
if (!$id || !$mechanic_id) { die('ID и mechanic_id обязательны'); }

if ($_POST && $_POST['confirm'] === 'yes') {
    $pdo->prepare("DELETE FROM mechanic_schedule WHERE schedule_id = ?")->execute([$id]);
    header("Location: index.php?mechanic_id=$mechanic_id");
    exit;
}

$stmt = $pdo->prepare("
    SELECT ms.day_of_week, ms.start_time, m.full_name
    FROM mechanic_schedule ms
    JOIN mechanic m ON ms.mechanic_id = m.mechanic_id
    WHERE ms.schedule_id = ?
");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { die('Запись не найдена'); }

$days = ['1' => 'Пн', '2' => 'Вт', '3' => 'Ср', '4' => 'Чт', '5' => 'Пт', '6' => 'Сб', '7' => 'Вс'];
$day_str = $days[$row['day_of_week']] ?? $row['day_of_week'];
?>

<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Удалить смену</title></head>
<body>
    <h1>Удалить смену?</h1>
    <p>
        Мастер: <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
        День: <strong><?= $day_str ?></strong>, время: <strong><?= htmlspecialchars($row['start_time']) ?></strong>
    </p>
    <form method="post">
        <input type="hidden" name="confirm" value="yes">
        <button type="submit" style="background: #dc3545; color: white; padding: 6px 12px;">Да, удалить</button>
        <a href="index.php?mechanic_id=<?= $mechanic_id ?>">Отмена</a>
    </form>
</body>
</html>