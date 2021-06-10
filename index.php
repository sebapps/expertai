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
            <div id="title">Expert.ai Stock Sentiment Analysis | <a href="stats.php">Stats Breakdown</a></div>
            <div id="instructions">
                Enter a ticker <input style="width:60px" type="text" id="ticker" name="ticker" /> or select from one of these trending tickers: <select onchange="ticker_lookup();" id="ticker_dropdown" name="ticker_dropdown">
                    <option value="">Select a ticker...</option>
                </select>
            </div>
            <div style="padding-bottom:12px"id="selected_ticker">Select a ticker to view its chart...</div>
            
            <div id="holder">
                <canvas id="chart" width="600" height="400"></canvas>
                <div id="sentiments">&nbsp</div>
                <div style="clear:both"></div>
                <div id="sentiment-details" style="display:none"></div>
            </div>
        </div>
        <script type="text/javascript">
            var chart = "";
            var ctx = document.getElementById('chart').getContext('2d');
            var data = "";
            var ticker = "";
            
            $(document).ready(function(){
                $.post("trending.php").done(function(data){
                    if(data != "ERROR") {
                        var json = JSON.parse(data);
                        $.each(json.data, function(key, value) {
                            $('#ticker_dropdown').append(new Option(value.companyname, value.ticker));
                        });
                    }
                });
                $('#ticker').keypress(function(e){
                    if(e.keyCode==13) {
                        ticker = $('#ticker').val();
                        $.post("lookup.php", {ticker: ticker}).done(function(data) {
                            if(data == "ERROR") {
                                if($("#chart").is(":visible"))
                                    $("#chart").fadeOut(400);
                                if($("#sentiments").is(":visible"))
                                    $("#sentiments").fadeOut(400);
                                if($("#sentiment-details").is(":visible"))
                                    $("#sentiment-details").fadeOut(400);
                                $("#selected_ticker").html("The company could not be found.");
                            }
                            else {
                                $("#selected_ticker").html(data);
                                // Lookup the sentiments and charts
                                get_chart();
                            }
                        })
                    }
                });
            });
            function ticker_lookup() {
                ticker = $("#ticker_dropdown").val();
                if(ticker == "") {
                    if($("#chart").is(":visible"))
                        $("#chart").fadeOut(400);
                    if($("#sentiments").is(":visible"))
                        $("#sentiments").fadeOut(400);
                    if($("#sentiment-details").is(":visible"))
                        $("#sentiment-details").fadeOut(400);
                    $("#selected_ticker").html("No company currently selected.");
                }
                else {
                    $("#selected_ticker").html($("#ticker_dropdown option:selected").text());
                    // Lookup the sentiments and charts
                    get_chart();
                }
            }
            function get_chart() {
                if(ticker != "") {
                    $.post("chart.php", {ticker: ticker}).done(function(data) {
                        var json = JSON.parse(data);
                        var labels = [];
                        var values = [];
                        var sentiments = [];
                        var sentiment_per_day = [];
                        
                        var bordercolor = [];
                        var backgroundcolor = [];
                        
                        // SUCCESS
                        if(json.msg == "success") {
                            // Hide the sentiments...
                            if($("#sentiment-details").is(":visible"))
                                $("#sentiment-details").fadeOut(400);
                        
                            // Parse the JSON
                            $.each(json.data, function(key, value) {
                                labels.push(value.date + " " + value.time);
                                values.push(value.value);
                                bordercolor.push('#0047AB');
                                backgroundcolor.push('#0096FF');
                            });
                            $.each(json.sentiments, function(key, value) {
                                sentiments.push(value);
                            });
                            $.each(json.sentiment_per_day, function(key, value) {
                                sentiment_per_day.push(value);
                            });
                            // Format the data
                            data = {
                                labels: labels,
                                datasets: [{
                                    label: 'Stock Price',
                                    data: values,
                                    borderColor: '#0047AB',
                                    backgroundColor: '#0096FF',
                                    pointBackgroundColor: backgroundcolor,
                                    pointBorderColor: bordercolor
                                }]
                            };
                            // Add the sentiments
                            for(var i=0; i<labels.length; i++) {
                                for(var j=0; j<sentiment_per_day.length; j++) {
                                    if(sentiment_per_day[j].date == labels[i]) {
                                        
                                        // NEGATIVE
                                        if(sentiment_per_day[j].score <= 0) {
                                            data.datasets[0].pointBackgroundColor[i] = '#DC143C';
                                            data.datasets[0].pointBorderColor[i] = '#8B0000';
                                        }
                                        // POSITIVE
                                        else {
                                            data.datasets[0].pointBackgroundColor[i] = '#00FF00';
                                            data.datasets[0].pointBorderColor[i] = '#006400';
                                        }
                                    }
                                }
                            }
                            
                            // Create the chart
                            if(chart == "") {
                                if(!$("#chart").is(":visible"))
                                    $("#chart").fadeIn(400);
                                
                                chart = new Chart(ctx, {
                                    type: 'line',
                                    data: data,
                                    spanGaps: true,
                                    options: {
                                        onClick: (e) => {
                                            var ids = [];
                                            $(".highlight").each(function(){
                                                ids.push($(this).attr("id"));
                                            });
                                            load_sentiments(ids);
                                        },
                                        onHover: (e) => {
                                            if(e.chart.tooltip.opacity > 0)
                                                highlight_row(e.chart.tooltip.title.toString());
                                            else if(e.chart.tooltip.opacity == 0)
                                            dehighlight_rows();
                                        }
                                    }
                                });
                            }
                            else {
                                if(!$("#chart").is(":visible"))
                                    $("#chart").fadeIn(400);
                                    
                                chart.data = data;
                                chart.update();
                            }
                            // Create the sentiments table
                            var innerTableHtml = '';
                            var tablehtml = '<table id="sentiments_table"><tr><th width="70">Date</th><th>Score</th><th>Blurb</th><th>1D</th><th>3D</th><th>5D</th></tr><<TABLE>></table>';
                            for(var i=0; i<sentiments.length; i++) {
                                var rowclass = "sentiment_row";
                                var scoreclass = "";
                                var d1class = "";
                                var d3class = "";
                                var d5class = "";
                                
                                if(sentiments[i].score <= 0) {
                                    rowclass += " negative ";
                                    scoreclass = "red";
                                    
                                    if(sentiments[i].delta1 <= 0) {
                                        d1class = "strong red";
                                    }
                                    else
                                        d1class = "green";
                                    
                                    if(sentiments[i].delta3 <= 0) {
                                        d3class = "strong red";
                                    }
                                    else
                                        d3class = "green";
                                    
                                    if(sentiments[i].delta5 <= 0) {
                                        d5class = "strong red";
                                    }
                                    else
                                        d5class = "green";
                                }
                                else {
                                    rowclass += " positive ";
                                    scoreclass = "green";
                                    
                                    if(sentiments[i].delta1 > 0) {
                                        d1class = "strong green";
                                    }
                                    else
                                        d1class = "red";
                                    
                                    if(sentiments[i].delta3 > 0) {
                                        d3class = "strong green";
                                    }
                                    else
                                        d3class = "red";
                                    
                                    if(sentiments[i].delta5 > 0) {
                                        d5class = "strong green";
                                    }
                                    else
                                        d5class = "red";
                                }
                                innerTableHtml+= '<tr id="'+sentiments[i].id+'" onclick="load_sentiment('+sentiments[i].id+')" onmouseover="highlight($(this));" onmouseout="dehighlight($(this));" class="'+rowclass+sentiments[i].class+'"><td>'+sentiments[i].datetime+'</td><td class="'+scoreclass+'"><strong>'+sentiments[i].score.toFixed(2)+'</strong></td><td>'+sentiments[i].text+'...</td><td class="'+d1class+'">'+sentiments[i].delta1+'%</td><td class="'+d3class+'">'+sentiments[i].delta3+'%</td><td class="'+d5class+'">'+sentiments[i].delta5+'%</td></tr>';
                            }
                            tablehtml = tablehtml.replace('<<TABLE>>', innerTableHtml);
                            
                            if(!$("#sentiments").is(":visible"))
                                $("#sentiments").fadeIn(400);
                                    
                            $("#sentiments").html(tablehtml);
                        }
                        // ERROR
                        else {
                            if($("#chart").is(":visible"))
                                    $("#chart").fadeOut(400);
                            if($("#sentiments").is(":visible"))
                                    $("#sentiments").fadeOut(400);
                            if($("#sentiment-details").is(":visible"))
                                $("#sentiment-details").fadeOut(400);
                            $("#selected_ticker").html("An error has occurred.");
                        }
                    });
                }
            }
            function highlight_row(row) {
                row = row.replace(" ", "_");
                var scrolled = false;
                $("."+row).each(function() {
                    if($(this) != null) {
                        if(!$(this).hasClass("highlight")) {
                            if($(this).hasClass("positive"))
                                $(this).addClass("highlight highlight_positive");
                            else
                                $(this).addClass("highlight highlight_negative");
                            
                            if(!scrolled) {
                                scrolled = true;
                                if(!checkInView($(this), false))
                                    $("#sentiments").scrollTop($(this).offset().top - $("#sentiments").offset().top + $("#sentiments").scrollTop() - 22);
                            }
                        }
                    }
                });
            }
            function dehighlight_rows() {
                if($(".sentiment_row").hasClass("highlight")) {
                    $(".sentiment_row").removeClass("highlight");
                    $(".sentiment_row").removeClass("highlight_positive");
                    $(".sentiment_row").removeClass("highlight_negative");
                }
            }
            function checkInView(elem, partial) {
                var container = $("#sentiments");
                var contHeight = container.height();
                var contTop = container.scrollTop();
                var contBottom = contTop + contHeight ;

                var elemTop = $(elem).offset().top - container.offset().top;
                var elemBottom = elemTop + $(elem).height();

                var isTotal = (elemTop >= 0 && elemBottom <=contHeight);
                var isPart = ((elemTop < 0 && elemBottom > 0 ) || (elemTop > 0 && elemTop <= container.height())) && partial ;

                return  isTotal  || isPart ;
            }
            function highlight(r) {
                if(!r.hasClass("highlight")) {
                    if(r.hasClass("positive"))
                        r.addClass("highlight highlight_positive");
                    else
                        r.addClass("highlight highlight_negative");
                                
                }
            }
            function dehighlight(r) {
                if(r.hasClass("highlight")) {
                    r.removeClass("highlight");
                    r.removeClass("highlight_positive");
                    r.removeClass("highlight_negative");
                }
            }
            function load_sentiment(id) {
                $.post("details.php", {id: id}).done(function(data){
                    $("#sentiment-details").html(data);
                    if(!$("#sentiment-details").is(":visible"))
                        $("#sentiment-details").fadeIn(400);
                });
            }
            function load_sentiments(ids) {
                if(ids.length == 0) {
                    if($("#sentiment-details").is(":visible"))
                        $("#sentiment-details").fadeOut(400);
                }
                else {
                    $.post("details.php", {id: ids.toString()}).done(function(data){
                        $("#sentiment-details").html(data);
                        if(!$("#sentiment-details").is(":visible"))
                            $("#sentiment-details").fadeIn(400);
                    });
                }
            }
        </script>
    </body>
</html>