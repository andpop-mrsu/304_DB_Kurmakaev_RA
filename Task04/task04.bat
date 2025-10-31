#!/bin/bash
chcp 65001

sqlite3 movies_rating.db < db_init.sql

echo "1. Найти все пары пользователей, оценивших один и тот же фильм. Устранить дубликаты, проверить отсутствие пар с самим собой. Для каждой пары должны быть указаны имена пользователей и название фильма, который они оценили. В списке оставить первые 100 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
WITH Pairs AS (
    SELECT
        u1.name AS user1,
        u2.name AS user2,
        m.title AS movie_title
    FROM ratings r1
    JOIN ratings r2 ON r1.movie_id = r2.movie_id AND r1.user_id < r2.user_id
    JOIN users u1 ON r1.user_id = u1.id
    JOIN users u2 ON r2.user_id = u2.id
    JOIN movies m ON r1.movie_id = m.id
)
SELECT user1, user2, movie_title
FROM Pairs
ORDER BY user1, user2, movie_title
LIMIT 100;
"
echo " "

echo "2. Найти 10 самых старых оценок от разных пользователей, вывести названия фильмов, имена пользователей, оценку, дату отзыва в формате ГГГГ-ММ-ДД."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
SELECT
    m.title AS movie_title,
    u.name AS user_name,
    r.rating,
    strftime('%Y-%m-%d', r.timestamp, 'unixepoch') AS rating_date
FROM ratings r
JOIN users u ON r.user_id = u.id
JOIN movies m ON r.movie_id = m.id
ORDER BY r.timestamp ASC
LIMIT 10;
"
echo " "

echo "3. Вывести в одном списке все фильмы с максимальным средним рейтингом и все фильмы с минимальным средним рейтингом. Общий список отсортировать по году выпуска и названию фильма. В зависимости от рейтинга в колонке \"Рекомендуем\" для фильмов должно быть написано \"Да\" или \"Нет\"."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
WITH AvgRatings AS (
    SELECT
        m.id,
        m.title,
        m.year,
        AVG(r.rating) AS avg_rating
    FROM movies m
    JOIN ratings r ON m.id = r.movie_id
    GROUP BY m.id
),
MinMax AS (
    SELECT
        MAX(avg_rating) AS max_avg,
        MIN(avg_rating) AS min_avg
    FROM AvgRatings
)
SELECT
    ar.title,
    ar.year,
    ROUND(ar.avg_rating, 2) AS avg_rating,
    CASE
        WHEN ar.avg_rating = mm.max_avg THEN 'Да'
        WHEN ar.avg_rating = mm.min_avg THEN 'Нет'
    END AS Рекомендуем
FROM AvgRatings ar
CROSS JOIN MinMax mm
WHERE ar.avg_rating = mm.max_avg OR ar.avg_rating = mm.min_avg
ORDER BY ar.year, ar.title;
"
echo " "

echo "4. Вычислить количество оценок и среднюю оценку, которую дали фильмам пользователи-мужчины в период с 2011 по 2014 год."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
SELECT
    COUNT(*) AS total_ratings,
    ROUND(AVG(r.rating), 2) AS avg_rating
FROM ratings r
JOIN users u ON r.user_id = u.id
WHERE u.gender = 'M'
  AND strftime('%Y', r.timestamp, 'unixepoch') BETWEEN '2011' AND '2014';
"
echo " "

echo "5. Составить список фильмов с указанием средней оценки и количества пользователей, которые их оценили. Полученный список отсортировать по году выпуска и названиям фильмов. В списке оставить первые 20 записей."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
SELECT
    m.title,
    m.year,
    ROUND(AVG(r.rating), 2) AS avg_rating,
    COUNT(r.user_id) AS users_count
FROM movies m
JOIN ratings r ON m.id = r.movie_id
GROUP BY m.id
ORDER BY m.year, m.title
LIMIT 20;
"
echo " "

echo "6. Определить самый распространенный жанр фильма и количество фильмов в этом жанре. Отдельную таблицу для жанров не использовать, жанры нужно извлекать из таблицы movies."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
WITH RECURSIVE split(genre, rest) AS (
    SELECT
        '',
        genres || '|'
    FROM movies
    WHERE genres IS NOT NULL AND genres != '(no genres listed)'
    UNION ALL
    SELECT
        substr(rest, 1, instr(rest, '|') - 1),
        substr(rest, instr(rest, '|') + 1)
    FROM split
    WHERE rest != ''
)
SELECT
    genre,
    COUNT(*) AS movie_count
FROM split
WHERE genre != '' AND genre != '(no genres listed)'
GROUP BY genre
ORDER BY movie_count DESC
LIMIT 1;
"
echo " "

echo "7. Вывести список из 10 последних зарегистрированных пользователей в формате \"Фамилия Имя|Дата регистрации\" (сначала фамилия, потом имя)."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
SELECT
    name || '|' || register_date AS user_info
FROM users
ORDER BY register_date DESC
LIMIT 10;
"
echo " "

echo "8. С помощью рекурсивного CTE определить, на какие дни недели приходился ваш день рождения в каждом году."
echo --------------------------------------------------
sqlite3 movies_rating.db -box -echo "
WITH RECURSIVE years AS (
    SELECT strftime('%Y', 'now') AS yr
    UNION ALL
    SELECT yr - 1
    FROM years
    WHERE yr > 1980
),
birthdays AS (
    SELECT
        yr,
        date(yr || '-05-13') AS bday
    FROM years
)
SELECT
    bday AS Дата,
    CASE strftime('%w', bday)
        WHEN '0' THEN 'Воскресенье'
        WHEN '1' THEN 'Понедельник'
        WHEN '2' THEN 'Вторник'
        WHEN '3' THEN 'Среда'
        WHEN '4' THEN 'Четверг'
        WHEN '5' THEN 'Пятница'
        WHEN '6' THEN 'Суббота'
    END AS День_недели
FROM birthdays
ORDER BY bday;
"
echo " "