<?php
    $host = "localhost"; //or localhost for some reason localhost didn't work for me?!
    $database = "ta_scheduler";
    $user = "ta_scheduler_app";
    $password = "$3kuDoG";
    $port = "3306";  //this should probably be 3306 (mysql default) for most of you

    $connection = new mysqli($host, $user, $password, $database, $port);
    if ($connection->connect_errno) {
			echo "Failed to connect to MySQL: (" . $connection->connect_errno . ") " . $connection->connect_error;
    }

    function manager_prefs() {
        global $connection;
        $queryStr = "SELECT  `person`.name, sunday_start, sunday_end, monday_start, monday_end, tuesday_start, tuesday_end, wednesday_start, wednesday_start, wednesday_end, thursday_start, thursday_end FROM `preferences`
        JOIN `person` ON person_id = `person`.id";
        $data = $connection->query($queryStr);
        return $data;
    }

    function survey_resp_today() {
        global $connection;
        $queryStr = "SELECT code, professor, text FROM `feedback` WHERE datetime = CURRENT_DATE()";
        $data = $connection->query($queryStr);
        return $data;
    }

    function survey_resp_date($date) {
        global $connection;
        $queryStr = "SELECT code, professor, text FROM `feedback` WHERE datetime = (?)";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    function get_courses() {
        global $connection;
        $queryStr = "SELECT code FROM `feedback`";
        $stmt = $connection->prepare($queryStr);
        $stmt->execute();
        return $stmt->get_result();
    }

        
    function get_professors() {
        global $connection;
        $queryStr = "SELECT professor FROM `feedback`";
        $stmt = $connection->prepare($queryStr);
        $stmt->execute();
        return $stmt->get_result();
    }
    

?>