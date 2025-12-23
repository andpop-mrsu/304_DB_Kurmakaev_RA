<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../src/db.php';

$mechanic_id = (int)($_GET['mechanic_id'] ?? 0);
if (!$mechanic_id) { die('mechanic_id обязателен'); }

$stmt = $pdo->prepare("SELECT full_name FROM mechanic WHERE mechanic_id = ?");
$stmt->execute([$mechanic_id]);
$mechanic_name = $stmt->fetchColumn();
if (!$mechanic_name) { die('Мастер не найден'); }

$message = '';
if ($_POST) {
    $day = (int)($_POST['day_of_week'] ?? 0);
    $start = $_POST['start_time'] ?? '';
    $end = $_POST['end_time'] ?? '';
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (in_array($day, range(1, 7)) && $start && $end && $end > $start) {
        $stmt = $pdo->prepare("
            INSERT INTO mechanic_schedule (mechanic_id, day_of_week, start_time, end_time, is_active)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$mechanic_id, $day, $start, $end, $is_active]);
        header("Location: index.php?mechanic_id=$mechanic_id");
        exit;
    } else {
        $message = 'Проверьте корректность данных: день 1–7, время в формате ЧЧ:ММ, конец > начала.';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Новая смена — <?= htmlspecialchars($mechanic_name) ?></title></head>
<body>
    <h1>Добавить смену для: <?= htmlspecialchars($mechanic_name) ?></h1>
    <?php if ($message): ?>
        <p style="color: red"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <p>
            <label>
                День недели:
                <select name="day_of_week" required>
                    <option value="">—</option>
                    <?php $days = ['1' => 'Понедельник', '2' => 'Вторник', '3' => 'Среда', '4' => 'Четверг', '5' => 'Пятница', '6' => 'Суббота', '7' => 'Воскресенье']; ?>
                    <?php foreach ($days as $k => $v): ?>
                        <option value="<?= $k ?>"><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </p>
        <p>
            <label>Начало: <input type="time" name="start_time" required></label>
        </p>
        <p>
            <label>Конец: <input type="time" name="end_time" required></label>
        </p>
        <p>
            <label><input type="checkbox" name="is_active" checked> Активна</label>
        </p>
        <p>
            <button type="submit">Сохранить</button>
            <a href="index.php?mechanic_id=<?= $mechanic_id ?>">Отмена</a>
        </p>
    </form>
</body>
</html>