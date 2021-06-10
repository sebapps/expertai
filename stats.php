<!doctype html>
<html>
    <head>
        <title>Expert.ai Stock Sentiment Analysis</title>
        <link rel="stylesheet" href="css/style.css?id=<?php echo date("His"); ?>">
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/chart.min.js"></script>
    </head>
    <body>
        <div align="center">
            <div id="title"><a href="index.php">Expert.ai Stock Sentiment Analysis</a> | Stats Breakdown</div>
            <div id="instructions">
                Here are the project findings.<br/><br/>
                <div id="results">Please wait...</div>
                <div style="clear:both"> </div>
                <div id="breakdown" style="padding-top:12px;display:none;padding-bottom:16px">Please wait for the sentiment breakdown...</div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function(){
                $.post("stats_breakdown.php").done(function(data){
                    $("#results").html(data);
                    $("#breakdown").show();
                    $.post("stats_breakdown_more.php").done(function(data){
                         $("#breakdown").html(data);
                    });
                });
            })
        </script>
    </body>
</html>