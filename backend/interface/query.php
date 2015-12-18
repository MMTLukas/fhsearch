<?php

require "../dao/dao.php";

$handle = fopen("php://input", "r");
$jsonInput = fgets($handle);
$decoded = json_decode($jsonInput, true);

if (isset($decoded["data"])) {
    $data = strtolower($decoded["data"]);
    $offset = strtolower($decoded["offset"]);
    $results = array();

    if (strlen($data) >= 3 && !(strlen($data) < 6 && stripos($data, "fhs") !== false)) {
        $data = "+" . $data;
        $data = str_replace(" ", "* +", $data);
        $data = $data . "*";
        $results = getPeople($data, $offset);
    }
    echo json_encode($results);
}

