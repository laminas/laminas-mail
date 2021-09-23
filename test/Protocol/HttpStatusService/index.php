<?php

if (isset($_GET['sleep'])) {
    sleep($_GET['sleep']);
}

header('HTTP/1.1 200 OK');
