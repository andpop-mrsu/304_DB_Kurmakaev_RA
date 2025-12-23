<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../src/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { die('ID не указан'); }

if ($_POST && $_POST['confirm'] === 'yes') {
    $pdo->prepare("DELETE FROM work_log WHERE mechanic_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM appointment WHERE mechanic_id = ?")->execute([$id]);

    $pdo->prepare("DELETE FROM mechanic WHERE mechanic_id = ?")->execute([$id]);
    
    header('Location: ../index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT full_name FROM mechanic WHERE mechanic_id = ?");
$stmt->execute([$id]);
$name = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Удалить мастера</title></head>
<body>
    <h1>Подтверждение удаления</h1>
    <p>Вы уверены, что хотите удалить мастера <strong><?= htmlspecialchars($name) ?></strong>?</p>
    <p>Все связанные данные (график, работы и т.д.) также будут удалены.</p>

    <form method="post">
        <input type="hidden" name="confirm" value="yes">
        <button type="submit" style="background: #dc3545; color: white; padding: 6px 12px;">Да, удалить</button>
        <a href="../index.php">Отмена</a>
    </form>
</body>
</html>