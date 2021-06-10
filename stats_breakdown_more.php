<?php
include("db.php");

// Get the totals and sum them up in PHP, not MySQL
$query = "SELECT sentiment_score, delta_1, delta_3, delta_5 FROM seekingalpha order by sentiment_score";
$result = mysqli_query($mysql, $query);
$num_rows = mysqli_num_rows($result);

if($num_rows>0) {
    $scores = array();
    while($row = mysqli_fetch_array($result)) {
        $scores[] = array($row['sentiment_score'], $row['delta_1'], $row['delta_3'], $row['delta_5']);
    }

    // Break it down into increments of 1, from -27 to 62
    $index = -27;
    $avg_scores = array();
    $running_total_d1 = 0.00;
    $running_total_d3 = 0.00;
    $running_total_d5 = 0.00;
    $running_count = 0;
    
    while($index <= -1) {
        for($i=0; $i<sizeof($scores); $i++) {
            if(intval($scores[$i][0]) == $index) {
                $running_total_d1+= $scores[$i][1];
                $running_total_d3+= $scores[$i][2];
                $running_total_d5+= $scores[$i][3];
                $running_count++;
            }
            elseif(intval($scores[$i][0]) > $index)
                break;
        }
        
        if($running_count > 0) {
            $avg_scores[] = array($index.".99 - ".$index.".00", number_format($running_total_d1 / $running_count, 2), number_format($running_total_d3 / $running_count, 2), number_format($running_total_d5 / $running_count, 2));
        }
        
        $running_total_d1 = 0.00;
        $running_total_d3 = 0.00;
        $running_total_d5 = 0.00;
        $running_count = 0;
        $index++;
    }
    
    // Negative zeroes
    for($i=0; $i<sizeof($scores); $i++) {
        if($scores[$i][0] < 0.00 && intval($scores[$i][0]) == 0) {
            $running_total_d1+= $scores[$i][1];
            $running_total_d3+= $scores[$i][2];
            $running_total_d5+= $scores[$i][3];
            $running_count++;
        }
        elseif($scores[$i][0] > 0.00)
            break;
    }
    
    if($running_count > 0) {
        $avg_scores[] = array("-0.99 - 0.00", number_format($running_total_d1 / $running_count, 2), number_format($running_total_d3 / $running_count, 2), number_format($running_total_d5 / $running_count, 2));
    }
        
    $running_total_d1 = 0.00;
    $running_total_d3 = 0.00;
    $running_total_d5 = 0.00;
    $running_count = 0;
    
    // Zero-zero
    for($i=0; $i<sizeof($scores); $i++) {
        if($scores[$i][0] == 0.00) {
            $running_total_d1+= $scores[$i][1];
            $running_total_d3+= $scores[$i][2];
            $running_total_d5+= $scores[$i][3];
            $running_count++;
        }
        elseif($scores[$i][0] > 0.00)
            break;
    }
    
    if($running_count > 0) {
        $avg_scores[] = array("0.00", number_format($running_total_d1 / $running_count, 2), number_format($running_total_d3 / $running_count, 2), number_format($running_total_d5 / $running_count, 2));
    }
        
    $running_total_d1 = 0.00;
    $running_total_d3 = 0.00;
    $running_total_d5 = 0.00;
    $running_count = 0;

    // Positive zeroes
    for($i=0; $i<sizeof($scores); $i++) {
        if($scores[$i][0] > 0.00 && intval($scores[$i][0]) == 0) {
            $running_total_d1+= $scores[$i][1];
            $running_total_d3+= $scores[$i][2];
            $running_total_d5+= $scores[$i][3];
            $running_count++;
        }
        elseif($scores[$i][0] > 0.00)
            break;
    }
    
    if($running_count > 0) {
        $avg_scores[] = array("0.00 - 0.99", number_format($running_total_d1 / $running_count, 2), number_format($running_total_d3 / $running_count, 2), number_format($running_total_d5 / $running_count, 2));
    }
        
    $running_total_d1 = 0.00;
    $running_total_d3 = 0.00;
    $running_total_d5 = 0.00;
    $running_count = 0;
    
    $index = 1;
    while($index <= 62) {
        for($i=0; $i<sizeof($scores); $i++) {
            if(intval($scores[$i][0]) == $index) {
                $running_total_d1+= $scores[$i][1];
                $running_total_d3+= $scores[$i][2];
                $running_total_d5+= $scores[$i][3];
                $running_count++;
            }
            elseif(intval($scores[$i][0]) > $index)
                break;
        }
        
        if($running_count > 0) {
            $avg_scores[] = array($index.".00 - ".$index.".99", number_format($running_total_d1 / $running_count, 2), number_format($running_total_d3 / $running_count, 2), number_format($running_total_d5 / $running_count, 2));
        }
        
        $running_total_d1 = 0.00;
        $running_total_d3 = 0.00;
        $running_total_d5 = 0.00;
        $running_count = 0;
        $index++;
    }
    
    $neg_table = '<table style="border:1px solid white;width:300px"><tr><th>Range</th><th>1 Day Avg</th><th>3 Day Avg</th><th>5 Day Avg</th></tr>';
    
    $pos_table = '<table style="border:1px solid white;width:300px"><tr><th>Range</th><th>1 Day Avg</th><th>3 Day Avg</th><th>5 Day Avg</th></tr>';
   
    foreach($avg_scores as $avg) {
        if(substr_count($avg[0], "-") > 1) {
            $neg_table.= '<tr><td class="red">'.$avg[0].'</td><td';
            if($avg[1] <= 0.00)
                $neg_table.= ' class="strong red"';
            $neg_table.= '>'.$avg[1].'%</td><td';
            if($avg[2] <= 0.00)
                $neg_table.= ' class="strong red"';
            $neg_table.= '>'.$avg[2].'%</td><td';
            if($avg[3] <= 0.00)
                $neg_table.= ' class="strong red"';
            $neg_table.= '>'.$avg[3].'%</td></tr>';
        }
        else {
            $pos_table.= '<tr><td class="green">'.$avg[0].'</td><td';
            if($avg[1] > 0.00)
                $pos_table.= ' class="strong green"';
            $pos_table.= '>'.$avg[1].'%</td><td';
            if($avg[2] > 0.00)
                $pos_table.= ' class="strong green"';
            $pos_table.= '>'.$avg[2].'%</td><td';
            if($avg[3] > 0.00)
                $pos_table.= ' class="strong green"';
            $pos_table.= '>'.$avg[3].'%</td></tr>';
        }
    }
    $neg_table.= '</table>';
    $pos_table.= '</table>';
   
    echo '<div style="width:640px"><div style="float:left;width:300px;padding:10px"><strong><span class="strong red">NEGATIVE SENTIMENT</span><br/>';
    
    echo $neg_table;
    echo '</div>';
    echo '<div style="float:right;width:300px;padding:10px"><strong><span class="strong green">POSITIVE SENTIMENT</span><br/>';
    
    echo $pos_table;
    echo '</div>';
    echo '</div>';
    echo '<div style="clear:both"> </div>';
    echo '<div style="padding-top:12px" id="overall">';
    echo '<span class="strong">OVERALL RESULTS</span><br/>';
    
    $neg_table = '<table style="border:1px solid white;width:500px"><tr><th>Category</th><th>Overall</th><th>1 Day</th><th>3 Day</th><th>5 Day</th></tr>';
    
    $pos_table = '<table style="border:1px solid white;width:500px"><tr><th>Range</th><th>Overall</th><th>1 Day</th><th>3 Day</th><th>5 Day</th></tr>';
    
    // Go through each negative value and sum the correct predictions
    $delta1correct = 0;
    $delta3correct = 0;
    $delta5correct = 0;
    $delta1 = 0.00;
    $delta3 = 0.00;
    $delta5 = 0.00;
    $count = 0;
    
    foreach($avg_scores as $avg) {
        if(substr_count($avg[0], "-") > 1) {
            if($avg[1] < 0.00)
                $delta1correct++;
            if($avg[2] < 0.00)
                $delta3correct++;
            if($avg[3] < 0.00)
                $delta5correct++;
            $delta1+= $avg[1];
            $delta3+= $avg[2];
            $delta5+= $avg[3];
            $count++;
        }
    }
    
    $neg_table.= '<tr><td>Correct Predictions</td>';
    $neg_table.= '<td align="center">'.(number_format(($delta1correct + $delta3correct + $delta5correct) / ($count * 3), 2) * 100).'%</td>';
    $neg_table.= '<td align="center">'.(number_format(($delta1correct / $count), 2) * 100).'%</td>';
    $neg_table.= '<td align="center">'.(number_format(($delta3correct / $count), 2) * 100).'%</td>';
    $neg_table.= '<td align="center">'.(number_format(($delta5correct / $count), 2) * 100).'%</td>';
    $neg_table.= '<tr><td>Average Change</td>';
    $neg_table.= '<td align="center">'.number_format(($delta1 + $delta3 + $delta5) / ($count * 3), 2).'%</td>';
    $neg_table.= '<td align="center">'.number_format($delta1 / $count, 2).'%</td>';
    $neg_table.= '<td align="center">'.number_format($delta3 / $count, 2).'%</td>';
    $neg_table.= '<td align="center">'.number_format($delta5 / $count, 2).'%</td></tr>';
    
    // Go through each positive value and sum the correct predictions
    $delta1correct = 0;
    $delta3correct = 0;
    $delta5correct = 0;
    $delta1 = 0.00;
    $delta3 = 0.00;
    $delta5 = 0.00;
    $count = 0;
    
    foreach($avg_scores as $avg) {
        if(substr_count($avg[0], "-") == 1) {
            if($avg[1] > 0.00)
                $delta1correct++;
            if($avg[2] > 0.00)
                $delta3correct++;
            if($avg[3] > 0.00)
                $delta5correct++;
            $delta1+= $avg[1];
            $delta3+= $avg[2];
            $delta5+= $avg[3];
            $count++;
        }
    }
    
    $pos_table.= '<tr><td>Correct Predictions</td>';
    $pos_table.= '<td align="center">'.(number_format(($delta1correct + $delta3correct + $delta5correct) / ($count * 3), 2) * 100).'%</td>';
    $pos_table.= '<td align="center">'.(number_format(($delta1correct / $count), 2) * 100).'%</td>';
    $pos_table.= '<td align="center">'.(number_format(($delta3correct / $count), 2) * 100).'%</td>';
    $pos_table.= '<td align="center">'.(number_format(($delta5correct / $count), 2) * 100).'%</td>';
    $pos_table.= '<tr><td>Average Change</td>';
    $pos_table.= '<td align="center">'.number_format(($delta1 + $delta3 + $delta5) / ($count * 3), 2).'%</td>';
    $pos_table.= '<td align="center">'.number_format($delta1 / $count, 2).'%</td>';
    $pos_table.= '<td align="center">'.number_format($delta3 / $count, 2).'%</td>';
    $pos_table.= '<td align="center">'.number_format($delta5 / $count, 2).'%</td></tr>';
    
    $neg_table.= '</table>';
    $pos_table.= '</table>';
    
    echo '<div style="width:640px"><span class="strong red">NEGATIVE SENTIMENT</span><br/>';
    
    echo $neg_table;
    echo '<br/>';
    
    echo '<span class="strong green">POSITIVE SENTIMENT</span><br/>';
    echo $pos_table;
    
    echo '</div>';
    echo '</div>';
}
else
    echo "Could not retrive the data. Please try again.";
?>