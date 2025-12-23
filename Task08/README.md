# Лабораторная работа 8: CRUD-приложение для СТО

## Требования
- PHP 8.0+
- Расширение `pdo_sqlite`

## Установка и запуск

1. **Создайте структуру проекта**:
```bash
mkdir -p Task08/{data,public/{mechanics,schedule,work_logs},src}

## Сброс БД к исходному состоянию
```powershell
Copy-Item data/db.sqlite data/db_original.sqlite  # Создать резервную копию ОДИН РАЗ
Remove-Item data/db.sqlite -Force; Copy-Item data/db_original.sqlite data/db.sqlite  # Сбросить