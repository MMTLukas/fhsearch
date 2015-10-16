<?php

  require "../models/human.php";
  require "../dao/dao.php";

  $decoded = json_decode($_POST["data"], true);
  $count = count($decoded);

  for ($i = 0; $i < $count; $i++) {
    $human = Human::fromOverview($decoded[$i]["prename"], $decoded[$i]["lastname"], $decoded[$i]["department"], $decoded[$i]["type"], $decoded[$i]["id"]);
<<<<<<< HEAD
    echo createHuman($human);
=======
    echo insertHuman($human);
>>>>>>> a79edd6e4a4d48eb02a7c334738a9a6e037ac922
  }

