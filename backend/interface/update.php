<?php

  require "../models/human.php";
  require "../dao/dao.php";

  $decoded = json_decode($_POST["details"], true);

  $human = Human::fromDetails($decoded["id"], $decoded["email"], $decoded["url"], $decoded["phone"], $decoded["mobile"], $decoded["room"], $decoded["state"]);
  echo updateHumanDetails($human);
