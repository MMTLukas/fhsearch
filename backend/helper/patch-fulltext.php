<?php

require "../dao/dao.php";


$count = getPeopleCount();

for($i = 1; $i<=$count; $i++){
    echo "Processing user " . $i . "...\n";
    $human = getHumanByPK($i);
    updateHumanFullTextAttributFromPK($human);
}

echo "Finished.";