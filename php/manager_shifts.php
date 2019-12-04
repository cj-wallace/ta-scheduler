<?php
include("../database/db.php");
$requests = get_unapproved_requests();


if ($requests->num_rows > 0) {
    // output data of each row
    while ($row = $requests->fetch_assoc()) {
        echo "<tr>";
        echo "<td>";
        $nameReq = get_name($row['dropper']);
        $nameRow = $nameReq->fetch_assoc();
        echo $nameRow['name'];
        echo "</td>";
        echo "<td>";
        $date_time = new DateTime($row['datetime']);
        $formatted_date = $date_time->format('d/m/y H:i');
        echo $date_time->format('m/d/Y');
        echo "</td>";
        echo "<td>";
        $dw = date( 'l', strtotime($formatted_date));
        echo $dw;
        echo "</td>";
        echo "<td>";
        echo $date_time->format('h:i:s A');
        echo "</td>";
        echo "<td>";
        $nameReq2 = get_name($row['picker']);
        $nameRow2 = $nameReq2->fetch_assoc();
        echo $nameRow2['name'];
        echo "</td>";
        echo "<td>";
        $button = "<form action=\"/php/shift_cover.php?approve=1\" method=\"post\"> 
        <input type=\"submit\" name=\"shift\" value=\"Approve Shift ";
        $button .= $row['id'];
        $button .= "\"/>  </form>"; 
        echo $button;
        echo "</td>";
        echo "<td>";
        $button2 = "<form action=\"/php/shift_cover.php?deny=1\" method=\"post\"> 
        <input type=\"submit\" name=\"shift\" value=\"Deny Shift ";
        $button2 .= $row['id'];
        $button2 .= "\"/>  </form>"; 
        echo $button2;
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}

?>