<?php
if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
    if (checkUser($_SERVER['PHP_AUTH_USER'])) {
        echo '<h1>Доступ запрещен!</h1>';
    }
}
else
{
    header('WWW-Authenticate: Basic realm="Secured Zone"');
    header('HTTP/1.0 401 Unauthorized');

    echo 'Необходима авторизация';
}

echo 'Прошел дальше';
header('HTTP/1.0 401 Unauthorized');

function checkUser($login) {
    return $login == 'admin' ? true : false;
}

