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
    SELECT wl.*, a.scheduled_start AS appointment_time, c.full_name AS client_name, s.name AS service_name
    FROM work_log wl
    LEFT JOIN appointment a ON wl.appointment_id = a.appointment_id
    LEFT JOIN car ca ON a.car_id = ca.car_id
    LEFT JOIN client c ON ca.client_id = c.client_id
    LEFT JOIN service s ON a.service_id = s.service_id
    WHERE wl.mechanic_id = ?
    ORDER BY wl.actual_start DESC
");
$stmt->execute([$mechanic_id]);
$works = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Выполненные работы: <?= htmlspecialchars($mechanic_name) ?></title>
    <style>
        table { border-collapse: collapse; width: 100%; margin: 1em 0; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; font-size: 0.9em; }
        th { background-color: #f4f4f4; }
        .btn { padding: 3px 7px; margin: 0 2px; text-decoration: none; background: #28a745; color: white; border-radius: 3px; font-size: 0.9em; }
        .btn-edit { background: #ffc107; color: #212529; }
        .btn-del { background: #dc3545; }
        .notes { font-style: italic; color: #666; }
    </style>
</head>
<body>
    <h1>Выполненные работы: <?= htmlspecialchars($mechanic_name) ?></h1>

    <table>
        <thead>
            <tr>
                <th>Начало</th>
                <th>Конец</th>
                <th>Клиент / Услуга</th>
                <th>Сумма</th>
                <th>Примечания</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($works)): ?>
                <tr><td colspan="6">Работ пока нет.</td></tr>
            <?php else: ?>
                <?php foreach ($works as $w): ?>
                    <tr>
                        <td><?= htmlspecialchars(substr($w['actual_start'], 0, 16)) ?></td>
                        <td><?= htmlspecialchars(substr($w['actual_end'], 0, 16)) ?></td>
                        <td>
                            <?php if ($w['client_name']): ?>
                                <?= htmlspecialchars($w['client_name']) ?> / <?= htmlspecialchars($w['service_name']) ?>
                            <?php else: ?>
                                <em>Без записи</em>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($w['final_price'], 0, ',', ' ') ?> ₽</td>
                        <td class="notes"><?= htmlspecialchars($w['notes'] ?? '') ?></td>
                        <td>
                            <a href="edit.php?id=<?= $w['work_id'] ?>&mechanic_id=<?= $mechanic_id ?>" class="btn btn-edit">✏️</a>
                            <a href="delete.php?id=<?= $w['work_id'] ?>&mechanic_id=<?= $mechanic_id ?>" 
                               class="btn btn-del" onclick="return confirm('Удалить работу?')">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p>
        <a href="create.php?mechanic_id=<?= $mechanic_id ?>" class="btn">➕ Добавить работу</a>
        <a href="../index.php">← К мастерам</a>
    </p>
</body>
</html>