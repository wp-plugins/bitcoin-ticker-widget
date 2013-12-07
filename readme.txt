=== Bitcoin Ticker Widget ===
Contributors: ofirbeigel
Donate link: http://bitcoinwithpaypal.com/bitcoin-ticker-widget-plugin/
Tags: bitcoin, bitcoin ticker, bitcoin widget, bitcoin price, bitcoin wordpress,
Requires at least: 2.9
Tested up to: 3.7.1
Stable tag: 1.4
License: GPLv2

This will allow you to display a simple Bitcoin ticker widget that displays Bitcoin prices form 4 major Bitcoin exchanges.

== Description ==
The new and improved ticker!!! Now faster, more accurate than before and displaying BTC China as well.

The Bitcoin Ticker WordPress Plugin allows you to add a widget to your WordPress blog that shows you a ticker of the latest Bitcoin prices from different exchanges.

* Gives you the option to view 3 different Bitcoin exchanges – MtGox, BTCe, BTC-China and BitStamp.
* Shows you the latest Bitcoin prices on the selected exchange.
* DIsplays a graph of the Bitcoin prices in the last 24 hours / 7 days / 30 days.
* Displays bid, ask, low, high and volume values of selected exchange.

Visit my original plugin page at http://bitcoinwithpaypal.com/bitcoin-ticker-widget-plugin/

If you have any questions or ideas for improvements please email me at ofir@nhm.co.il

== Installation ==

How to install the Bitcoin Ticker WordPress Widget (plugin) on your site:

1. Click here to download the latest version of the plugin.
2. Log in to your WordPress dashboard and go to Plugins -> Add New
3. Select “Upload” for the top tab.
4. Select “choose file” and select the Zip file of the plugin you downloaded.
5. Once the plugin is installed select “Activate plugin”.
6. Go to Appearance -> Widgets
7. Drag the widget named “Bitcoin Widget” to your side bar.

How to use the Bitcoin Ticker WordPress Widget (plugin):

You basically don’t have to do anything to use the Widget. Each time you load the page the Widget will automatically fetch the values for the different exchanges. All you need to do is select the Bitcoin exchange that you want to view.

== Frequently Asked Questions ==

If you have any questions please email me at ofir@nhm.co.il

Question: I am not getting complete date for BTC China.
Answer: It takes some time for the DataBase to fill itself. Since this version is new this should not occur after January 10th 2014.

Question: I am getting an error message "This plugin requires PHP CURL module which is not enabled on your server. Please contact your server administrator"

Answer: This means that you do not have PHP cURL enabled on your hosting plan. You will need to contact whoever is hosting your website and request that it be enabled. There is no way to fetch data from the different exchanges without PHP cURL enabled.

Question: Can I remove the "get the plugin" hyperlink at the bottom of the widget ?

Answer: Yes you can. Although I'd prefer you wouldn't. But if you still want to here's what you need to do:

1. Unzip the bitcoin-ticker-widget.zip file
2. open the bicoin_widget.php file with any text editor (I use TextMate)
3. Delete this line:
div id="get-the-plugin">a style="text-decoration: underline;" href="#" target="_BLANK">Get the plugin/a>/div>  (it should be line 244).
4. Zip all the files back together.
5. Refer to "Installation" to reinstall the plugin.


== Screenshots ==

1. Bitcoin Ticker Widget

== Changelog ==

= 1.0 =
* Plugin released.

= 1.1  =
* Reduced widget size.
* Updated to disable plugin when PHP cURL isn't enabled.

= 1.2  =
* Reduced footer size.
* Bug fix on loading of data.

= 1.3 = 
* Adjust size to column
* Use cache for faster UI
* Allow more requests per user

= 1.3.1 =
* Fixed graph to show BUY price
* Fixed "last updated" label

= 1.3.2 =
* Added missing JS files

= 1.4 =
* Added BTC China to the exchanges.
* Fixed width of ticker problem
* minor graphic changes

== Upgrade Notice ==

= 1.0 =
* Get the latest Bitcoin prices on your site

= 1.1  =
* This update in optional. It will fit the plugin better in your browser.

= 1.2  =
* This update in optional. It will make the plugin look better and fetch data faster.

= 1.3 = 
* This update in required. It will fetch correct data and speed plugin load time.

= 1.3.1 =
* This update in required. It fixes critical bugs in older version.

= 1.3.2 =
* This update in required. It fixes critical bugs in older version.

= 1.3.2 =
* This update in optional. It will add BTC China to your list of exchanges.