<?php

include("db.php");
$ids = $_POST['id'];

$items = array();
if(strpos($ids, ",") !== FALSE) {
    $items = explode(",", $ids);
}
else
    $items[] = $ids;

if(sizeof($items) > 0) {
    
    $table = '<table id="sentiment-details-table"><tr><th>Date</th><th>Score</th><th>Full Text</th><th>1D</th><th>3D</th><th>5D</th><th>1D Score</th><th>3D Score</th><th>5D Score</th></tr><<TABLE>></table>';
    $row_text = "";
    
    foreach($items as $id) {
        if($id != "") {
            $query = "SELECT date(articledate) AS articledate, sentiment_score AS score, article, delta_1, delta_3, delta_5 FROM seekingalpha WHERE seekingalpha_id = '$id'";
            $result = mysqli_query($mysql, $query);
            $num_rows = mysqli_num_rows($result);
            
            if($num_rows > 0) {
                $row = mysqli_fetch_array($result);
                $score = $row['score'];
                $d1 = $row['delta_1'];
                $d3 = $row['delta_3'];
                $d5 = $row['delta_5'];
                
                if($score <= 0) {
                    $scoreclass = "red";
                    $d1class = ($d1 <= 0) ? "strong red" : "green";
                    $d3class = ($d3 <= 0) ? "strong red" : "green";
                    $d5class = ($d5 <= 0) ? "strong red" : "green";
                    $d1image = ($d1 <= 0) ? "check.png" : "redx.png";
                    $d3image = ($d3 <= 0) ? "check.png" : "redx.png";
                    $d5image = ($d5 <= 0) ? "check.png" : "redx.png";
                }
                else {
                    $scoreclass = "green";
                    $d1class = ($d1 > 0) ? "strong green" : "red";
                    $d3class = ($d3 > 0) ? "strong green" : "red";
                    $d5class = ($d5 > 0) ? "strong green" : "red";
                    $d1image = ($d1 > 0) ? "check.png" : "redx.png";
                    $d3image = ($d3 > 0) ? "check.png" : "redx.png";
                    $d5image = ($d5 > 0) ? "check.png" : "redx.png";
                }
                
                $row_text.= '<tr><td>'.$row['articledate'].'</td><td class="strong '.$scoreclass.'">'.$score.'</td><td><div class="article-td">'.$row['article'].'</div></td><td class="'.$d1class.'">'.$d1.'%</td><td class="'.$d3class.'">'.$d3.'%</td><td class="'.$d5class.'">'.$d5.'%</td><td align="center"><img src="images/'.$d1image.'" style="height:26px" /></td><td align="center"><img src="images/'.$d3image.'" style="height:26px" /></td><td align="center"><img src="images/'.$d5image.'" style="height:26px" /></td></tr>';
            }
        }
    }
    $table = str_replace("<<TABLE>>", $row_text, $table);
}

echo $table;
?>