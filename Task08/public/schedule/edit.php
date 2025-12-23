<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../src/db.php';

$id = (int)($_GET['id'] ?? 0);
$mechanic_id = (int)($_GET['mechanic_id'] ?? 0);
if (!$id || !$mechanic_id) { die('ID и mechanic_id обязательны'); }

$stmt = $pdo->prepare("SELECT * FROM mechanic_schedule WHERE schedule_id = ?");
$stmt->execute([$id]);
$schedule = $stmt->fetch();
if (!$schedule) { die('Запись не найдена'); }

$message = '';
if ($_POST) {
    $day = (int)($_POST['day_of_week']);
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (in_array($day, range(1, 7)) && $start && $end && $end > $start) {
        $stmt = $pdo->prepare("
            UPDATE mechanic_schedule
            SET day_of_week = ?, start_time = ?, end_time = ?, is_active = ?
            WHERE schedule_id = ?
        ");
        $stmt->execute([$day, $start, $end, $is_active, $id]);
        header("Location: index.php?mechanic_id=$mechanic_id");
        exit;
    } else {
        $message = 'Некорректные данные';
    }
}

$stmt = $pdo->prepare("SELECT full_name FROM mechanic WHERE mechanic_id = ?");
$stmt->execute([$mechanic_id]);
$mechanic_name = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Редактировать смену</title></head>
<body>
    <h1>Редактировать смену — <?= htmlspecialchars($mechanic_name) ?></h1>
    <?php if ($message): ?>
        <p style="color: red"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <p>
            <label>
                День:
                <select name="day_of_week" required>
                    <?php $days = ['1' => 'Пн', '2' => 'Вт', '3' => 'Ср', '4' => 'Чт', '5' => 'Пт', '6' => 'Сб', '7' => 'Вс']; ?>
                    <?php foreach ($days as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $schedule['day_of_week'] == $k ? 'selected' : '' ?>>
                            <?= $v ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
        <p>
            <label>Начало: <input type="time" name="start_time" value="<?= htmlspecialchars($schedule['start_time']) ?>" required></label>
        </p>
        <p>
            <label>Конец: <input type="time" name="end_time" value="<?= htmlspecialchars($schedule['end_time']) ?>" required></label>
        </p>
        <p>
            <label><input type="checkbox" name="is_active" <?= $schedule['is_active'] ? 'checked' : '' ?>> Активна</label>
        </p>
        <p>
            <button type="submit">Сохранить</button>
            <a href="index.php?mechanic_id=<?= $mechanic_id ?>">Отмена</a>
        </p>
    </form>
</body>
</html>