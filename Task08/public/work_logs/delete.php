<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../src/db.php';

$id = (int)($_GET['id'] ?? 0);
$mechanic_id = (int)($_GET['mechanic_id'] ?? 0);
if (!$id || !$mechanic_id) { die('ID и mechanic_id обязательны'); }

if ($_POST && $_POST['confirm'] === 'yes') {
    $pdo->prepare("DELETE FROM work_log WHERE work_id = ?")->execute([$id]);
    header("Location: index.php?mechanic_id=$mechanic_id");
    exit;
}

$stmt = $pdo->prepare("
    SELECT wl.*, m.full_name AS mechanic, 
           COALESCE(c.full_name || ' / ' || s.name, 'Без записи') AS job_desc
    FROM work_log wl
    JOIN mechanic m ON wl.mechanic_id = m.mechanic_id
    LEFT JOIN appointment a ON wl.appointment_id = a.appointment_id
    LEFT JOIN car ca ON a.car_id = ca.car_id
    LEFT JOIN client c ON ca.client_id = c.client_id
    LEFT JOIN service s ON a.service_id = s.service_id
    WHERE wl.work_id = ?
");
$stmt->execute([$id]);
$work = $stmt->fetch();
if (!$work) { die('Работа не найдена'); }
?>

<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Удалить работу</title></head>
<body>
    <h1>Удалить работу?</h1>
    <p>
        Мастер: <strong><?= htmlspecialchars($work['mechanic']) ?></strong><br>
        Работа: <strong><?= htmlspecialchars($work['job_desc']) ?></strong><br>
        Сумма: <strong><?= number_format($work['final_price'], 0, ',', ' ') ?> ₽</strong>
    </p>
    <form method="post">
        <input type="hidden" name="confirm" value="yes">
        <button type="submit" style="background: #dc3545; color: white; padding: 6px 12px;">Да, удалить</button>
        <a href="index.php?mechanic_id=<?= $mechanic_id ?>">Отмена</a>
    </form>
</body>
</html>