<?php
// task7_cli.php

$dbFile = __DIR__ . '/database.db';

if (!file_exists($dbFile)) {
    die("Ошибка: файл БД '$dbFile' не найден. Выполните инициализацию.\n");
}

try {
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage() . "\n");
}

// 1. Получить всех мастеров
$stmt = $pdo->query("
    SELECT mechanic_id, full_name
    FROM mechanic
    WHERE is_active = 1 OR mechanic_id IN (
        SELECT DISTINCT mechanic_id FROM work_log
    )
    ORDER BY full_name
");
$mechanics = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($mechanics)) {
    die("В базе нет мастеров.\n");
}

echo "Доступные мастера:\n";
foreach ($mechanics as $m) {
    echo "  {$m['mechanic_id']}: {$m['full_name']}\n";
}
echo "\nВведите номер мастера или нажмите Enter для вывода всех: ";
$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));
fclose($handle);

$mechanicId = null;
if ($input !== '') {
    if (!ctype_digit($input)) {
        die("Ошибка: введите корректный номер мастера (целое число) или оставьте пустым.\n");
    }
    $mechanicId = (int)$input;

    // Проверить, существует ли такой мастер (хотя бы в work_log или mechanic)
    $exists = $pdo->prepare("
        SELECT 1 FROM mechanic WHERE mechanic_id = ?
        UNION
        SELECT 1 FROM work_log WHERE mechanic_id = ?
        LIMIT 1
    ");
    $exists->execute([$mechanicId, $mechanicId]);
    if (!$exists->fetch()) {
        die("Ошибка: мастер с ID $mechanicId не найден в базе.\n");
    }
}

// 2. Запрос на получение списка оказанных услуг
// Важно: для work_log.appointment_id IS NOT NULL — получаем service.name
// Для NULL — подставляем "услуга не указана"

$sql = "
    SELECT
        m.mechanic_id AS mechanic_id,
        m.full_name AS full_name,
        wl.actual_start AS work_date,
        COALESCE(s.name, '(услуга не указана)') AS service_name,
        wl.final_price AS price
    FROM work_log wl
    JOIN mechanic m ON wl.mechanic_id = m.mechanic_id
    LEFT JOIN appointment a ON wl.appointment_id = a.appointment_id
    LEFT JOIN service s ON a.service_id = s.service_id
";

$params = [];
if ($mechanicId !== null) {
    $sql .= " WHERE wl.mechanic_id = ?";
    $params[] = $mechanicId;
}

$sql .= " ORDER BY m.full_name, wl.actual_start";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "Нет выполненных работ.\n";
    exit;
}

// Подготовка к отрисовке таблицы псевдографикой
$headers = ['ID', 'Мастер', 'Дата работы', 'Услуга', 'Стоимость'];
$data = array_map(function($row) {
    return [
        $row['mechanic_id'],
        $row['full_name'],
        date('Y-m-d H:i', strtotime($row['work_date'])),
        $row['service_name'],
        sprintf('%.2f', $row['price'])
    ];
}, $rows);

// Рассчитаем ширину колонок
$colWidths = array_map('strlen', $headers);
foreach ($data as $row) {
    foreach ($row as $i => $cell) {
        $colWidths[$i] = max($colWidths[$i], iconv_strlen($cell, 'UTF-8'));
    }
}

// Функция для выравнивания текста с учётом многобайтных символов (кириллица)
function str_pad_mb($str, $len, $pad = ' ') {
    return $str . str_repeat($pad, max(0, $len - iconv_strlen($str, 'UTF-8')));
}

// Верхняя граница
$top = '+' . implode('+', array_map(fn($w) => str_repeat('-', $w + 2), $colWidths)) . '+';
echo $top . "\n";

// Заголовки
echo '| ';
foreach ($headers as $i => $h) {
    echo str_pad_mb($h, $colWidths[$i]) . ' | ';
}
echo "\n" . $top . "\n";

// Строки данных
foreach ($data as $row) {
    echo '| ';
    foreach ($row as $i => $cell) {
        echo str_pad_mb($cell, $colWidths[$i]) . ' | ';
    }
    echo "\n";
}
echo $top . "\n";