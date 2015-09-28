<?php

  require "../dao/dao.php";

  $handle = fopen("php://input", "r");
  $jsonInput = fgets($handle);
  $decoded = json_decode($jsonInput, true);

  if (isset($decoded["data"])) {
    $data = $decoded["data"];
    $offset = @$decoded["offset"] ? $decoded["offset"] : 0;
    $results = [];

    if (stripos($data, "fhs") !== false && strlen($data) >= 6) {
      $results = getPeopleByFhs(strtolower($data), $offset);
    }  else {
      $results = getPeopleByNameAndGroup($data, $offset);
    }

    echo json_encode($results);
  }
