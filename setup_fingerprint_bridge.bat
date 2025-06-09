@echo off
echo ========================================
echo FingerprintBridge Module Setup
echo ========================================
echo.

echo 1. Creating FinPro database and test data...
mysql -u root < setup_fin_pro_test.sql
if %errorlevel% neq 0 (
    echo Error creating FinPro database
    pause
    exit /b 1
)
echo    ✓ FinPro database created

echo.
echo 2. Creating test students...
mysql -u root < setup_test_students.sql
if %errorlevel% neq 0 (
    echo Error creating test students
    pause
    exit /b 1
)
echo    ✓ Test students created

echo.
echo 3. Creating FingerprintBridge tables...
mysql -u root < create_fingerprint_bridge_tables.sql
if %errorlevel% neq 0 (
    echo Error creating FingerprintBridge tables
    pause
    exit /b 1
)
echo    ✓ FingerprintBridge tables created

echo.
echo 4. Verifying setup...
mysql -u root -e "SELECT COUNT(*) as fin_pro_records FROM fin_pro.att_log; SELECT COUNT(*) as students FROM studentfinger.students; SELECT COUNT(*) as fingerprint_tables FROM information_schema.tables WHERE table_schema='studentfinger' AND table_name LIKE 'fingerprint%%';"

echo.
echo ========================================
echo Setup completed successfully!
echo ========================================
echo.
echo You can now access:
echo - Dashboard: http://studentfinger.me/fingerprint-bridge
echo - Test Page: http://studentfinger.me/test_fingerprint_bridge.php
echo.
echo To test CLI import:
echo php spark fingerprint:import --test --start-date=2025-01-09 --end-date=2025-01-09
echo.
pause
