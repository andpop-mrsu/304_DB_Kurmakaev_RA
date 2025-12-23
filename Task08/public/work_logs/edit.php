<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../src/db.php';

$id = (int)($_GET['id'] ?? 0);
$mechanic_id = (int)($_GET['mechanic_id'] ?? 0);
if (!$id || !$mechanic_id) { die('ID и mechanic_id обязательны'); }

$stmt = $pdo->prepare("SELECT * FROM work_log WHERE work_id = ?");
$stmt->execute([$id]);
$work = $stmt->fetch();
if (!$work) { die('Работа не найдена'); }

// Список записей для выбора
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
            UPDATE work_log
            SET appointment_id = ?, actual_start = ?, actual_end = ?, final_price = ?, notes = ?
            WHERE work_id = ?
        ");
        $stmt->execute([$appointment_id, $actual_start, $actual_end, $final_price, $notes, $id]);
        header("Location: index.php?mechanic_id=$mechanic_id");
        exit;
    } else {
        $message = 'Проверьте данные';
    }
}

$stmt = $pdo->prepare("SELECT full_name FROM mechanic WHERE mechanic_id = ?");
$stmt->execute([$mechanic_id]); 
$mechanic_name = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Редактировать работу</title></head>
<body>
    <h1>Редактировать работу — <?= htmlspecialchars($mechanic_name) ?></h1>
    <?php if ($message): ?>
        <p style="color: red"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <p>
            <label>
                Запись:
                <select name="appointment_id">
                    <option value="">— без записи —</option>
                    <?php foreach ($appointments as $a): ?>
                        <option value="<?= $a['appointment_id'] ?>" <?= ($work['appointment_id'] == $a['appointment_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['client']) ?> / <?= htmlspecialchars($a['service']) ?> — <?= substr($a['scheduled_start'], 0, 16) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
        <p>
            <label>Начало: <input type="datetime-local" name="actual_start" value="<?= str_replace(' ', 'T', substr($work['actual_start'], 0, 16)) ?>" required></label>
        </p>
        <p>
            <label>Конец: <input type="datetime-local" name="actual_end" value="<?= str_replace(' ', 'T', substr($work['actual_end'], 0, 16)) ?>" required></label>
        </p>
        <p>
            <label>Цена: <input type="number" step="0.01" min="0" name="final_price" value="<?= $work['final_price'] ?>" required></label>
        </p>
        <p>
            <label>Примечания: <textarea name="notes" rows="3" style="width:100%"><?= htmlspecialchars($work['notes'] ?? '') ?></textarea></label>
        </p>
        <p>
            <button type="submit">Сохранить</button>
            <a href="index.php?mechanic_id=<?= $mechanic_id ?>">Отмена</a>
        </p>
    </form>
</body>
</html>