<?php

include("db.php");
$query = "SELECT sentiment_score, delta_1, delta_3, delta_5 FROM seekingalpha ORDER BY sentiment_score";
$result = mysqli_query($mysql, $query);
$num_rows = mysqli_num_rows($result);

if($num_rows == 0) {
    echo "Could not obtain the data. Please try again.";
    exit;
}

$scores = array();
$averages = array();
$sum = 0.00;
    
while($row = mysqli_fetch_array($result)) {
    $scores[] = array($row['sentiment_score'], $row['delta_1'], $row['delta_3'], $row['delta_5']);
    $sum+= $row['sentiment_score'];
    if(sizeof($averages) == 0) {
        $averages[] = array($row['sentiment_score'], 1);
    }
    else {
        $found = false;
        for($i=0; $i<sizeof($averages); $i++) {
            if($averages[$i][0] == $row['sentiment_score']) {
                $averages[$i][1]++;
                $found = true;
                break;
            }
        }
        if(!$found)
            $averages[] = array($row['sentiment_score'], 1);
    }
}

$sort = array();
foreach ($averages as $key => $row) {
    $sort[$key]  = $row[1];
}
array_multisort($sort, SORT_DESC, $averages);

$rangelow = number_format($scores[0][0], 2);
$rangehigh = number_format($scores[(sizeof($scores)-1)][0], 2);
$mean = number_format($sum / sizeof($scores), 2);
$median = number_format($scores[(sizeof($scores)/2)][0], 2);
$variance = 0.00;
    
foreach($scores as $sentiment)
    $variance += pow(($sentiment[0] - $mean), 2);
    
$stddev = number_format((float)sqrt($variance/sizeof($scores)), 2);

$mode_table = '<table style="width:100px;border:1px solid white"><tr><th>Sentiment</th><th>Count</th></tr><<TABLE>></table>';

$mode_table_vals = "";
for($i=0; $i<5; $i++) {
    $mode_table_vals.= '<tr><td>'.$averages[$i][0].'</td><td>'.$averages[$i][1].'</td></tr>';
}
$mode_table = str_replace('<<TABLE>>', $mode_table_vals, $mode_table);

$median_table = '<table style="width:100px;border:1px solid white"><tr><td align="center">'.$median.'</td></tr></table>';

$mean_table = '<table style="width:100px;border:1px solid white"><tr><td align="center">'.$mean.'</td></tr></table>';

$stddev_table = '<table style="width:100px;border:1px solid white"><tr><td align="center">'.$stddev.'</td></tr></table>';

$range_table = '<table style="width:100px;border:1px solid white"><tr><th>Low</th><th>High</th></tr><tr><td align="center">'.$rangelow.'</td><td align="center">'.$rangehigh.'</td></tr></table>';


$query = "SELECT sentiment_score, count(sentiment_score) AS cnt FROM seekingalpha group by sentiment_score ORDER BY count(sentiment_score) DESC limit 5";
$result = mysqli_query($mysql, $query);
$num_rows = mysqli_num_rows($result);

if($num_rows>0) {
    
}
else {
    $mode_table = str_replace('<<TABLE>>', '<tr><td colspan="2">Could not find any values</td></tr>', $mode_table);
}

echo '<div style="width:620px"><div style="padding:10px;float:left"><strong>MODE</strong><br/>'.$mode_table.'</div>';
echo '<div style="padding:10px;float:left"><strong>MEDIAN</strong><br/>'.$median_table.'</div>';
echo '<div style="padding:10px;float:left"><strong>MEAN</strong><br/>'.$mean_table.'</div>';
echo '<div style="padding:10px;float:left"><strong>STD DEV</strong><br/>'.$stddev_table.'</div>';
echo '<div style="padding:10px;float:left"><strong>RANGE</strong><br/>'.$range_table.'</div>';
echo '</div>';

?>