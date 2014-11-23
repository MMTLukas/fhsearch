<?php

  require "../dao/dao.php";

  $handle = fopen("php://input", "r");
  $jsonInput = fgets($handle);
  $decoded = json_decode($jsonInput, true);

  if (isset($decoded["data"])) {
    $data = $decoded["data"];
    $results = [];

    //stripos is not case sensitive
    //strpos is case sensitive
    if (stripos($data, "fhs") !== false) {
      if (strlen($data) < 6) {
        return json_encode([]);
      }
      $results = getPeopleByFhs(strtolower($data));
    } else {
      $results = getPeopleByName($data);
    }

    echo json_encode($results);
  }