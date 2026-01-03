@echo off
REM Database setup script
C:\xampp\mysql\bin\mysql.exe -u root < db_schema.sql
echo Database setup completed!
pause
