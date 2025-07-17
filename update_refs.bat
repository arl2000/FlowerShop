@echo off
setlocal enabledelayedexpansion

for %%f in (*.php *.html) do (
    echo Processing %%f
    
    rem Create temp file
    set "tempfile=%%f.tmp"
    if exist "!tempfile!" del "!tempfile!"
    
    rem Process CSS references
    for /f "tokens=*" %%a in (%%f) do (
        set "line=%%a"
        set "line=!line:href=\"=href=\"css/!"
        echo !line! >> "!tempfile!"
    )
    
    move /y "!tempfile!" "%%f" >nul
    
    rem Process JS references
    for /f "tokens=*" %%a in (%%f) do (
        set "line=%%a"
        set "line=!line:src=\"=src=\"js/!"
        echo !line! >> "!tempfile!"
    )
    
    move /y "!tempfile!" "%%f" >nul
)

echo Update complete
del update_refs.bat
