<?php

  require "../dao/dao.php";

  $handle = fopen("php://input", "r");
  $jsonInput = fgets($handle);
  $decoded = json_decode($jsonInput, true);

  if (isset($decoded["data"])) {
    $data = $decoded["data"];
    $offset = $decoded["offset"];
    $results = array();

    if (strlen($data) >= 3) {
      $results = getPeople(strtolower($data), $offset);
    }

    echo json_encode($results);
  }