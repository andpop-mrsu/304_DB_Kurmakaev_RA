-- 1. Категории автомобилей
CREATE TABLE car_category (
    category_id INTEGER PRIMARY KEY,
    name TEXT NOT NULL UNIQUE
);

-- 2. Мастера
CREATE TABLE mechanic (
    mechanic_id INTEGER PRIMARY KEY,
    full_name TEXT NOT NULL,
    specialization TEXT NOT NULL,
    commission_rate REAL NOT NULL CHECK (commission_rate BETWEEN 0 AND 100),
    is_active BOOLEAN NOT NULL DEFAULT 1,
    hire_date DATE NOT NULL,
    dismissal_date DATE
);

-- 3. Услуги
CREATE TABLE service (
    service_id INTEGER PRIMARY KEY,
    name TEXT NOT NULL UNIQUE,
    base_duration_min INTEGER NOT NULL CHECK (base_duration_min > 0),
    base_price REAL NOT NULL CHECK (base_price >= 0)
);

-- 4. Услуги по категориям
CREATE TABLE service_category (
    service_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    duration_min INTEGER NOT NULL CHECK (duration_min > 0),
    price REAL NOT NULL CHECK (price >= 0),
    PRIMARY KEY (service_id, category_id),
    FOREIGN KEY (service_id) REFERENCES service(service_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES car_category(category_id) ON DELETE CASCADE
);

-- 5. Связь "мастер — может выполнять услугу"
CREATE TABLE mechanic_service (
    mechanic_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    PRIMARY KEY (mechanic_id, service_id),
    FOREIGN KEY (mechanic_id) REFERENCES mechanic(mechanic_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES service(service_id) ON DELETE CASCADE
);

-- 6. Клиенты
CREATE TABLE client (
    client_id INTEGER PRIMARY KEY,
    full_name TEXT NOT NULL,
    phone TEXT NOT NULL UNIQUE,
    email TEXT
);

-- 7. Автомобили
CREATE TABLE car (
    car_id INTEGER PRIMARY KEY,
    client_id INTEGER NOT NULL,
    brand TEXT NOT NULL,
    model TEXT NOT NULL,
    year INTEGER NOT NULL CHECK (year BETWEEN 1900 AND 2030),
    category_id INTEGER NOT NULL,
    vin TEXT UNIQUE,
    FOREIGN KEY (client_id) REFERENCES client(client_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES car_category(category_id) ON DELETE RESTRICT
);

-- 8. Записи на СТО
CREATE TABLE appointment (
    appointment_id INTEGER PRIMARY KEY,
    car_id INTEGER NOT NULL,
    mechanic_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    scheduled_start DATETIME NOT NULL,
    scheduled_end DATETIME NOT NULL,
    status TEXT NOT NULL CHECK (status IN ('ожидает', 'подтверждена', 'отменена', 'выполнена')),
    FOREIGN KEY (car_id) REFERENCES car(car_id) ON DELETE CASCADE,
    FOREIGN KEY (mechanic_id) REFERENCES mechanic(mechanic_id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES service(service_id) ON DELETE RESTRICT,
    CHECK (scheduled_end > scheduled_start)
);

-- 9. Лог выполненных работ (факт)
CREATE TABLE work_log (
    work_id INTEGER PRIMARY KEY,
    appointment_id INTEGER,
    mechanic_id INTEGER NOT NULL,
    actual_start DATETIME NOT NULL,
    actual_end DATETIME NOT NULL,
    final_price REAL NOT NULL CHECK (final_price >= 0),
    notes TEXT,
    FOREIGN KEY (appointment_id) REFERENCES appointment(appointment_id) ON DELETE SET NULL,
    FOREIGN KEY (mechanic_id) REFERENCES mechanic(mechanic_id) ON DELETE RESTRICT,
    CHECK (actual_end > actual_start)
);

-- 10. График работы мастера
CREATE TABLE mechanic_schedule (
    schedule_id INTEGER PRIMARY KEY,
    mechanic_id INTEGER NOT NULL,
    day_of_week INTEGER NOT NULL CHECK (day_of_week BETWEEN 1 AND 7),
    start_time TEXT NOT NULL,
    end_time TEXT NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT 1,
    FOREIGN KEY (mechanic_id) REFERENCES mechanic(mechanic_id) ON DELETE CASCADE,
    CHECK (end_time > start_time)
);

-- Данные
INSERT INTO car_category (category_id, name) VALUES
(1, 'Легковые'), (2, 'Внедорожники/SUV'), (3, 'Микроавтобусы'), (4, 'Грузовые до 3.5т');

INSERT INTO mechanic (mechanic_id, full_name, specialization, commission_rate, is_active, hire_date, dismissal_date) VALUES
(1, 'Иванов Иван Иванович', 'Диагностика и электрика', 15.0, 1, '2022-03-10', NULL),
(2, 'Петров Пётр Петрович', 'Двигатель и трансмиссия', 18.0, 1, '2021-11-05', NULL),
(3, 'Сидоров Сидор Сидорович', 'Кузовной ремонт', 20.0, 0, '2020-01-15', '2024-08-20'),
(4, 'Козлов Дмитрий Сергеевич', 'Шиномонтаж и балансировка', 12.0, 1, '2023-06-12', NULL),
(5, 'Волкова Анна Владимировна', 'Климатические системы', 16.5, 1, '2024-01-20', NULL);

INSERT INTO service (service_id, name, base_duration_min, base_price) VALUES
(1, 'Замена масла', 30, 800),
(2, 'Диагностика ЭБУ', 60, 1500),
(3, 'Ремонт подвески', 120, 3000),
(4, 'Покраска двери', 240, 5000),
(5, 'Заправка кондиционера', 45, 1200);

INSERT INTO service_category (service_id, category_id, duration_min, price) VALUES
(1, 1, 30, 800), (2, 1, 60, 1500), (3, 1, 120, 3000), (4, 1, 240, 5000), (5, 1, 45, 1200),
(1, 2, 40, 1000), (2, 2, 70, 1800), (3, 2, 150, 3800), (4, 2, 270, 6000), (5, 2, 55, 1500),
(1, 4, 45, 1100), (5, 4, 50, 1350);

INSERT INTO mechanic_service (mechanic_id, service_id) VALUES
(1, 1), (1, 2), (2, 1), (2, 3), (3, 4), (4, 1), (4, 2), (5, 1), (5, 2), (5, 5);

INSERT INTO client (client_id, full_name, phone, email) VALUES
(1, 'Кузнецов Алексей', '+79001112233', 'kuzn@example.com'),
(2, 'Морозова Елена', '+79004445566', NULL),
(3, 'Смирнов Дмитрий', '+79007778899', 'smirnov.d@example.com'),
(4, 'Новикова Ольга', '+79001113344', 'olga.n@example.com'),
(5, 'Григорьев Максим', '+79009990011', 'grig.max@example.com');

INSERT INTO car (car_id, client_id, brand, model, year, category_id, vin) VALUES
(1, 1, 'Toyota', 'Camry', 2020, 1, 'JTDKARFU9EJ123456'),
(2, 2, 'Land Rover', 'Discovery', 2022, 2, 'SALWA2FV1KA123789'),
(3, 3, 'Volkswagen', 'Tiguan', 2021, 2, 'WVGZZZ5NZMW123457'),
(4, 4, 'Lada', 'Vesta', 2023, 1, 'XTA211770J1234567'),
(5, 5, 'Gazelle', 'Business', 2022, 4, 'RU123456789GZEL01');

INSERT INTO appointment (appointment_id, car_id, mechanic_id, service_id, scheduled_start, scheduled_end, status) VALUES
(1, 1, 1, 1, '2025-11-30 09:00:00', '2025-11-30 09:30:00', 'подтверждена'),
(2, 2, 2, 3, '2025-11-30 10:00:00', '2025-11-30 12:00:00', 'ожидает'),
(3, 1, 3, 4, '2025-05-15 14:00:00', '2025-05-15 18:00:00', 'выполнена'),
(4, 1, 1, 2, '2025-11-29 14:00:00', '2025-11-29 15:00:00', 'выполнена'),
(5, 2, 3, 4, '2025-07-10 09:00:00', '2025-07-10 13:00:00', 'выполнена'),
(6, 3, 2, 3, '2025-12-01 10:30:00', '2025-12-01 12:30:00', 'подтверждена'),
(7, 4, 4, 1, '2025-12-02 09:15:00', '2025-12-02 09:55:00', 'ожидает'),
(8, 5, 5, 1, '2025-12-03 11:00:00', '2025-12-03 11:50:00', 'подтверждена'),
(9, 4, 5, 5, '2025-11-28 16:00:00', '2025-11-28 16:45:00', 'выполнена'),
(10, 2, 5, 2, '2025-12-04 14:00:00', '2025-12-04 15:10:00', 'ожидает'),
(11, 3, 4, 1, '2025-12-05 08:30:00', '2025-12-05 09:00:00', 'подтверждена'),
(12, 5, 1, 2, '2025-12-05 13:00:00', '2025-12-05 14:00:00', 'отменена'),
(13, 4, 2, 3, '2025-11-25 10:00:00', '2025-11-25 12:00:00', 'выполнена'),
(14, 1, 3, 1, '2025-06-30 11:00:00', '2025-06-30 11:30:00', 'выполнена');

INSERT INTO work_log (work_id, appointment_id, mechanic_id, actual_start, actual_end, final_price, notes) VALUES
(1, 1, 1, '2025-11-30 09:05:00', '2025-11-30 09:32:00', 800, NULL),
(2, 3, 3, '2025-05-15 14:10:00', '2025-05-15 18:20:00', 6000, 'Покраска передней двери'),
(3, 4, 1, '2025-11-29 14:05:00', '2025-11-29 15:02:00', 1500, 'Ошибок не найдено'),
(4, 5, 3, '2025-07-10 09:10:00', '2025-07-10 13:15:00', 6000, 'Покраска бампера'),
(5, 9, 5, '2025-11-28 16:05:00', '2025-11-28 16:48:00', 1200, 'Фреон R134a'),
(6, 13, 2, '2025-11-25 10:10:00', '2025-11-25 12:20:00', 3000, 'Замена амортизаторов'),
(7, 8, 5, '2025-12-03 11:08:00', '2025-12-03 12:00:00', 1000, 'Масло Shell'),
(8, 11, 4, '2025-12-05 08:32:00', '2025-12-05 09:01:00', 1000, 'Быстрая замена'),
(9, 14, 3, '2025-06-30 11:05:00', '2025-06-30 11:35:00', 1000, 'Последняя работа'),
(10, NULL, 4, '2025-11-30 17:00:00', '2025-11-30 17:25:00', 800, 'Экспресс-диагностика'),
(11, NULL, 2, '2025-12-01 18:15:00', '2025-12-01 18:50:00', 1000, 'Срочная замена масла'),
(12, NULL, 5, '2025-12-02 14:30:00', '2025-12-02 15:20:00', 1500, 'Грузовой фургон'),
(13, NULL, 1, '2025-12-04 09:00:00', '2025-12-04 10:10:00', 1800, 'Ручная диагностика'),
(14, NULL, 3, '2025-08-10 15:00:00', '2025-08-10 17:30:00', 4500, 'Кузовной ремонт'),
(15, NULL, 5, '2025-12-06 10:00:00', '2025-12-06 10:50:00', 1350, 'Заправка фургона (грузовая категория)');

-- 10. График работы мастера
INSERT INTO mechanic_schedule (mechanic_id, day_of_week, start_time, end_time, is_active) VALUES
(1, 1, '09:00', '18:00', 1),
(1, 2, '09:00', '18:00', 1),
(1, 3, '10:00', '19:00', 1),
(1, 4, '09:00', '18:00', 1),
(1, 5, '09:00', '17:00', 1),
(2, 1, '08:00', '16:00', 1),
(2, 2, '08:00', '16:00', 1),
(2, 4, '08:00', '16:00', 1),
(2, 5, '08:00', '16:00', 1),
(3, 1, '09:30', '18:30', 0),
(3, 3, '09:30', '18:30', 0),
(3, 5, '09:30', '16:00', 0),
(4, 6, '09:00', '15:00', 1), 
(5, 1, '10:00', '19:00', 1),
(5, 2, '10:00', '19:00', 1),
(5, 3, '10:00', '19:00', 1),
(5, 4, '10:00', '19:00', 1),
(5, 5, '10:00', '18:00', 1);