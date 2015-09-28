<?php

  require "../models/human.php";
  require "../dao/dao.php";

  $decoded = json_decode($_POST["details"], true);
  $count = count($decoded);

  $human = Human::fromDetails($decoded["id"], $decoded["email"], $decoded["url"]);
  echo updateHuman($human);
