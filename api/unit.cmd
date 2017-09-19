set php=d:\OpenServer\modules\php\PHP-7
set PATH=%PATH%;%php%

call vendor/bin/phpunit.bat "d:\OpenServer\domains\drom\api\unit"