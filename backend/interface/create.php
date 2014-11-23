<?php
  /**
   * Created by PhpStorm.
   * User: Enthusiasmus
   * Date: 12.10.2014
   * Time: 10:07
   */

  require "../models/human.php";
  require "../dao/dao.php";

  $handle = fopen("php://input", "r");
  $jsonInput = fgets($handle);
  $decoded = json_decode($jsonInput, true);
  $count = count($decoded["data"]);

  for ($i = 0; $i < $count; $i++) {
    $human = Human::fromOverview($decoded["data"][$i]["prename"], $decoded["data"][$i]["lastname"], $decoded["data"][$i]["department"], $decoded["data"][$i]["type"], $decoded["data"][$i]["id"]);
    echo insertHuman($human);
  }

