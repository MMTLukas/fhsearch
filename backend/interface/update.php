<?php

  require "../models/human.php";
  require "../dao/dao.php";

  if (isset($_POST["id"])) {
    $human = Human::fromDetails($_POST["id"], $_POST["email"], $_POST["url"]);
    return updateHuman($human);
  }