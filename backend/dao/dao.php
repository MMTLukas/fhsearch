<?php
  /**
   * Created by PhpStorm.
   * User: Enthusiasmus
   * Date: 01.08.14
   * Time: 23:11
   */

  include "../config.php";

  function insertHuman(Human $human)
  {
    global $DSN, $DB_USER, $DB_PASS;
    $adapter = new PDO($DSN, $DB_USER, $DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")) or die(false);

    $insert = $adapter->prepare("INSERT INTO test (prename, lastname, department, type, id) VALUES (:prename, :lastname, :department, :type, :id)");
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

    $update = $adapter->prepare("UPDATE test SET pictureUrlFhs = :pictureUrlFhs, email = :email WHERE id = :id");
    $result = $update->execute(array(
      ":pictureUrlFhs" => $human->getPictureUrlFhs() == "" ? null : $human->getPictureUrlFhs(),
      ":email" => $human->getEmail() == "" ? null : $human->getEmail(),
      ":id" => $human->getId()
    ));

    if (!$result) {
      header("HTTP/1.0 500 Internal Server Error");
    } else {
      return $human->getId();
    }
  }

  function getPeopleByFhs($fhsId, $offset)
  {
    global $DSN, $DB_USER, $DB_PASS, $LIMIT;
    $adapter = new PDO($DSN, $DB_USER, $DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")) or die(false);

    $select = $adapter->prepare("SELECT `prename`, `lastname`, `email`, `id`, `department` FROM `people` WHERE `id` LIKE :fhsId ORDER BY `id` ASC LIMIT :limit OFFSET :offset");
    $select->bindParam(':fhsId', htmlentities($fhsId . "%"), PDO::PARAM_STR);
    $select->bindParam(':limit', $LIMIT, PDO::PARAM_INT);
    $select->bindParam(':offset', $offset, PDO::PARAM_INT);
    $result = $select->execute();

    $selectCount = $adapter->prepare("SELECT count(*) AS count FROM `people` WHERE `id` LIKE :fhsId");
    $selectCount->bindParam(':fhsId', htmlentities($fhsId . "%"), PDO::PARAM_STR);
    $selectCount->execute();

    $count = $selectCount->fetch(PDO::FETCH_ASSOC);
    $count = $count["count"];

    if (!$result) {
      header("HTTP/1.0 500 Internal Server Error");
    } else {
      return array(
        "people" => $select->fetchAll(PDO::FETCH_ASSOC),
        "count" => $count,
        "offset" => $offset
      );
    }
  }

  function getPeopleByNameAndGroup($data, $offset)
  {
    global $DSN, $DB_USER, $DB_PASS, $LIMIT;
    $adapter = new PDO($DSN, $DB_USER, $DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")) or die(false);

    $queryExtended = explode(" ", $data);
    $amountQueryParts = count($queryExtended);

    if ($amountQueryParts == 1) {
      $select = $adapter->prepare("SELECT `prename`, `lastname`, `email`, `id`, `department` FROM `people` WHERE `lastname` LIKE :name OR `prename` LIKE :name OR SUBSTRING_INDEX(SUBSTRING_INDEX(`email`,'@',1), '.',-1) LIKE :name ORDER BY `lastname` ASC LIMIT :limit OFFSET :offset");
      $select->bindParam(':name', htmlentities("%" . $data . "%"), PDO::PARAM_STR);
      $select->bindParam(':limit', $LIMIT, PDO::PARAM_INT);
      $select->bindParam(':offset', $offset, PDO::PARAM_INT);
      $result = $select->execute();

      $selectCount = $adapter->prepare("SELECT count(*) AS count FROM `people` WHERE `lastname` LIKE :name OR `prename` LIKE :name OR SUBSTRING_INDEX(SUBSTRING_INDEX(`email`,'@',1), '.',-1) LIKE :name");
      $selectCount->bindParam(':name', htmlentities("%" . $data . "%"), PDO::PARAM_STR);
      $selectCount->execute();
    } else if ($amountQueryParts == 2) {
      $select = $adapter->prepare("SELECT `prename`, `lastname`, `email`, `id`, `department` AS url FROM `people` WHERE `prename` LIKE :firstInput AND `lastname` LIKE :secondInput OR `prename` LIKE :secondInput AND `lastname` LIKE :firstInput OR `prename` LIKE :bothInputs  ORDER BY `lastname` ASC LIMIT :limit OFFSET :offset");

      $select->bindParam(':firstInput', htmlentities("%" . $queryExtended[0] . "%"), PDO::PARAM_STR);
      $select->bindParam(':secondInput', htmlentities("%" . $queryExtended[1] . "%"), PDO::PARAM_STR);
      $select->bindParam(':bothInputs', htmlentities("%" . $queryExtended[0] . " " . $queryExtended[1] . "%"), PDO::PARAM_STR);
      $select->bindParam(':limit', $LIMIT, PDO::PARAM_INT);
      $select->bindParam(':offset', $offset, PDO::PARAM_INT);

      $result = $select->execute();

      $selectCount = $adapter->prepare("SELECT count(*) AS count FROM `people` WHERE `prename` LIKE :firstInput AND `lastname` LIKE :secondInput OR `prename` LIKE :secondInput AND `lastname` LIKE :firstInput OR `prename` LIKE :bothInputs");
      $selectCount->bindParam(':firstInput', htmlentities("%" . $queryExtended[0] . "%"), PDO::PARAM_STR);
      $selectCount->bindParam(':secondInput', htmlentities("%" . $queryExtended[1] . "%"), PDO::PARAM_STR);
      $selectCount->bindParam(':bothInputs', htmlentities("%" . $queryExtended[0] . " " . $queryExtended[1] . "%"), PDO::PARAM_STR);
      $selectCount->execute();

    } else if ($amountQueryParts == 3) {
      $select = $adapter->prepare("SELECT `prename`, `lastname`, `email`, `id`, `department` AS url FROM `people` WHERE `prename` LIKE :firstPossibilityPrename AND `lastname` LIKE :firstPossibilityLastname OR `prename` LIKE :secondPossibilityPrename AND `lastname` LIKE :secondPossibilityLastname ORDER BY `lastname` ASC LIMIT :limit OFFSET :offset");

      $firstInput = $queryExtended[0];
      $secondInput = $queryExtended[1];
      $thirdInput = $queryExtended[2];

      $select->bindParam(':firstPossibilityPrename', htmlentities("%" . $firstInput . " " . $secondInput . "%"), PDO::PARAM_STR);
      $select->bindParam(':firstPossibilityLastname', htmlentities("%" . $thirdInput . "%"), PDO::PARAM_STR);
      $select->bindParam(':secondPossibilityLastname', htmlentities("%" . $thirdInput . "%"), PDO::PARAM_STR);
      $select->bindParam(':secondPossibilityPrename', htmlentities("%" . $secondInput . " " . $thirdInput . "%"), PDO::PARAM_STR);
      $select->bindParam(':limit', $LIMIT, PDO::PARAM_INT);
      $select->bindParam(':offset', $offset, PDO::PARAM_INT);

      $result = $select->execute();

      $selectCount = $adapter->prepare("SELECT count(*) AS count FROM `people` WHERE `prename` LIKE :firstPossibilityPrename AND `lastname` LIKE :firstPossibilityLastname OR `prename` LIKE :secondPossibilityPrename AND `lastname` LIKE :secondPossibilityLastname");
      $selectCount->bindParam(':firstPossibilityPrename', htmlentities("%" . $firstInput . " " . $secondInput . "%"), PDO::PARAM_STR);
      $selectCount->bindParam(':firstPossibilityLastname', htmlentities("%" . $thirdInput . "%"), PDO::PARAM_STR);
      $selectCount->bindParam(':secondPossibilityLastname', htmlentities("%" . $thirdInput . "%"), PDO::PARAM_STR);
      $selectCount->bindParam(':secondPossibilityPrename', htmlentities("%" . $secondInput . " " . $thirdInput . "%"), PDO::PARAM_STR);
      $selectCount->execute();
    }

    if (!$result) {
      header("HTTP/1.0 500 Internal Server Error");
    } else {
      return array(
        "people" => $select->fetchAll(PDO::FETCH_ASSOC),
        "count" => $selectCount->fetch(PDO::FETCH_ASSOC)["count"],
        "offset" => $offset
      );
    }
  }


  /** Error-Analysis
   * $adapter->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
   * prepare
   * execute
   * print_r($insert->errorInfo());
   */

  /**
   * Show all study groups
   * SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(`email`,'@',1), '.',-1) AS group FROM test WHERE `email` != "" AND `email` LIKE '%20%' GROUP BY SUBSTRING_INDEX(SUBSTRING_INDEX(`email`,'@',1), '.',-1)
   */