<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../src/db.php';

$stmt = $pdo->query("SELECT * FROM mechanic ORDER BY full_name");
$mechanics = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мастера СТО</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        th { background-color: #f4f4f4; }
        .actions a { margin: 0 5px; text-decoration: none; }
        .btn { padding: 4px 8px; background: #007bff; color: white; border-radius: 3px; }
        .btn-delete { background: #dc3545; }
    </style>
</head>
<body>
    <h1>Мастера СТО</h1>
    <table>
        <thead>
            <tr>
                <th>ФИО</th>
                <th>Специализация</th>
                <th>Активен</th>
                <th>Дата приёма</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mechanics as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['full_name']) ?></td>
                    <td><?= htmlspecialchars($m['specialization']) ?></td>
                    <td><?= $m['is_active'] ? '✅' : '❌' ?></td>
                    <td><?= $m['hire_date'] ?></td>
                    <td class="actions">
                        <a href="mechanics/edit.php?id=<?= $m['mechanic_id'] ?>" class="btn">Редактировать</a>
                        <a href="mechanics/delete.php?id=<?= $m['mechanic_id'] ?>" class="btn btn-delete" onclick="return confirm('Удалить мастера?')">Удалить</a>
                        <a href="schedule/index.php?mechanic_id=<?= $m['mechanic_id'] ?>" class="btn">График</a>
                        <a href="work_logs/index.php?mechanic_id=<?= $m['mechanic_id'] ?>" class="btn">Выполненные работы</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="mechanics/create.php" class="btn">➕ Добавить мастера</a></p>
</body>
</html>