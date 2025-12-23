<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../src/db.php';

$mechanic_id = (int)($_GET['mechanic_id'] ?? 0);
if (!$mechanic_id) { die('mechanic_id обязателен'); }

$stmt = $pdo->prepare("SELECT full_name FROM mechanic WHERE mechanic_id = ?");
$stmt->execute([$mechanic_id]);
$mechanic_name = $stmt->fetchColumn();
if (!$mechanic_name) { die('Мастер не найден'); }

$stmt = $pdo->prepare("
    SELECT * FROM mechanic_schedule 
    WHERE mechanic_id = ? 
    ORDER BY day_of_week, start_time
");
$stmt->execute([$mechanic_id]);
$schedules = $stmt->fetchAll();

$days = [1 => 'Пн', 2 => 'Вт', 3 => 'Ср', 4 => 'Чт', 5 => 'Пт', 6 => 'Сб', 7 => 'Вс'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>График: <?= htmlspecialchars($mechanic_name) ?></title>
    <style>
        table { border-collapse: collapse; width: 100%; margin: 1em 0; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        .btn { padding: 4px 8px; margin: 0 3px; text-decoration: none; background: #007bff; color: white; border-radius: 3px; }
        .btn-del { background: #dc3545; }
        .inactive { color: #999; text-decoration: line-through; }
    </style>
</head>
<body>
    <h1>График работы: <?= htmlspecialchars($mechanic_name) ?></h1>

    <table>
        <thead>
            <tr>
                <th>День</th>
                <th>Начало</th>
                <th>Конец</th>
                <th>Активен</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($schedules)): ?>
                <tr><td colspan="5">График не задан.</td></tr>
            <?php else: ?>
                <?php foreach ($schedules as $s): ?>
                    <tr class="<?= $s['is_active'] ? '' : 'inactive' ?>">
                        <td><?= htmlspecialchars($days[$s['day_of_week']] ?? $s['day_of_week']) ?></td>
                        <td><?= htmlspecialchars($s['start_time']) ?></td>
                        <td><?= htmlspecialchars($s['end_time']) ?></td>
                        <td><?= $s['is_active'] ? '✅' : '❌' ?></td>
                        <td>
                            <a href="edit.php?id=<?= $s['schedule_id'] ?>&mechanic_id=<?= $mechanic_id ?>" class="btn">✏️</a>
                            <a href="delete.php?id=<?= $s['schedule_id'] ?>&mechanic_id=<?= $mechanic_id ?>" 
                               class="btn btn-del" onclick="return confirm('Удалить запись графика?')">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p>
        <a href="create.php?mechanic_id=<?= $mechanic_id ?>" class="btn">➕ Добавить смену</a>
        <a href="../index.php">← К списку мастеров</a>
    </p>
</body>
</html>