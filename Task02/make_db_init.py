import csv
import os
import re

DATASET_DIR = 'dataset'
MOVIES_FILE = os.path.join(DATASET_DIR, 'movies.csv')
RATINGS_FILE = os.path.join(DATASET_DIR, 'ratings.csv')
TAGS_FILE = os.path.join(DATASET_DIR, 'tags.csv')
USERS_FILE = os.path.join(DATASET_DIR, 'users.txt') 

SQL_FILE = 'db_init.sql'

def generate_create_table():
    return """
DROP TABLE IF EXISTS movies;
DROP TABLE IF EXISTS ratings;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS users;

CREATE TABLE movies (
    id INTEGER PRIMARY KEY,
    title TEXT,
    year INTEGER,
    genres TEXT
);

CREATE TABLE ratings (
    id INTEGER PRIMARY KEY,
    user_id INTEGER,
    movie_id INTEGER,
    rating REAL,
    timestamp INTEGER
);

CREATE TABLE tags (
    id INTEGER PRIMARY KEY,
    user_id INTEGER,
    movie_id INTEGER,
    tag TEXT,
    timestamp INTEGER
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY,
    name TEXT,
    email TEXT,
    gender TEXT,
    register_date TEXT,
    occupation TEXT
);
"""

def sql_escape(s):
    if s is None:
        return 'NULL'
    return "'" + str(s).replace("'", "''") + "'"

def extract_year_from_title(title):
    match = re.search(r'\((\d{4})\)', title)
    return int(match.group(1)) if match else None

def clean_title(title):
    return re.sub(r'\s*\(\d{4}\)\s*$', '', title).strip()

def generate_inserts_movies(file_path):
    inserts = []
    with open(file_path, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            movie_id = row['movieId']
            raw_title = row['title']
            genres = row.get('genres', '(no genres listed)')

            year = extract_year_from_title(raw_title)
            title = clean_title(raw_title)

            values = [
                movie_id,
                sql_escape(title),
                'NULL' if year is None else str(year),
                sql_escape(genres)
            ]
            inserts.append(f"INSERT INTO movies (id, title, year, genres) VALUES ({', '.join(values)});")
    return '\n'.join(inserts)

def generate_inserts_ratings(file_path):
    inserts = []
    with open(file_path, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            values = [
                sql_escape(row['userId']),
                sql_escape(row['movieId']),
                sql_escape(row['rating']),
                sql_escape(row['timestamp'])
            ]
            inserts.append(f"INSERT INTO ratings (user_id, movie_id, rating, timestamp) VALUES ({', '.join(values)});")
    return '\n'.join(inserts)

def generate_inserts_tags(file_path):
    inserts = []
    with open(file_path, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            values = [
                sql_escape(row['userId']),
                sql_escape(row['movieId']),
                sql_escape(row['tag']),
                sql_escape(row['timestamp'])
            ]
            inserts.append(f"INSERT INTO tags (user_id, movie_id, tag, timestamp) VALUES ({', '.join(values)});")
    return '\n'.join(inserts)

def generate_inserts_users(file_path):
    inserts = []
    with open(file_path, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f, delimiter='|', fieldnames=['id', 'name', 'email', 'gender', 'register_date', 'occupation'])
        for row in reader:
            values = [
                sql_escape(row['id']),
                sql_escape(row['name']),
                sql_escape(row['email']),
                sql_escape(row['gender']),
                sql_escape(row['register_date']),
                sql_escape(row['occupation'])
            ]
            inserts.append(f"INSERT INTO users (id, name, email, gender, register_date, occupation) VALUES ({', '.join(values)});")
    return '\n'.join(inserts)

def main():
    files = [MOVIES_FILE, RATINGS_FILE, TAGS_FILE, USERS_FILE]
    for f in files:
        if not os.path.exists(f):
            raise FileNotFoundError(f"Missing file: {f}")
            
    sql_content = generate_create_table()
    sql_content += generate_inserts_movies(MOVIES_FILE) + '\n'
    sql_content += generate_inserts_ratings(RATINGS_FILE) + '\n'
    sql_content += generate_inserts_tags(TAGS_FILE) + '\n'
    sql_content += generate_inserts_users(USERS_FILE) + '\n'

    with open(SQL_FILE, 'w', encoding='utf-8') as f:
        f.write(sql_content)

if __name__ == '__main__':
    main()