<?php

  require "../models/human.php";
  require "../dao/dao.php";

  $decoded = json_decode($_POST["data"], true);
  $count = count($decoded);

  for ($i = 0; $i < $count; $i++) {
    $human = Human::fromOverview($decoded[$i]["prename"], $decoded[$i]["lastname"], $decoded[$i]["department"], $decoded[$i]["type"], $decoded[$i]["id"]);
    echo createHuman($human);
  }

