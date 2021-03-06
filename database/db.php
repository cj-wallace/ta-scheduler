
<?php
    $host = "localhost"; //or localhost for some reason localhost didn't work for me?!
    $database = "ta_scheduler";
    $user = "ta_scheduler_app";
    $password = "$3kuDoG";
    $port = "3306";  //this should probably be 3306 (mysql default) for most of you
    $connection = new mysqli($host, $user, $password, $database, $port);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    if ($connection->connect_errno) {
			echo "Failed to connect to MySQL: (" . $connection->connect_errno . ") " . $connection->connect_error;
    }

    function insert_queue_data($ques) {
        global $connection;
        $queryStr = "UPDATE `queue` SET `queue` = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $ques);
        $stmt->execute();
    }
    function get_queue_data() {
        global $connection;
        $queryStr = "SELECT * FROM `queue`";
        $data = $connection->query($queryStr);
        return $data;
    }
    function insert_feedback($class, $prof, $date, $text) {
        global $connection;
        $queryStr = "INSERT INTO `feedback`(code, professor, text, datetime) VALUES (?, ?, ?, ?)";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("ssss", $class, $prof, $text, $date);
        $stmt->execute();
        return $stmt->error;
    }
    function manager_prefs() {
        global $connection;
        $queryStr = "SELECT  `person`.name, sunday_start, sunday_end, monday_start, monday_end, tuesday_start, tuesday_end, wednesday_start, wednesday_start, wednesday_end, thursday_start, thursday_end, late_shifts FROM `preferences`
        JOIN `person` ON person_id = `person`.id";
        $data = $connection->query($queryStr);
        return $data;
    }
    function survey_resp_today() {
        global $connection;
        $queryStr = "SELECT code, professor, text FROM `feedback` WHERE datetime >= CURRENT_DATE() AND datetime <= CURRENT_DATE() + 1";
        $data = $connection->query($queryStr);
        return $data;
    }
    function survey_resp_date($date) {
        global $connection;
        //must change to do math
        $date2 = DateTime::createFromFormat('Y-m-d', $date);
        date_add($date2, date_interval_create_from_date_string('1 days'));
        //change back
        $date2 = $date2->format("Y-m-d");
        $queryStr = "SELECT code, professor, text FROM `feedback` WHERE datetime >= (?) AND datetime <= (?)";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("ss", $date, $date2);
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

    function get_incoming_requests() {
        global $connection;
        $queryStr = "SELECT * FROM `shift_request` where approved = false  AND picker IS NULL";
        $stmt = $connection->prepare($queryStr);
        $stmt->execute();
        return $stmt->get_result();
    }

    function get_unapproved_requests() {
        global $connection;
        $queryStr = "SELECT * FROM `shift_request` where approved = false  AND picker IS NOT NULL";
        $stmt = $connection->prepare($queryStr);
        $stmt->execute();
        return $stmt->get_result();
    }

    function get_outgoing_requests($id) {
        global $connection;
        $queryStr = "SELECT * FROM `shift_request` where dropper = ? AND approved = false";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function get_next_shift($id) {
        global $connection;
        $queryStr = "SELECT * FROM `shift` where id = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result();
    }
    

    function get_name($id) {
        global $connection;
        $queryStr = "SELECT name FROM `person` where id = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function get_shift($id) {
        global $connection;
        $queryStr = "SELECT * FROM `shift` where id = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function get_role($id) {
        global $connection;
        $queryStr = "SELECT * FROM `person` where `username` = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function get_id($username) {
        global $connection;
        $queryStr = "SELECT id FROM `person` where `username` = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        return $stmt->get_result();
    }

    function claim_shift($shiftID) {
        global $connection;
        $id = $_SESSION['id'];
        $queryStr = "UPDATE shift_request SET `picker` = $id where id = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $shiftID);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    function check_pref() {
        global $connection;
        $id = $_SESSION['id'];
        $queryStr = "SELECT * FROM `preferences` WHERE person_id = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function insert_preferences($sun_s, $sun_e, $mon_s, $mon_e, $tue_s, $tue_e, $wed_s, $wed_e, $thur_s, $thur_e, $late_shift) {
        global $connection;
        $id = $_SESSION['id'];
        $exist = check_pref();
        if($exist-> num_rows > 0) {
            update_preferences($sun_s, $sun_e, $mon_s, $mon_e, $tue_s, $tue_e, $wed_s, $wed_e, $thur_s, $thur_e, $late_shift);
        } else {
            $queryStr = "INSERT INTO `preferences` VALUES(Default,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $connection->prepare($queryStr);
            $stmt->bind_param("issssssssssi", $id, $sun_s, $sun_e, $mon_s, $mon_e, $tue_s, $tue_e, $wed_s, $wed_e, $thur_s, $thur_e, $late_shift);
            $stmt->execute();
        }

    }

    function update_preferences($sun_s, $sun_e, $mon_s, $mon_e, $tue_s, $tue_e, $wed_s, $wed_e, $thur_s, $thur_e, $late_shift) {
        global $connection;
        $id = $_SESSION['id'];
        $queryStr = "UPDATE `preferences` SET `sunday_start` = ?, `sunday_end`=?, `monday_start`=?, `monday_end`=?, `tuesday_start`=?, `tuesday_end`=?, `wednesday_start` = ?, `wednesday_end`=?, `thursday_start`=?, `thursday_end`=? , `late_shifts` = ? WHERE person_id = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("ssssssssssii", $sun_s, $sun_e, $mon_s, $mon_e, $tue_s, $tue_e, $wed_s, $wed_e, $thur_s, $thur_e, $late_shift, $id);
        $stmt->execute();
    }

    function get_shift_request($id) {
        global $connection;
        $queryStr = "SELECT * FROM `shift_request` where id = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function delete_shift_request($id) {
        global $connection;
        $queryStr = "DELETE FROM `shift_request` where id = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function approve_shift($shiftId) {
        global $connection;
        $queryStr = "UPDATE shift_request SET `approved` = true where id = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $shiftId);
        $stmt->execute();
        return $stmt->get_result();
    }

    function swap_shift($shiftId, $personId) {
        global $connection;
        $queryStr = "UPDATE shift SET `owner` = ? where id = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("ss", $personId, $shiftId);
        $stmt->execute();
        return $stmt->get_result();
    }

    function deny_shift($shiftId) {
        global $connection;
        $queryStr = "UPDATE shift_request SET `picker` = NULL where id = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $shiftId);
        $stmt->execute();
        return $stmt->get_result();
    }

    function people_table() {
        global $connection;
        $queryStr = "SELECT `username`, `name`, `role` FROM `person`";
        $stmt = $connection->prepare($queryStr);
        $stmt->execute();
        return $stmt->get_result();
    }

    function delete_person($eid) {
        global $connection;
        $queryStr = "DELETE FROM `person` where username = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $eid);
        $stmt->execute();
        return $stmt->get_result();
    }

    function add_person($eid, $name, $role) {
        global $connection;
        $queryStr = "INSERT INTO `person` VALUES (DEFAULT, ?,?,?)";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("sss", $eid, $name, $role);
        $stmt->execute();
        return $stmt->get_result();
    }

    function get_tas_shifts($id) {
        global $connection;
        $queryStr = "SELECT * FROM `shift` where `owner` = ?";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        return $stmt->get_result();
    }

    function make_request($shiftId, $personId) {
        global $connection;
        $queryStr = "INSERT INTO `shift_request` VALUES (DEFAULT, ?, NULL, ?, NULL, 0)";
        $stmt = $connection->prepare($queryStr);
        $stmt->bind_param("ss", $personId, $shiftId);
        $stmt->execute();
        return $stmt->get_result();
    }

?>