<?php
  /**
   * Created by PhpStorm.
   * User: Enthusiasmus
   * Date: 01.08.14
   * Time: 23:11
   */

  $DB_NAME = "fhs";
  $DB_USER = "root";
  $DB_PASS = "";
  $DSN = "mysql:dbname=$DB_NAME;host=localhost";

  function insertHuman(Human $human)
  {
    global $DSN, $DB_USER, $DB_PASS;
    $adapter = new PDO($DSN, $DB_USER, $DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")) or die(false);

    $insert = $adapter->prepare("INSERT INTO people (prename, lastname, department, type, id) VALUES (:prename, :lastname, :department, :type, :id)");
    $result = $insert->execute(array(
      ":prename" => $human->getPrename(),
      ":lastname" => $human->getLastname(),
      ":department" => $human->getDepartment(),
      ":type" => $human->getType(),
      ":id" => $human->getId()
    ));

    if (!$result) {
      header("HTTP/1.0 500 Internal Server Error");
    } else {
      return $adapter->lastInsertId();
    }
  }

  function updateHuman(Human $human)
  {
    global $DSN, $DB_USER, $DB_PASS;
    $adapter = new PDO($DSN, $DB_USER, $DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")) or die(false);

    $update = $adapter->prepare("UPDATE people SET pictureUrlFhs = :pictureUrlFhs, email = :email WHERE id = :id");
    $result = $update->execute(array(
      ":pictureUrlFhs" => $human->getPictureUrlFhs(),
      ":email" => $human->getEmail(),
      ":id" => $human->getId()
    ));

    if (!$result) {
      header("HTTP/1.0 500 Internal Server Error");
    } else {
      return $human->getId();
    }
  }

  function getPeopleByFhs($fhsId)
  {
    global $DSN, $DB_USER, $DB_PASS;
    $adapter = new PDO($DSN, $DB_USER, $DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")) or die(false);

    $select = $adapter->prepare("SELECT `prename`, `lastname`, `email`, `pictureUrlFhs` AS url FROM `people` WHERE `id` LIKE :fhsId ORDER BY `id` ASC LIMIT 20");
    $result = $select->execute(array(
      ":fhsId" => htmlentities($fhsId . "%")
    ));

    if (!$result) {
      header("HTTP/1.0 500 Internal Server Error");
    } else {
      return $select->fetchAll(PDO::FETCH_ASSOC);
    }
  }

  function getPeopleByName($name)
  {
    global $DSN, $DB_USER, $DB_PASS;
    $adapter = new PDO($DSN, $DB_USER, $DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")) or die(false);

    $queryExtended = explode(" ", $name);
    $amountQueryParts = count($queryExtended);

    if ($amountQueryParts == 1) {
      $select = $adapter->prepare("SELECT `prename`, `lastname`, `email`, `pictureUrlFhs` AS url FROM `people` WHERE `lastname` LIKE :name OR `prename` LIKE :name ORDER BY `lastname` ASC LIMIT 20");
      $result = $select->execute(array(
        ":name" => htmlentities("%" . $name . "%")
      ));
    } else if ($amountQueryParts == 2) {
      $select = $adapter->prepare("SELECT `prename`, `lastname`, `email`, `pictureUrlFhs` AS url FROM `people` WHERE `prename` LIKE :firstInput AND `lastname` LIKE :secondInput OR `prename` LIKE :secondInput AND `lastname` LIKE :firstInput OR `prename` LIKE :bothInputs  ORDER BY `lastname` ASC LIMIT 20");
      $result = $select->execute(array(
        ":firstInput" => htmlentities("%" . $queryExtended[0] . "%"),
        ":secondInput" => htmlentities("%" . $queryExtended[1] . "%"),
        ":bothInputs" => htmlentities("%" . $queryExtended[0] . " " . $queryExtended[1] . "%"),
      ));
    } else if ($amountQueryParts == 3) {
      $select = $adapter->prepare("SELECT `prename`, `lastname`, `email`, `pictureUrlFhs` AS url FROM `people` WHERE `prename` LIKE :firstPossibilityPrename AND `lastname` LIKE :firstPossibilityLastname OR `prename` LIKE :secondPossibilityPrename AND `lastname` LIKE :secondPossibilityLastname ORDER BY `lastname` ASC LIMIT 20");

      $firstInput = $queryExtended[0];
      $secondInput = $queryExtended[1];
      $thirdInput = $queryExtended[2];

      $result = $select->execute(array(
        ":firstPossibilityPrename" => htmlentities("%" . $firstInput . " " . $secondInput . "%"),
        ":firstPossibilityLastname" => htmlentities("%" . $thirdInput . "%"),
        ":secondPossibilityLastname" => htmlentities("%" . $thirdInput . "%"),
        ":secondPossibilityPrename" => htmlentities("%" . $secondInput . " " . $thirdInput . "%"),
      ));
    }

    if (!$result) {
      header("HTTP/1.0 500 Internal Server Error");
    } else {
      return $select->fetchAll(PDO::FETCH_ASSOC);
    }
  }


  /** Error-Analysis
   * $adapter->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
   * prepare
   * execute
   * print_r($insert->errorInfo());
   */