(function($){

    var google_visualization_loaded = false;

    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(function(){

        google_visualization_loaded = true;

        $.each( widgetsWaitingForLoad , function( i , widget ){

            $( widget.element ).bitcoinWidget( widget.data );

        });

    });

    var listOfWidgets = new Array();
    var widgetsWaitingForLoad = new Array();

    $.fn.bitcoinWidget = function( data ){

        if( !google_visualization_loaded ){

            return this.each(function(){

                widgetsWaitingForLoad.push({ "element" : this, "data" : data });

            });
        }

        return this.each(function(){

            var $widget = $(this),
                $tab_links = $widget.find("a.bitcoin-tab-link"),
                $tabs = $widget.find(".bitcoin-tab");

            listOfWidgets.push( $widget );

            if( listOfWidgets.length == 1 ){
                setInterval(function(){

                    $.get( btw_ajax_url , { action : "btw_data" }, function( response ){

                        $.each( listOfWidgets , function( i , widget ){

                            $(widget).trigger("btw.update",[ response ]);

                        });

                    },"json");

                }, 1 * 60 * 1000 );
            }

            $tab_links.each(function( index ){

                var $tab_link = $(this),
                    $tab = $tabs.eq(index),
                    tabName = $tab.attr("id").replace("bitcoin-tab-",""),
                    tabData = data[tabName],
                    $time_links =  $tab.find(".bitcoin-login-status a"),
                    time = null;

               $time_links.bind("click",function(e){

                    e.preventDefault();

                    $time_links.removeClass("active");

                    $(this).addClass("active");

                    time = $(this).data("time");

                    $tab.data("time",time);

                    $tab.find(".bitcoin-chart").empty();

                    if( !tabData["chart"] || !tabData["chart"][ time ] || tabData["chart"][ time ].length == 0 ){
                        $tab.find(".bitcoin-chart").addClass("bitcoin-chart-disabled").html( "<span>Data currently not available</span>" );
                    }
                    else {
                        
                        var chartData = new Array();

                        chartData.push(['','']);

                        $.each( tabData["chart"][ time ] , function( index , time_data ){

                            chartData.push( [ null, time_data[ 1 ] ] );

                        } );

                        var googleChartData = google.visualization.arrayToDataTable(chartData);

                        var options = {
                            hAxis : {
                                textPosition : "none"
                            },
                            legend : {
                                position : "none"
                            }
                        };

                        var chart = new google.visualization.LineChart( $tab.find(".bitcoin-chart").get( 0 ) );

                        chart.draw(googleChartData, options);

                        //$.plot( $tab.find(".bitcoin-chart").removeClass("bitcoin-chart-disabled") ,[ tabData["chart"][ time ] ] );
                    }

                });

                $tab_link.bind("click",function(e){

                    e.preventDefault();

                    $tab_links.removeClass("active");

                    $tab_link.addClass("active");

                    $tabs.hide();

                    $tab.show();

                    $time_links.filter(".active").trigger("click");

                });

                $tab.data("time","daily");

            }).first().trigger("click");

            $widget.bind("btw.update",function( e, new_data ){

                data = new_data;

                $tabs.each(function(){

                    var $tab = $(this),
                        tabName = $tab.attr("id").replace("bitcoin-tab-",""),
                        tabData = data[tabName];

                    $tab.find(".bitcoin-last-price").html(' <h2>$'+(number_format(tabData.ticker.buy,2))+'</h2>');

                    $tab.find(".bitcoin-data").html(
                        '<ul>\
                            <li>Buy : $'+ (number_format(tabData.ticker.buy,2))+'</li>\
                            <li>Sell : $'+(number_format(tabData.ticker.sell,2))+'</li>\
                            <li>High : $'+(number_format(tabData.ticker.high,2))+'</li>\
                            <li>Low : $'+(number_format(tabData.ticker.low,2))+'</li>\
                            <li>Volume : '+(number_format(tabData.ticker.volume,2))+'</li>\
                        </ul>'
                    );

                    $tab.find('.bitcoin-timeago').data("livestamp",data.updated).attr("data-livestamp",data.updated);
                        
                    $tab.find(".bitcoin-chart").empty();

                    $tab.removeClass("bitcoin-tab-loading");

                    if( tabData["chart"][ $tab.data("time") ].length == 0 ){
                        $tab.find(".bitcoin-chart").html( "Data currently not available" );
                    }
                    else {

                        var chartData = new Array();

                        chartData.push(['','']);

                        $.each( tabData["chart"][ $tab.data("time") ] , function( index , time_data ){

                            chartData.push( [ null, time_data[ 1 ] ] );

                        } );

                        var googleChartData = google.visualization.arrayToDataTable(chartData);

                        var options = {
                            hAxis : {
                                textPosition : "none"
                            },
                            legend : {
                                position : "none"
                            }
                        };

                        var chart = new google.visualization.LineChart( $tab.find(".bitcoin-chart").get( 0 ) );

                        chart.draw(googleChartData, options);


                       // $.plot( $tab.find(".bitcoin-chart") , [ tabData["chart"][ $tab.data("time") ] ]);
                    }

                });

                $('div.flot-x-axis').remove(); 
            });

        });
    }

})(jQuery);

