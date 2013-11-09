<?php
/*
    Plugin Name: Bitcoin Ticker Widget
    Plugin URI: 
    Description: Displays a ticker widget on your site of latest Bitcoin prices
    Author: Ofir Beigel
    Version: 1.3
    Author URI: ofir@nhm.co.il
*/

DEFINE("BTW_API_URL","http://bitcoinwithpaypal.com/api/v1/");
DEFINE("BTW_CACHE_DURATION",300); // 5 minutes, because API is regenerated every 5 minutes

register_activation_hook( __FILE__,  "btw_update_data" );

function btw_update_data(){

	$response = wp_remote_get( BTW_API_URL , array(
		"sslverify" => false,
		"timeout" => 10
	) );

	$btw_options = get_option("btw_options");

	$update_time = time();

	if( !$btw_options ) $btw_options = array();

	if( !$btw_options["data"] )
		$btw_options["data"] = array( 
			"chart" => array() , 
			"ticker" =>array( 
				'buy' => 0,
				'sell' => 0,
				'high' => 0,
				'low' => 0,
				'volume' => 0
			),
			'updated' => $update_time
		);

	if ( is_wp_error( $response ) ):

		$btw_options["data"]["updated"] = $update_time;

		update_option( "btw_options" , array(
			"last_updated" => $update_time,
			"data" => $btw_options["data"]
		) );
		
		return;

	endif;

	$json = json_decode( $response["body"] , true );

	if( isset( $json["error"] ) && $json["error"] == true ):

		$btw_options["data"]["updated"] = $update_time;

		update_option( "btw_options" , array(
			"last_updated" => $update_time,
			"data" => $btw_options["data"]
		) );
	else :

		$json["updated"] = $update_time;

		update_option( "btw_options" , array(
			"last_updated" => $update_time,
			"data" => $json
		) );

	endif;
}

function btw_get_options( $update = true){

	$btw_options = get_option( "btw_options" );

	if( $update && ( !$btw_options || $btw_options["last_updated"] < time() - BTW_CACHE_DURATION ) ):
		btw_update_data();
	endif;

	return $btw_options;
}

function btw_data(){	
	
	$btw_options = btw_get_options();

	btw_output_json( $btw_options["data"] );
	
}

add_action('wp_ajax_btw_data', 'btw_data');
add_action('wp_ajax_nopriv_btw_data', 'btw_data');

function btw_output_json( $data ){

	header("Content-type:application/json");

	echo json_encode( $data );
	exit;

}
/**
 * Proper way to enqueue scripts and styles
 */
function bitcoin_scripts() {
	wp_enqueue_style( 'bitcoin-style',  plugin_dir_url(__FILE__) . '/css/style.css' );

        if(!wp_script_is('jquery'))
            wp_enqueue_script( 'jquery');

        wp_enqueue_script( 'bitcoin-plugins', plugin_dir_url(__FILE__) . '/js/plugins.js', array('jquery'), '', true );
        wp_enqueue_script( 'bitcoin-script', plugin_dir_url(__FILE__) . '/js/script.js', array('jquery'), '', true );
        wp_localize_script( 'jquery', 'ajax_url', site_url() . '/wp-admin/admin-ajax.php' );
}

add_action( 'wp_enqueue_scripts', 'bitcoin_scripts' );

function bitcoin_head() {
	?><script type='text/javascript'>var btw_ajax_url = "<?php echo admin_url('admin-ajax.php'); ?>"; </script><?php
}

add_action( 'wp_head', 'bitcoin_head' , 1 );

/**
 * Adds Bitcoin widget.
 */

global $btw_widget_index;

$btw_widget_index = 0;

class Bitcoin_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'bitcoin_widget', // Base ID
			'Bitcoin Widget', // Name
			array( 'description' => __( 'Bitcoin Price Widget', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$link = apply_filters( 'widget_title', $instance['link'] );

		echo $args['before_widget'];
	
		$btw_options = btw_get_options( false );

		global $btw_widget_index;

		$btw_widget_index++;
		
		?>
			<div id='bitcoin-widget-<?php echo $btw_widget_index; ?>'>
                <div id="bitcoin-widget" class='bitcoin-widget'>
					<div class="bitcoin-logo"></div>
                    <div class="bitcoin-tab-nav">
                        <a class="bitcoin-tab-link bitcoin-first-tab-link" href="javascript:void(0)" data-name="mtgox">Mt.GOX</a>
                        <a class="bitcoin-tab-link bitcoin-middle-tab-link" href="javascript:void(0)" data-name="btce">BTC-E</a>
                        <a class="bitcoin-tab-link bitcoin-last-tab-link" href="javascript:void(0)" data-name="bitstamp">BitStamp</a>
                        <div class="clear line"></div>
                    </div>
					<div class="bitcoin-widget-tabs">
						<div class='bitcoin-tab' id='bitcoin-tab-mtgox' >
							<div class='bitcoin-tab-content' >
								<div class="bitcoin-last-price"><h2><?php echo number_format($btw_options["data"]["mtgox"]["ticker"]["buy"],2);?></h2></div>
								<div class="bitcoin-chart"></div>
								<div class="bitcoin-login-status">Show last: <a href='javascript:void(0)' data-time='daily' class='active' >24h</a> / <a href='javascript:void(0)' data-time='weekly' >7d</a> / <a href='javascript:void(0)' data-time='monthly' >30d</a></div>
								<div class="bitcoin-data">
									<ul>
		                                <li>Buy : $<?php echo number_format($btw_options["data"]["mtgox"]["ticker"]["buy"],2); ?></li>
		                                <li>Sell : $<?php echo number_format($btw_options["data"]["mtgox"]["ticker"]["sell"],2); ?></li>
		                                <li>High : $<?php echo number_format($btw_options["data"]["mtgox"]["ticker"]["high"],2); ?></li>
		                                <li>Low : $<?php echo number_format($btw_options["data"]["mtgox"]["ticker"]["low"],2); ?></li>
		                                <li>Volume : <?php echo number_format($btw_options["data"]["mtgox"]["ticker"]["volume"],2); ?></li>
		                            </ul>
								</div>
								<div class="bitcoin-link-row">
									<span class="bitcoin-last-updated">Last updated: <span class="bitcoin-timeago" data-livestamp="<?php echo $btw_options["last_updated"]; ?>" ></span></span>
								</div>
							</div>
						</div>
						<div class='bitcoin-tab' id='bitcoin-tab-btce' >
							<div class='bitcoin-tab-content' >
								<div class="bitcoin-last-price"><h2><?php echo number_format($btw_options["data"]["btce"]["ticker"]["buy"] , 2);?></h2></div>
								<div class="bitcoin-chart"></div>
								<div class="bitcoin-login-status">Show last: <a href='javascript:void(0)' data-time='daily' class='active' >24h</a> / <a href='javascript:void(0)' data-time='weekly' >7d</a> / <a href='javascript:void(0)' data-time='monthly' >30d</a></div>
								<div class="bitcoin-data">
									<ul>
		                                <li>Buy : $<?php echo number_format($btw_options["data"]["btce"]["ticker"]["buy"] , 2); ?></li>
		                                <li>Sell : $<?php echo number_format($btw_options["data"]["btce"]["ticker"]["sell"] , 2); ?></li>
		                                <li>High : $<?php echo number_format($btw_options["data"]["btce"]["ticker"]["high"] , 2); ?></li>
		                                <li>Low : $<?php echo number_format($btw_options["data"]["btce"]["ticker"]["low"] , 2); ?></li>
		                                <li>Volume : <?php echo number_format($btw_options["data"]["btce"]["ticker"]["volume"] , 2); ?></li>
		                            </ul>
		                         </div>
								<div class="bitcoin-link-row">
									<span class="bitcoin-last-updated">Last updated: <span class="bitcoin-timeago" data-livestamp="<?php echo $btw_options["last_updated"]; ?>" ></span></span>
								</div>
							</div>
						</div>
						<div class='bitcoin-tab' id='bitcoin-tab-bitstamp' >
							<div class='bitcoin-tab-content' >
								<div class="bitcoin-last-price"><h2><?php echo number_format($btw_options["data"]["bitstamp"]["ticker"]["buy"] , 2);?></h2></div>
								<div class="bitcoin-chart"></div>
								<div class="bitcoin-login-status">Show last: <a href='javascript:void(0)' data-time='daily' class='active' >24h</a> / <a href='javascript:void(0)' data-time='weekly' >7d</a> / <a href='javascript:void(0)' data-time='monthly' >30d</a></div>
								<div class="bitcoin-data">
									<ul>
		                                <li>Buy : $<?php echo number_format( $btw_options["data"]["bitstamp"]["ticker"]["buy"] , 2); ?></li>
		                                <li>Sell : $<?php echo number_format($btw_options["data"]["bitstamp"]["ticker"]["sell"] , 2); ?></li>
		                                <li>High : $<?php echo number_format($btw_options["data"]["bitstamp"]["ticker"]["high"] , 2); ?></li>
		                                <li>Low : $<?php echo number_format($btw_options["data"]["bitstamp"]["ticker"]["low"] , 2); ?></li>
		                                <li>Volume : <?php echo number_format($btw_options["data"]["bitstamp"]["ticker"]["volume"] , 2); ?></li>
		                            </ul>
								</div>
								<div class="bitcoin-link-row">
									<span class="bitcoin-last-updated">Last updated: <span class="bitcoin-timeago" data-livestamp="<?php echo $btw_options["last_updated"]; ?>" ></span></span>
								</div>
							</div>
						</div>
					</div>	
					<hr />
					<div class="bitcoin-footer">
                        <div class="bitcoin-get-the-plugin"><a style="text-decoration: underline;" href="http://bitcoinwithpaypal.com/bitcoin-ticker-widget-plugin/" target="_BLANK">Get the Bitcoin Ticker</a></div>
                    </div>
                    <!--<div class="bitcoin-loader"></div>-->
                </div>
            </div>
            <script type='text/javascript' >
                jQuery(document).ready(function($){

                	var data  = <?php echo json_encode( $btw_options["data"] ); ?>;

                	$("#bitcoin-widget-<?php echo $btw_widget_index; ?>").bitcoinWidget( data );

                });
            </script>
                <?php
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		return;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['link'] = ( ! empty( $new_instance['link'] ) ) ? strip_tags( $new_instance['link'] ) : '';

		return $instance;
	}

} // class Bitcoin_Widget

function register_bitcoin_widget(){
    register_widget( 'Bitcoin_Widget' );
}
add_action( 'widgets_init', 'register_bitcoin_widget');


function bitcoin_activate() {

    // Activation code here...
    if(!function_exists('curl_version')){
        deactivate_plugins(__FILE__);
        wp_die('This plugin requires PHP CURL module which is not enabled on your server. Please contact your server administrator');
    }
	
}
register_activation_hook( __FILE__, 'bitcoin_activate' );