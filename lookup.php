<?php

include("db.php");
$ticker = strtoupper($_POST['ticker']);

if($ticker != "") {
    $query = "SELECT companyname FROM companies WHERE ticker = '$ticker'";
    $result = mysqli_query($mysql, $query);
    $num_rows = mysqli_num_rows($result);
    if($num_rows == 0) {
        echo "ERROR";
    }
    else {
        $ticker = strtoupper($ticker);
        $row = mysqli_fetch_array($result);
        echo $row['companyname']." ($ticker)";
    }
}
else {
    echo "ERROR";
}
?>