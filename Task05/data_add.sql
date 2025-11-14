INSERT OR IGNORE INTO users (name, email, gender, register_date, occupation_id)
VALUES
('Кочетов Владислав Андреевич', 'kochetovva@gmail.com', 'male', date('now'),
 (SELECT id FROM occupations ORDER BY id LIMIT 1)),
('Кувакин Роман Александрович', 'kuvakinra@gmail.com', 'male', date('now'),
 (SELECT id FROM occupations ORDER BY id LIMIT 1)),
 ('Курмакаев Ренард Анварович', 'kurmakaevra@gmail.com', 'male', date('now'),
 (SELECT id FROM occupations ORDER BY id LIMIT 1)),
('Луковатая Ксения Вадимовна', 'lukovatayakv@gmail.com', 'female', date('now'),
 (SELECT id FROM occupations ORDER BY id LIMIT 1)),
('Мигачев Иван Павлович', 'migachevip@gmail.com', 'male', date('now'),
 (SELECT id FROM occupations ORDER BY id LIMIT 1));



INSERT OR IGNORE INTO movies (title, year)
VALUES
('Мажор (2021)', 2021),
('В погоне за счастьем (2006)', 2006),
('Остров проклятых (2009)', 2009);


INSERT OR IGNORE INTO genres (name) VALUES ('Sci-Fi');
INSERT OR IGNORE INTO genres (name) VALUES ('Action');
INSERT OR IGNORE INTO genres (name) VALUES ('Crime');
INSERT OR IGNORE INTO genres (name) VALUES ('Thriller');
INSERT OR IGNORE INTO genres (name) VALUES ('Drama');

INSERT OR IGNORE INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id FROM movies m JOIN genres g ON g.name = 'Action'
WHERE m.title = 'Мажор (2021)';

INSERT OR IGNORE INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id FROM movies m JOIN genres g ON g.name = 'Drama'
WHERE m.title = 'В погоне за счастьем (2006)';

INSERT OR IGNORE INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id FROM movies m JOIN genres g ON g.name = 'Thriller'
WHERE m.title = 'Остров проклятых (2009)';


INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT u.id, m.id, 4.7, strftime('%s','now')
FROM users u JOIN movies m ON m.title = 'Мажор (2021)'
WHERE u.email = 'kurmakaevra@gmail.com'
AND NOT EXISTS (
    SELECT 1 FROM ratings r WHERE r.user_id = u.id AND r.movie_id = m.id
);

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT u.id, m.id, 5.0, strftime('%s','now')
FROM users u JOIN movies m ON m.title = 'В погоне за счастьем (2006)'
WHERE u.email = 'kurmakaevra@gmail.com'
AND NOT EXISTS (
    SELECT 1 FROM ratings r WHERE r.user_id = u.id AND r.movie_id = m.id
);

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT u.id, m.id, 4.9, strftime('%s','now')
FROM users u JOIN movies m ON m.title = 'Остров проклятых (2009)'
WHERE u.email = 'kurmakaevra@gmail.com'
AND NOT EXISTS (
    SELECT 1 FROM ratings r WHERE r.user_id = u.id AND r.movie_id = m.id
);