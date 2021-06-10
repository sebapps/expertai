<?php

include("db.php");

$query = "select seekingalpha.ticker, companies.companyname from seekingalpha, companies where seekingalpha.ticker = companies.ticker group by seekingalpha.ticker order by count(seekingalpha.ticker) desc limit 15";

$result = mysqli_query($mysql, $query);
$num_rows = mysqli_num_rows($result);

if($num_rows == 0) {
    echo "ERROR";
    exit;
}
else {
    $json = '{ "data": [<<DATA>>] }';
    $vals = array();
    while($row = mysqli_fetch_array($result)) {
        $vals[] = '{ "companyname" : "'.$row['companyname'].'", "ticker" : "'.$row['ticker'].'"}';
    }
    $json = str_replace("<<DATA>>", implode(",", $vals), $json);
    echo $json;
}

?>