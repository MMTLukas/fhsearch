<?php

  require "../models/human.php";
  require "../dao/dao.php";

var_dump($_POST);


  if (isset($decoded["details"])) {
    $human = Human::fromDetails($decoded["details"]["id"], $decoded["details"]["email"], $decoded["details"]["url"]);
    return updateHuman($human);
  }