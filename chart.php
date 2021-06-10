<?php

include("db.php");
$ticker = strtoupper($_POST['ticker']);

if($ticker != "") {
    $query = "SELECT * FROM stock_quotes WHERE ticker = '$ticker' ORDER BY quotedate";
    $result = mysqli_query($mysql, $query);
    $num_rows = mysqli_num_rows($result);
    if($num_rows == 0) {
        echo '{"msg" : "error"}';
    }
    else {
        $json = '{"msg" : "success", "data" : [<<DATA>>], "sentiments" : [<<SENTIMENTS>>], "sentiment_per_day" : [<<SENTIMENTS_PER_DAY>>]}';
        $values = array();
        while($row = mysqli_fetch_array($result)) {
            $quotedate = substr($row['quotedate'], 0, 4)."-".substr($row['quotedate'], 4, 2)."-".substr($row['quotedate'], 6, 2);
            $values[] = '{"date" : "'.$quotedate.'", "time" : "AM", "value" : "'.$row['quoteopen'].'"}';
            $values[] = '{"date" : "'.$quotedate.'", "time" : "PM", "value" : "'.$row['quoteclose'].'"}';
        }
        $json = str_replace("<<DATA>>", implode(",", $values), $json);
        
        $query = "SELECT *, if(weekday(date(articledate)) <= 4, concat(date(articledate), '_PM'), if(weekday(date(articledate)) = 5, concat(date(date_add(articledate, interval 2 day)), '_AM'), if(weekday(date(articledate)) = 6, concat(date(date_add(articledate, interval 1 day)), '_AM'), ''))) as class FROM seekingalpha WHERE ticker = '$ticker' ORDER BY articledate";
        $result = mysqli_query($mysql, $query);
        $num_rows = mysqli_num_rows($result);
        
        if($num_rows == 0) {
            $json = str_replace("<<SENTIMENTS>>", "", $json);
        }
        else {
            $sentiments = array();
            while($row = mysqli_fetch_array($result)) {
                $id = $row['seekingalpha_id'];
                $datetime = substr($row['articledate'], 0, 10);
                $score = $row['sentiment_score'];
                $class = $row['class'];
                $d1 = $row['delta_1'];
                $d3 = $row['delta_3'];
                $d5 = $row['delta_5'];
                
                $sentiments[] = '{"id" : '.$id.', "datetime" : "'.$datetime.'", "score" : '.$score.', "text": "'.substr($row['article'],0, 50).'", "class" : "'.$class.'", "delta1" : '.$d1.', "delta3" : '.$d3.', "delta5" : '.$d5.'}';
            }
            $json = str_replace("<<SENTIMENTS>>", implode(",", $sentiments), $json);
        }
        
        $query = "SELECT sentiment_date, SUM(day_score) as day_score FROM ( SELECT concat(date(articledate), ' PM') as sentiment_date, sum(sentiment_score) as day_score FROM seekingalpha WHERE ticker = '$ticker' AND weekday(date(articledate)) <= 4 group by date(articledate) union SELECT concat(date_add(date(articledate), interval 2 day), ' AM') as sentiment_date, sum(sentiment_score) as day_score FROM seekingalpha WHERE ticker = '$ticker' AND weekday(date(articledate)) = 5 group by date(articledate) union SELECT concat(date_add(date(articledate), interval 1 day), ' AM') as sentiment_date, sum(sentiment_score) as day_score FROM seekingalpha WHERE ticker = '$ticker' AND weekday(date(articledate)) = 6 group by date(articledate) ) as sentiment_table group by sentiment_date";
        
        $result = mysqli_query($mysql, $query);
        $num_rows = mysqli_num_rows($result);
        if($num_rows == 0) {
            $json = str_replace("<<SENTIMENTS_PER_DAY>>", "", $json);
        }
        else {
            $sentiments_per_day = array();
            while($row = mysqli_fetch_array($result)) {
                $sentiment_date = $row['sentiment_date'];
                $day_score = $row['day_score'];
                $sentiments_per_day[] = '{"date" : "'.$sentiment_date.'", "score" : '.$day_score.'}';
            }
            $json = str_replace("<<SENTIMENTS_PER_DAY>>", implode(",", $sentiments_per_day), $json);
        }
        echo $json;
    }
}
else {
    echo '{"msg" : "error"}';
}
?>