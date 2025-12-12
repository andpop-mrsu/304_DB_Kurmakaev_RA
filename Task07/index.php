<?php
// index.php

$dbFile = __DIR__ . '/database.db';

if (!file_exists($dbFile)) {
    die('Ошибка: БД не найдена. Выполните инициализацию.');
}

try {
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Ошибка подключения к БД: ' . $e->getMessage());
}

// Получаем мастеров
$mechanics = $pdo->query("
    SELECT mechanic_id, full_name
    FROM mechanic
    WHERE is_active = 1 OR mechanic_id IN (
        SELECT DISTINCT mechanic_id FROM work_log
    )
    ORDER BY full_name
")->fetchAll(PDO::FETCH_ASSOC);

// Получаем параметр фильтра (через GET или POST — для простоты GET)
$mechanicId = isset($_GET['mechanic_id']) && is_numeric($_GET['mechanic_id'])
    ? (int)$_GET['mechanic_id']
    : null;

// Запрос на работы
$sql = "
    SELECT
        m.mechanic_id,
        m.full_name,
        wl.actual_start AS work_date,
        COALESCE(s.name, '(услуга не указана)') AS service_name,
        wl.final_price AS price
    FROM work_log wl
    JOIN mechanic m ON wl.mechanic_id = m.mechanic_id
    LEFT JOIN appointment a ON wl.appointment_id = a.appointment_id
    LEFT JOIN service s ON a.service_id = s.service_id
";

$params = [];
if ($mechanicId) {
    $sql .= " WHERE wl.mechanic_id = ?";
    $params = [$mechanicId];
}

$sql .= " ORDER BY m.full_name, wl.actual_start";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$works = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Выполненные работы СТО</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        select, button { padding: 6px 12px; font-size: 1rem; }
    </style>
</head>
<body>
    <h1>Выполненные работы СТО</h1>

    <form method="GET">
        <label for="mechanic_id">Мастер:</label>
        <select name="mechanic_id" id="mechanic_id">
            <option value="">— Все мастера —</option>
            <?php foreach ($mechanics as $m): ?>
                <option value="<?= htmlspecialchars($m['mechanic_id']) ?>" 
                    <?= $mechanicId == $m['mechanic_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['full_name']) ?> (ID <?= $m['mechanic_id'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Применить</button>
    </form>

    <?php if (!empty($works)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID мастера</th>
                    <th>ФИО</th>
                    <th>Дата работы</th>
                    <th>Услуга</th>
                    <th>Стоимость (₽)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($works as $w): ?>
                    <tr>
                        <td><?= htmlspecialchars($w['mechanic_id']) ?></td>
                        <td><?= htmlspecialchars($w['full_name']) ?></td>
                        <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($w['work_date']))) ?></td>
                        <td><?= htmlspecialchars($w['service_name']) ?></td>
                        <td><?= number_format($w['price'], 2, ',', ' ') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Нет данных по выполненным работам.</p>
    <?php endif; ?>

</body>
</html>