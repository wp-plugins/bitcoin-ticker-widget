<?php
/*
    Plugin Name: Bitcoin Ticker Widget
    Plugin URI: 
    Description: Displays a ticker widget on your site of latest Bitcoin prices
    Author: Ofir Beigel
    Version: 1.1
    Author URI: ofir@nhm.co.il
*/



function fetch_chart_data(){	
	
	$name = $_GET['tab'];
	$data = array();
	//echo date('m d Y H:i:s',(time() - 24 * 60 * 60));
	$url = '';
	if($name == 'bitstamp'){
		$url = 'https://www.bitstamp.net/api/transactions/?limit=4500';
		
		$ch = curl_init($url);

		// Configuring curl options
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('Content-type: application/json') ,
		);

		// Setting curl options
		curl_setopt_array( $ch, $options );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  
		// Getting results
		$result =  curl_exec($ch); // Getting JSON result string

		$data = json_decode($result);
			
		//$data =  json_encode($data);	
		$prev = 0;
		$k =0 ;
		foreach(array_reverse($data) as $d)
		{
			if((int)$d->date >= (time() - (24 * 60 * 60)) && $k == 0){
				$prev = (int)$d->date;
				$k++;
			}

			if((int)$d->date >= $prev + (60 * 60)){
				$prev =(int) $d->date;
				$arr[] = array($k , (float)$d->price);
				$k++;
			}
			
		}
		echo json_encode($arr);
		die;
	}
	
	switch($name)
	{
		case 'mtgox':
			$url = 'http://api.bitcoincharts.com/v1/trades.csv?symbol=mtgoxUSD&start=' . (time() - 24 * 60 * 60);
		break;
		case 'btce':
			$url = 'http://api.bitcoincharts.com/v1/trades.csv?symbol=btceUSD&start=' . (time() - 24 * 60 * 60);
		break;
	}
	
	$arr = array();
        if ($stream = fopen($url, 'r')) {
		
		$prev = 0;
		$k =0 ;
		while (($data = fgetcsv($stream, 8000, "\n")) !== FALSE) {
			
			$d = explode(',',$data[0]);
			if($k == 0){
				$prev = $d[0];
				$k++;
			}
			
			if($d[0] >= $prev + (60 * 60)){
				$prev = $d[0];
				//echo date('d m Y H:i:s', $d[0]) . '<br />';
				$arr[] = array($k , (float)$d[1]);
				$k++;
			}
		}	

		fclose($stream);
	}
	echo json_encode($arr);
	die;	
}
add_action('wp_ajax_fetch_chart_data', 'fetch_chart_data');
add_action('wp_ajax_nopriv_fetch_chart_data', 'fetch_chart_data');



function fetch_widget_data(){

    $tab = $_GET['tab'];

    $request = array('mtgox' => 'http://data.mtgox.com/api/1/BTCUSD/ticker',
                     'btce' => 'https://btc-e.com/api/2/btc_usd/ticker',
                     'bitstamp' => 'https://www.bitstamp.net/api/ticker/');
        
    
    // Initializing curl
    $ch = curl_init($request[$tab]);

    // Configuring curl options
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('Content-type: application/json') ,
    );

    // Setting curl options
    curl_setopt_array( $ch, $options );
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  
    // Getting results
    $result =  curl_exec($ch); // Getting JSON result string

    $data = json_decode($result);
    
    $echo = array();
    switch($tab){
        case 'mtgox':
            $echo['buy'] = $data->return->buy->value;
            $echo['sell'] = $data->return->sell->value;
            $echo['high'] = $data->return->high->value;
            $echo['low'] = $data->return->low->value;
            $echo['vol'] = $data->return->vol->value;
            $echo['updated'] = round($data->return->now / 1000000);
            break;
        case 'btce':
            $echo['buy'] = $data->ticker->buy;
            $echo['sell'] = $data->ticker->sell;
            $echo['high'] = $data->ticker->high;
            $echo['low'] = $data->ticker->low;
            $echo['vol'] = $data->ticker->vol;
            $echo['updated'] = $data->ticker->updated;
            break;
        case 'bitstamp':
             $echo['buy'] = $data->bid;
            $echo['sell'] = $data->ask;
            $echo['high'] = $data->high;
            $echo['low'] = $data->low;
            $echo['vol'] = $data->volume;
            $echo['updated'] = $data->timestamp;
            break;

    }
    
    echo json_encode(array('ticker' => $echo));
    curl_close($ch);
    die;
}
add_action('wp_ajax_fetch_widget_data', 'fetch_widget_data');
add_action('wp_ajax_nopriv_fetch_widget_data', 'fetch_widget_data');

function fetch_plugin_data()
{

	$data = file_get_contents('http://nhm.co.il/plugin_data.csv');
	$data = explode(',',$data);
	
	echo json_encode(array( 'powered_by' => $data[0],
							'powered_by_url' => $data[1],
							'get_plugin_url' => $data[2]));
	die;
}
add_action('wp_ajax_fetch_plugin_data', 'fetch_plugin_data');
add_action('wp_ajax_nopriv_fetch_plugin_data', 'fetch_plugin_data');


/**
 * Proper way to enqueue scripts and styles
 */
function bitcoin_scripts() {
	wp_enqueue_style( 'bitcoin-style',  plugin_dir_url(__FILE__) . '/css/style.css' );

        if(!wp_script_is('jquery'))
            wp_enqueue_script( 'jquery');

        wp_enqueue_script( 'bitcoin-chart', plugin_dir_url(__FILE__) . '/js/sparkline.js', array('jquery'), '', true );
        wp_enqueue_script( 'bitcoin-script', plugin_dir_url(__FILE__) . '/js/script.js', array('jquery'), '', true );
        wp_localize_script( 'jquery', 'ajax_url', site_url() . '/wp-admin/admin-ajax.php' );
}

add_action( 'wp_enqueue_scripts', 'bitcoin_scripts' );

/**
 * Adds Bitcoin widget.
 */
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
		?>
                <div id="bitcoin-widget">
				<div class="bitcoin-logo">
				<img src="<?php echo plugin_dir_url(__FILE__); ?>/image/bitcoin_final_03.png" />
				</div>
                    <div class="tab-div">
                        <a class="tab" href="#mtgox" data-name="mtgox">Mt.GOX</a>
                        <a class="tab middle-tab" href="#btce" data-name="btce">BTC-E</a>
                        <a class="tab" href="#bitstamp" data-name="bitstamp">BitStamp</a>
                        <div class="clear line"></div>
                    </div>
					<div id="fixedheight">
						<div id="last-price"></div>
						<div id="chart"></div>
						<div class="bitcoin-login-status">Last 24 hours</div>
						<div id="bitcoin-data">
							
						</div>
						<div class="clear"></div>
						<div id="link-row"></div>
						<div class="clear"></div>
					</div>	
					<hr />
					<div class="bitcon-footer">
						<div id="powered-by">Powered by <span></span></div>
						<div id="get-the-plugin"><a style="text-decoration: underline;" href="#" target="_BLANK">Get the plugin</a></div>
						<div class="loader">Loading...</div>
					</div>
                </div>
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