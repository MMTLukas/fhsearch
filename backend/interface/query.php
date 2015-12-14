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
        $data = str_replace(" ", " +", $data);

        if (stripos($data, "fhs")) {
            $positionFHS = stripos($data, "fhs");
            $positionNextParameter = stripos($data, " ", $positionFHS);

            if ($positionNextParameter !== false) {
                if ($positionNextParameter - $positionFHS < 8) {
                    $stringToReplace = substr($data, stripos($data, "fhs"), $positionNextParameter - 1);
                    $data = str_replace($stringToReplace, $stringToReplace . "*", $data);
                }
            } else {
                $data = $data . "*";
            }
        }

        //var_dump($data);

        $results = getPeople($data, $offset);
    }
    echo json_encode($results);
}

