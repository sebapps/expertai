<?php
set_time_limit(0);

// MySQL
$db_server = "localhost";
$db_username = "DB_USER";
$db_password = "DB_PASSWORD";
$db_database = "DB";
$mysql = mysqli_connect($db_server, $db_username, $db_password, $db_database);

// Read the token from the file. If it does not exist, get it
$token = "";
if(file_exists("token.txt"))
    $token = file_get_contents("token.txt");
else {
    $result = shell_exec("curl -X POST https://developer.expert.ai/oauth2/token -H 'Content-Type: application/json; charset=utf-8' -d '{\"username\": \"USERNAME\",\"password\": \"PASSWORD\"}'");
    $token = trim($result);
    $file = fopen("token.txt", "w");
    fwrite($file, $token);
    fclose($file);
}

// Go through the first 10 0.0 items
$query = "SELECT seekingalpha_id, article FROM seekingalpha WHERE scored = 'n' ORDER BY seekingalpha_id LIMIT 0,50";

$result = mysqli_query($mysql, $query);
$num_rows = mysqli_num_rows($result);
$done = false;

if($num_rows == 0) {
    $done = true;
    echo "ALL DONE!";
    exit;
}

while($row = mysqli_fetch_array($result)) {
    $seekingalpha_id = $row['seekingalpha_id'];
    $article = $row['article'];
    
    // Get the sentiment score
    $result_curl = shell_exec("curl -X POST https://nlapi.expert.ai/v2/analyze/standard/en/sentiment -H 'Authorization: Bearer $token' -H 'Content-Type: application/json; charset=utf-8' -d '{\"document\": {\"text\": \"$article\"}}'");
    
    $json = json_decode($result_curl, true);
    $sentiment = $json['data']['sentiment']['overall'];
    
    $query = "UPDATE seekingalpha SET sentiment_score = '$sentiment', scored = 'y' WHERE seekingalpha_id = '$seekingalpha_id'";
    $result_update = mysqli_query($mysql, $query);
}
?>