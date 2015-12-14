<?php

require "../dao/dao.php";

$callback = '';
if (isset($_GET['callback'])) {
    $callback = filter_var($_GET['callback'], FILTER_SANITIZE_STRING);
}

echo $callback . '(' . json_encode(getStatistic()) . ');';