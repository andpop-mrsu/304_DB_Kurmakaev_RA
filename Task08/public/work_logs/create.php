<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../src/db.php';

$mechanic_id = (int)($_GET['mechanic_id'] ?? 0);
if (!$mechanic_id) { die('mechanic_id обязателен'); }

$stmt = $pdo->prepare("SELECT full_name FROM mechanic WHERE mechanic_id = ?");
$stmt->execute([$mechanic_id]);
$mechanic_name = $stmt->fetchColumn();

// Получим список записей для выбора (опционально)
$stmt = $pdo->prepare("
    SELECT a.appointment_id, a.scheduled_start, c.full_name AS client, s.name AS service
    FROM appointment a
    JOIN car ca ON a.car_id = ca.car_id
    JOIN client c ON ca.client_id = c.client_id
    JOIN service s ON a.service_id = s.service_id
    WHERE a.mechanic_id = ? AND a.status = 'подтверждена'
    ORDER BY a.scheduled_start DESC
    LIMIT 20
");
$stmt->execute([$mechanic_id]);
$appointments = $stmt->fetchAll();

$message = '';
if ($_POST) {
    $appointment_id = $_POST['appointment_id'] === '' ? null : (int)$_POST['appointment_id'];
    $actual_start = $_POST['actual_start'];
    $actual_end = $_POST['actual_end'];
    $final_price = floatval($_POST['final_price']);
    $notes = trim($_POST['notes'] ?? '');

    if ($actual_start && $actual_end && $final_price >= 0 && $actual_end > $actual_start) {
        $stmt = $pdo->prepare("
            INSERT INTO work_log (appointment_id, mechanic_id, actual_start, actual_end, final_price, notes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$appointment_id, $mechanic_id, $actual_start, $actual_end, $final_price, $notes]);
        header("Location: index.php?mechanic_id=$mechanic_id");
        exit;
    } else {
        $message = 'Проверьте: дата/время, сумма ≥0, конец > начала.';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Новая работа — <?= htmlspecialchars($mechanic_name) ?></title></head>
<body>
    <h1>Добавить выполненную работу: <?= htmlspecialchars($mechanic_name) ?></h1>
    <?php if ($message): ?>
        <p style="color: red"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <p>
            <label>
                Связанная запись (опционально):
                <select name="appointment_id">
                    <option value="">— без записи —</option>
                    <?php foreach ($appointments as $a): ?>
                        <option value="<?= $a['appointment_id'] ?>">
                            <?= htmlspecialchars($a['client']) ?> / <?= htmlspecialchars($a['service']) ?> — <?= substr($a['scheduled_start'], 0, 16) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
        <p>
            <label>Фактическое начало: <input type="datetime-local" name="actual_start" required></label>
        </p>
        <p>
            <label>Фактическое окончание: <input type="datetime-local" name="actual_end" required></label>
        </p>
        <p>
            <label>Финальная цена (₽): <input type="number" step="0.01" min="0" name="final_price" value="0" required></label>
        </p>
        <p>
            <label>Примечания: <textarea name="notes" rows="2" style="width:100%"></textarea></label>
        </p>
        <p>
            <button type="submit">Сохранить</button>
            <a href="index.php?mechanic_id=<?= $mechanic_id ?>">Отмена</a>
        </p>
    </form>
</body>
</html>