<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../src/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { die('ID не указан'); }

// Получаем данные
$stmt = $pdo->prepare("SELECT * FROM mechanic WHERE mechanic_id = ?");
$stmt->execute([$id]);
$mechanic = $stmt->fetch();

if (!$mechanic) { die('Мастер не найден'); }

$message = '';

if ($_POST) {
    $full_name = trim($_POST['full_name']);
    $specialization = trim($_POST['specialization']);
    $commission_rate = floatval($_POST['commission_rate']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $hire_date = $_POST['hire_date'];
    $dismissal_date = empty($_POST['dismissal_date']) ? null : $_POST['dismissal_date'];

    if ($full_name && $specialization) {
        $stmt = $pdo->prepare("
            UPDATE mechanic
            SET full_name = ?, specialization = ?, commission_rate = ?, is_active = ?, hire_date = ?, dismissal_date = ?
            WHERE mechanic_id = ?
        ");
        $stmt->execute([$full_name, $specialization, $commission_rate, $is_active, $hire_date, $dismissal_date, $id]);
        header('Location: ../index.php');
        exit;
    } else {
        $message = 'Заполните обязательные поля!';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Редактировать мастера</title></head>
<body>
    <h1>Редактировать: <?= htmlspecialchars($mechanic['full_name']) ?></h1>
    <?php if ($message): ?>
        <p style="color: red"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post">
        <p>
            <label>ФИО: <input type="text" name="full_name" value="<?= htmlspecialchars($mechanic['full_name']) ?>" required></label>
        </p>
        <p>
            <label>Специализация: <input type="text" name="specialization" value="<?= htmlspecialchars($mechanic['specialization']) ?>" required></label>
        </p>
        <p>
            <label>Комиссия (%): <input type="number" step="0.1" min="0" max="100" name="commission_rate" value="<?= $mechanic['commission_rate'] ?>"></label>
        </p>
        <p>
            <label><input type="checkbox" name="is_active" <?= $mechanic['is_active'] ? 'checked' : '' ?>> Активен</label>
        </p>
        <p>
            <label>Дата приёма: <input type="date" name="hire_date" value="<?= $mechanic['hire_date'] ?>"></label>
        </p>
        <p>
            <label>Дата увольнения: <input type="date" name="dismissal_date" value="<?= $mechanic['dismissal_date'] ?>"></label>
            <small>(оставьте пустым, если работает)</small>
        </p>
        <p>
            <button type="submit">Сохранить</button>
            <a href="../index.php">← Отмена</a>
        </p>
    </form>
</body>
</html>