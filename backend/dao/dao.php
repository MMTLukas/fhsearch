<?php
/**
 * User: Enthusiasmus
 * Date: 01.08.14
 * Time: 23:11
 */

include "../config.php";

function createHuman(Human $human)
{
    global $DSN, $DB_USER, $DB_PASS;
    $adapter = new PDO($DSN, $DB_USER, $DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")) or die(false);

    //Check if ID already exists, so we have only the make a update
    //Otherwise we want to insert the new human
    $select = $adapter->prepare("SELECT id FROM people WHERE id = :id");
    $result = $select->execute(array(
        ":id" => htmlspecialchars($human->getId())
    ));
    $result = $select->fetch(PDO::FETCH_ASSOC);

    if ($result["id"]) {
        return updateHumanOverview($human);
    } else {
        return insertHuman($human);
    }
}

function insertHuman(Human $human)
{
    global $DSN, $DB_USER, $DB_PASS;
    $adapter = new PDO($DSN, $DB_USER, $DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")) or die(false);

    $insert = $adapter->prepare("INSERT INTO people (prename, lastname, department, type, id) VALUES (:prename, :lastname, :department, :type, :id)");
    $result = $insert->execute(array(
        ":prename" => htmlspecialchars($human->getPrename()),
        ":lastname" => htmlspecialchars($human->getLastname()),
        ":department" => htmlspecialchars($human->getDepartment()),
        ":type" => htmlspecialchars($human->getType()),
        ":id" => htmlspecialchars($human->getId())
    ));

    if (!$result) {
        header("HTTP/1.0 500 Internal Server Error");
    } else {
        return $adapter->lastInsertId();
    }
}

function updateHumanOverview(Human $human)
{
    global $DSN, $DB_USER, $DB_PASS;
    $adapter = new PDO($DSN, $DB_USER, $DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")) or die(false);

    $update = $adapter->prepare("UPDATE people SET prename = :prename, lastname = :lastname, department = :department, type = :type WHERE id = :id");
    $result = $update->execute(array(
        ":prename" => htmlspecialchars($human->getPrename()),
        ":lastname" => htmlspecialchars($human->getLastname()),
        ":department" => htmlspecialchars($human->getDepartment()),
        ":type" => htmlspecialchars($human->getType()),
        ":id" => htmlspecialchars($human->getId())
    ));

    if (!$result) {
        header("HTTP/1.0 500 Internal Server Error");
    } else {
        return $human->getId();
    }
}

function updateHumanDetails(Human $human)
{
    global $DSN, $DB_USER, $DB_PASS;
    $adapter = new PDO($DSN, $DB_USER, $DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")) or die(false);

    $pictureURLFHS = htmlspecialchars($human->getPictureUrlFhs());
    $pictureURLFHS = $pictureURLFHS == "" ? null : $pictureURLFHS;

    $email = htmlspecialchars($human->getEmail());
    $email = $email == "" ? null : $email;

    $id = htmlspecialchars($human->getId());
    $id = $id == "" ? null : $id;

    $phone = htmlspecialchars($human->getPhone());
    $phone = $phone == "" ? null : $phone;

    $mobile = htmlspecialchars($human->getMobile());
    $mobile = $mobile == "" ? null : $mobile;

    $room = htmlspecialchars($human->getRoom());
    $room = $room == "" ? null : $room;

    $update = $adapter->prepare("UPDATE people SET pictureUrlFhs = :pictureUrlFhs, email = :email, mobile = :mobile, phone = :phone, room = :room WHERE id = :id");
    $result = $update->execute(array(
        ":pictureUrlFhs" => $pictureURLFHS,
        ":email" => $email,
        ":id" => $id,
        ":phone" => $phone,
        ":mobile" => $mobile,
        ":room" => $room
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

    $select = $adapter->prepare("SELECT `prename`, `lastname`, `email`, `id`, `type`, `department`, `mobile`, `phone`, `room` FROM `people` WHERE `id` LIKE :fhsId ORDER BY `id` ASC LIMIT :limit OFFSET :offset");
    $select->bindParam(':fhsId', htmlentities($fhsId . "%"), PDO::PARAM_STR);
    $select->bindParam(':limit', $LIMIT, PDO::PARAM_INT);
    $select->bindParam(':offset', intval(htmlspecialchars($offset)), PDO::PARAM_INT);
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
    $adapter = new PDO($DSN, $DB_USER, $DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')) or die(false);

    $queryExtended = explode(" ", $data);
    $amountQueryParts = count($queryExtended);

    if ($amountQueryParts == 1) {

        $adapter->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $select = $adapter->prepare("SELECT `prename`, `lastname`, `email`, `id`, `type`, `department`, `mobile`, `phone`, `room` FROM `people` WHERE `lastname` LIKE :name OR `prename` LIKE :name OR SUBSTRING_INDEX(SUBSTRING_INDEX(`email`,'@',1), '.',-1) LIKE :name ORDER BY `lastname` ASC LIMIT :limit OFFSET :offset");
        $select->bindParam(':name', htmlspecialchars("%" . $data . "%", ENT_QUOTES, "UTF-8"));
        $select->bindParam(':limit', $LIMIT, PDO::PARAM_INT);
        $select->bindParam(':offset', intval(htmlspecialchars($offset)), PDO::PARAM_INT);
        $result = $select->execute();

        $selectCount = $adapter->prepare("SELECT count(*) AS count FROM `people` WHERE `lastname` LIKE :name OR `prename` LIKE :name OR SUBSTRING_INDEX(SUBSTRING_INDEX(`email`,'@',1), '.',-1) LIKE :name");
        $selectCount->bindParam(':name', htmlspecialchars("%" . $data . "%", ENT_QUOTES, "UTF-8"));
        $selectCount->execute();

    } else if ($amountQueryParts == 2) {
        $select = $adapter->prepare("SELECT `prename`, `lastname`, `email`, `id`, `type`, `department`, `mobile`, `phone`, `room` FROM `people` WHERE `prename` LIKE :firstInput AND `lastname` LIKE :secondInput OR `prename` LIKE :secondInput AND `lastname` LIKE :firstInput OR `prename` LIKE :bothInputs  ORDER BY `lastname` ASC LIMIT :limit OFFSET :offset");

        $select->bindParam(':firstInput', htmlspecialchars("%" . $queryExtended[0] . "%", ENT_QUOTES, "UTF-8"));
        $select->bindParam(':secondInput', htmlspecialchars("%" . $queryExtended[1] . "%", ENT_QUOTES, "UTF-8"));
        $select->bindParam(':bothInputs', htmlspecialchars("%" . $queryExtended[0] . " " . $queryExtended[1] . "%", ENT_QUOTES, "UTF-8"));
        $select->bindParam(':limit', $LIMIT, PDO::PARAM_INT);
        $select->bindParam(':offset', intval(htmlspecialchars($offset)), PDO::PARAM_INT);

        $result = $select->execute();

        $selectCount = $adapter->prepare("SELECT count(*) AS count FROM `people` WHERE `prename` LIKE :firstInput AND `lastname` LIKE :secondInput OR `prename` LIKE :secondInput AND `lastname` LIKE :firstInput OR `prename` LIKE :bothInputs");
        $selectCount->bindParam(':firstInput', htmlspecialchars("%" . $queryExtended[0] . "%", ENT_QUOTES, "UTF-8"));
        $selectCount->bindParam(':secondInput', htmlspecialchars("%" . $queryExtended[1] . "%", ENT_QUOTES, "UTF-8"));
        $selectCount->bindParam(':bothInputs', htmlspecialchars("%" . $queryExtended[0] . " " . $queryExtended[1] . "%", ENT_QUOTES, "UTF-8"));
        $selectCount->execute();

    } else if ($amountQueryParts == 3) {
        $select = $adapter->prepare("SELECT `prename`, `lastname`, `email`, `id`, `type`, `department`, `mobile`, `phone`, `room` FROM `people` WHERE `prename` LIKE :firstPossibilityPrename AND `lastname` LIKE :firstPossibilityLastname OR `prename` LIKE :secondPossibilityPrename AND `lastname` LIKE :secondPossibilityLastname ORDER BY `lastname` ASC LIMIT :limit OFFSET :offset");

        $firstInput = $queryExtended[0];
        $secondInput = $queryExtended[1];
        $thirdInput = $queryExtended[2];

        $select->bindParam(':firstPossibilityPrename', htmlspecialchars("%" . $firstInput . " " . $secondInput . "%", ENT_QUOTES, "UTF-8"));
        $select->bindParam(':firstPossibilityLastname', htmlspecialchars("%" . $thirdInput . "%", ENT_QUOTES, "UTF-8"));
        $select->bindParam(':secondPossibilityLastname', htmlspecialchars("%" . $thirdInput . "%", ENT_QUOTES, "UTF-8"));
        $select->bindParam(':secondPossibilityPrename', htmlspecialchars("%" . $secondInput . " " . $thirdInput . "%", ENT_QUOTES, "UTF-8"));
        $select->bindParam(':limit', $LIMIT, PDO::PARAM_INT);
        $select->bindParam(':offset', intval(htmlspecialchars($offset)), PDO::PARAM_INT);

        $result = $select->execute();

        $selectCount = $adapter->prepare("SELECT count(*) AS count FROM `people` WHERE `prename` LIKE :firstPossibilityPrename AND `lastname` LIKE :firstPossibilityLastname OR `prename` LIKE :secondPossibilityPrename AND `lastname` LIKE :secondPossibilityLastname");
        $selectCount->bindParam(':firstPossibilityPrename', htmlspecialchars("%" . $firstInput . " " . $secondInput . "%", ENT_QUOTES, "UTF-8"));
        $selectCount->bindParam(':firstPossibilityLastname', htmlspecialchars("%" . $thirdInput . "%", ENT_QUOTES, "UTF-8"));
        $selectCount->bindParam(':secondPossibilityLastname', htmlspecialchars("%" . $thirdInput . "%", ENT_QUOTES, "UTF-8"));
        $selectCount->bindParam(':secondPossibilityPrename', htmlspecialchars("%" . $secondInput . " " . $thirdInput . "%", ENT_QUOTES, "UTF-8"));
        $selectCount->execute();
    }

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
