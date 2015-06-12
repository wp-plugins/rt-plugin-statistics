<?php
/*
	Plugin Name: RT Plugin Statistics
	Plugin URI:  http://www.this-play.nl
	Description: Displays plugin activation statistics for a multisite network
	Version:     1.0
	Author:      Roy Tanck
	Author URI:  http://www.this-play.nl
	Text Domain: rt-plugin-stats
	License:     GPLv2
	Network:     true
*/

// if called without WordPress, exit
if( !defined('ABSPATH') ){ exit; }


if( !class_exists('RT_Plugin_Stats') && is_multisite() ){

	class RT_Plugin_Stats {

		private $textdomain = 'rt-plugin-stats';

		/**
		 * Constructor
		 */
		function __construct() {
			// load the plugin's text domain			
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
			// hook for the admin page
			add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
			// hook for the admin js
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_js' ) );
		}


		/**
		 * Load the translated strings
		 */
		function load_textdomain(){
			load_plugin_textdomain( $this->textdomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}


		/**
		 * Add a new options page to the network admin
		 */
		function admin_menu() {
			add_submenu_page( 'settings.php', __( 'Plugin Statistics', $this->textdomain ), __( 'Plugin Statistics', $this->textdomain ), 'manage_options', 'rt_plugin_stats', array( $this, 'settings_page' ) );
		}


		/**
		 * Render the options page
		 */
		function settings_page() {

			// start a timer to keep track of processing time
			$starttime = microtime( true );

			// create a new array to keep the stats in
			$results = array();

			// start the page's output
			echo '<div class="wrap">';
			echo '<h2>' . __( 'Plugin Statistics', $this->textdomain ) . '</h2>';
			echo '<h3>' . __( 'Network activated plugins', $this->textdomain ) . '</h3>';
			echo '<p>';
			
			// get network activated plugins
			$network_plugins = get_site_option( 'active_sitewide_plugins', null );
			
			// render the html table
			if( !empty( $network_plugins ) ){
				$this->render_network_activated_table( $network_plugins );
			}
			
			echo '</p>';

			// get all currently published sites
			$args = array(
				'archived'   => 0,
				'mature'     => 0,
				'spam'       => 0,
				'deleted'    => 0,
				'limit'      => 9999,
				'offset'     => 0,
			);
			$sites = wp_get_sites( $args );

			echo '<h3>' . __( 'Activated plugins', $this->textdomain ) . '</h3>';
			echo '<p>';

			// gather the data by looping through the sites and getting the active_plugins option
			foreach( $sites as $site ){
				
				$plugins = get_blog_option( $site['blog_id'], 'active_plugins', null );
			
				foreach( $plugins as $plugin ){
					if( !empty( $plugin ) ){
						// clean up the php file path that WordPress stores to get a "semi-readable" name
						$pluginname = $this->get_plugin_name( $plugin );
						// make sure there's an array for this plugin
						if( !isset($results[$pluginname]) || !is_array( $results[$pluginname] ) ){
							$results[$pluginname] = array();
						}
						// add the instance's data to the array
						$results[$pluginname][] = $site['path'];
					}
				}

			}

			// sort the results array alphabetically
			ksort( $results );

			// render the html table
			$this->render_table( $results );
			
			// wrap up
			echo '</p>';
			echo '<p><em>';
			printf( __('Page render time: %1$s seconds, sites queried: %2$s', $this->textdomain ), round( microtime( true ) - $starttime, 3 ), count( $sites ) );
			echo '</em></p>';
			echo '</div>';

			// add the inline js
			$this->render_inline_js();
		}


		/**
		 * Gets passed the network activated plugins array, renders a nice HTML table
		 */
		function render_network_activated_table( $results ){
			$html = '<table class="widefat fixed" cellspacing="0">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Plugin name', $this->textdomain ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			$count = 0;

			foreach( $results as $name=>$inst ){
				$html .= '<tr' . ( ( $count % 2 == 0 ) ? ' class="alternate"' : '' ) . '>';
				$html .= '<td class="column-columnname"><strong>' . $this->get_plugin_name( $name ) . '</strong></td>';
				$html .= '</tr>';
				$count++;
			}

			$html .= '</tbody>';
			$html .= '</table>';

			echo $html;
		}


		/**
		 * Gets passed the results array, renders a nice HTML table
		 */
		function render_table( $results ){
			$html = '<table class="widefat fixed" cellspacing="0">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Plugin name', $this->textdomain ) . '</th>';
			$html .= '<th class="manage-column column-columnname num">' . __( 'Activation count', $this->textdomain ) . '</th>';
			$html .= '<th class="manage-column column-columnname">' . __( 'Sites', $this->textdomain ) . '</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			$count = 0;

			foreach( $results as $name=>$inst ){
				$html .= '<tr' . ( ( $count % 2 == 0 ) ? ' class="alternate"' : '' ) . '>';
				$html .= '<td class="column-columnname"><strong>' . $name . '</strong></td>';
				$html .= '<td class="column-columnname num">' . count( $inst ) . '</td>';

				$html .= '<td class="column-columnname">';
				$html .= '<div class="rt_plugin_stats_details" style="display: none;">';
				foreach( $inst as $i ){
					$html .= $i . '<br />';
				}
				$html .= '</div>';
				$html .= '<a class="rt_plugin_stats_toggle_details" href="#">' . __( 'show', $this->textdomain ) . '</a>';
				$html .= '</td>';
				
				$html .= '</tr>';

				$count++;
			}

			$html .= '</tbody>';
			$html .= '</table>';

			echo $html;
		}


		/**
		 * A little bit of inline JS to fold/unfold the site info
		 */
		function render_inline_js(){
			$html = '<script type="text/javascript">';
			$html .= 'jQuery(document).ready(function( $ ) {';
			$html .= '$(".rt_plugin_stats_toggle_details").click( function( e ){';
			$html .= 'e.preventDefault();';
			$html .= '$(this).closest("td").find(".rt_plugin_stats_details").slideToggle(500,function(){';
			$html .= 'if( $(this).css("display") == "none" ){';
			$html .= '$(this).closest("td").find(".rt_plugin_stats_toggle_details").html("' . __( 'show', $this->textdomain ) . '")';
			$html .= '} else {';
			$html .= '$(this).closest("td").find(".rt_plugin_stats_toggle_details").html("' . __( 'hide', $this->textdomain ) . '")';
			$html .= '}';
			$html .= '});';
			$html .= '});';
			$html .= '});';
			$html .= '</script>';
			echo $html;
		}


		/**
		 * Convert a plugins file's path into something readable
		 */
		function get_plugin_name( $path_str ){
			$r = $path_str;
			if( strpos( $path_str, '/' ) !== false ){
				$r = substr( $r, strrpos( $r, '/' )+1 );	
			}
			$r = str_replace( '.php', '', $r );
			return sanitize_title( $r );
		}


		/**
		 * Enqueue javascript (just depenencies for now)
		 */
		function enqueue_js( $hook ){
			if ( 'settings_page_rt_plugin_stats' != $hook ) {
				return;
			}
			wp_enqueue_script( 'jquery' );
		}

	}

	// create an instance of the class
	$RT_Plugin_Stats = new RT_Plugin_Stats();

}

?>