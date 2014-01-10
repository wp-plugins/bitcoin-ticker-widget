// JavaScript Document

$(document).ready(function() {
	//$("#ltc_info").hide();
    $(".main_category").click(function(){
		var drop_child=$(this).children('.dropdown');
		
		/*$(".dropdown").each(function(i,e){
			if($(e).hasClass('expanded') && !($(e).get(0)==$(drop_child).get(0)))
			{
				$(e).fadeOut(0);
				$(e).removeClass('expanded');
			}
		});*/
		
		$(drop_child).animate({
			opacity:1,
			height:'auto'
		},100,'linear',function(){
			
				if($(drop_child).hasClass('expanded'))
				{
					$(this).fadeOut(200);
					$(drop_child).removeClass('expanded');
				}
				else
				{					
					$(this).fadeIn(200);
					$(drop_child).addClass('expanded');
				}
			}
		);
	});
	
	$(".drop_options").click(function(e){
		var parent_category=$(this).parent().parent().parent();
		var category_value=$(parent_category).children('.category_value');
		
		$(category_value).html($(this).html());
		$('.dropdown').fadeOut(0);
		//$('.dropdown').removeClass('expanded');
		if($(category_value).attr('id')=='widget_coin')
		{
			var widget_parent_id=$(this).parent().parent().parent().parent().parent().attr('id');
			update_Widget(widget_parent_id);
		}
	});
	
	/*$(".main_category").trigger( "click" );
	var temp_coin=$("#widget_coin").parent();
	$(temp_coin).trigger( "click" );*/
	setTimeout(function(){$(".bitcoin-widget").css('opacity','1');},2000);
});

$(window).bind("load",function(){
	$(".main_category").trigger( "click" );
	var temp_coin=$("#widget_coin").parent();
	$(temp_coin).trigger( "click" );
	
	$(".widget_period").each(function(i,e){
		var temp_period_dropdown=$(e).parent();
		temp_period_dropdown=$(temp_period_dropdown).children('.dropdown');
		$(temp_period_dropdown).hide();
		$(temp_period_dropdown).removeClass('expanded');
	});
	
});

function update_Widget(widget_parent_id)
{
	if(($("#widget_coin").html()).toLowerCase()=='btc')
	{
		$("#ltc_info").hide();
		$("#btc_info").show();
		var listOfWidgets = new Array();
		listOfWidgets.push( $("#"+widget_parent_id) );
		
		$(".bitcoin-chart").css('opacity','0');
		$("#btc_info .loading").show();
		
		 $.get( btw_ajax_url , { action : "btw_data" , random : new Date().getTime() }, function( response ){

			$.each( listOfWidgets , function( i , widget ){

				$(widget).trigger("btw.update",[ response ]);

			});
			$(".bitcoin-chart").css('opacity','1');
			$("#btc_info .loading").hide();

		},"json");
	}
	else if(($("#widget_coin").html()).toLowerCase()=='ltc')
	{
		$("#btc_info").hide();
		$("#ltc_info").show();
		//$("#ltc_info").css('opacity','0');
		//$("#bitcoin-tab-mtgox").show();
		
		$(".litecoin-chart").css('opacity','0');
		$("#ltc_info .loading").show();
		
		var listOfWidgets = new Array();
		listOfWidgets.push( $("#"+widget_parent_id) );
		
				$.get( lcw_ajax_url , { action : "lcw_data" , random : new Date().getTime() }, function( response ){
	
				$.each( listOfWidgets , function( i , widget ){
	
					$(widget).trigger("lcw.update",[ response ]);
	
				});
				
				$(".litecoin-chart").css('opacity','1');
				$("#ltc_info .loading").hide();
	
			},"json");
			//setTimeout(function(){$("#ltc_info").css('opacity','1');},1000);
		
		//$("#ltc_info .litecoin-tab-nav .drop_options").trigger('click');
		//$("#ltc_info #litecoin-tab-litecoin .litecoin-login-status a").trigger('click');
	}
}