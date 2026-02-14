@echo off
echo ================================================
echo    مرحباً بك في منصة املاك
echo    Amlak Platform - Imam Muhammad bin Saud
echo ================================================
echo.
echo جاري تشغيل الخادم...
echo Starting server on http://localhost
echo.
echo للدخول: http://localhost
echo البريد: admin@mscs.org.sa
echo كلمة المرور: 12345678
echo.
echo ملاحظة: لا تغلق هذه النافذة
echo.
php artisan serve --host=0.0.0.0 --port=80
pause
