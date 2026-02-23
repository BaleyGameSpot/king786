@echo off
echo Starting build...
gradlew.bat clean assembleDevDebug installDevDebug
echo Build completed with exit code: %ERRORLEVEL%
pause

