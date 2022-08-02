<?php

declare(strict_types=1);

if (isset($_GET['sleep'])) {
    sleep((int) $_GET['sleep']);
}

header('HTTP/1.1 200 OK');
