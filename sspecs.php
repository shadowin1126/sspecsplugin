<?php
/*
Plugin Name: SecretSpecs
Plugin URI: http://secretspecs.com/
Description: SecretSpecs plugin for phone hardware
Version: 1.0.0
Author: shadowin
Author URI: http://secretspecs.com/
License: GPL2

*/
// http://codex.wordpress.org/Function_Reference/WP_Rewrite#Examples

// Fork this project successfully to https://github.com/juzhax/sspecsplugin

function ss_seo_meta() {
	global $ss,$wp_query;

	$wp_query->queried_object->post_title = $ss->seo_title;

	add_filter( 'jetpack_enable_opengraph', '__return_false', 99 );

	$seo_title = $ss->seo_title;
	$seo_desc = $ss->seo_desc;
	$keywords = $ss->seo_keywords;

	$seo_keywords = '';
	foreach (array($keywords) as $keyword) {
		$seo_keywords .= $keyword.', ';
	}
	$seo_keywords .= 'Intel ss, ss Map, ss Portal, Guardian Portal';

	$ss_seo = '
<!-- ss SEO -->


	<link rel="canonical" href="https://'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"].'" />

	<meta name="title" content="'.$seo_title.'" />
	<meta name="entry-title" content="'.$seo_title.'" />
	<meta name="description" content="'.$seo_desc.'" />
	<meta name="keywords" content="'.$seo_keywords.'" />
	<meta name="url" content="https://'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"].'" />
	<meta property="og:type" content="article" />
	<meta property="og:title" content="'.$seo_title.'" />
	<meta property="og:description" content="'.$seo_desc.'" />
	<meta property="og:url" content="https://'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"].'" />
	';
	$ss_seo .= '<meta property="og:image" content="https://s0.wp.com/i/blank.jpg" />';

	$ss_seo .= '
	<meta property="article:published_time" content="'.get_the_date('c').'" />
	<meta property="article:modified_time" content="'.get_the_modified_date('c').'" />

	<meta property="og:site_name" content="Secret Specs" />
	<meta property="og:locale" content="en_US" />
	<meta property="fb:app_id" content="531406790246098"/>
	<meta name="twitter:card" content="summary"/>
	<meta name="twitter:description" content="'.$seo_desc.'" />
	<meta name="yandex-verification" content="60dc371905b6f83a" />
	';

/*
<!-- Jetpack Open Graph Tags -->
	<meta name="twitter:site" content="@rumorscity"/>
*/


	$ss_seo .= '
<!-- ss SEO -->
			';
	echo $ss_seo;

//	return $ss_seo;

}


function ss_title($title) {
    global $ss;
	if ( in_the_loop() && is_page() ) {
		$title = $ss->seo_title;
	}
	return $title;
}




function ss_seo_loader_init() {
	global $ss;
	$urlArr = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRIPPED));
	$path = explode('/', $urlArr['path']);
	$ss->action = $path[1];

	if ($ss->action == 'brand') {
		$ss->brand = $path[2];
		$ss->get_brand();
		$ss->print_brand();
		echo $ss->brandname;
	}

	if ($ss->action == 'model') {

		// Set the current model
		$ss->model = $path[2];

		// Get the current spec
		$ss->get_specs();
		







		// Replace Wordpress title / Seo ultimate title

		if (isset($GLOBALS['seo_ultimate'])) {
			remove_action('wp_head', array( $GLOBALS['seo_ultimate'], 'template_head' ), 1 );
		}
		add_filter( 'jetpack_enable_opengraph', '__return_false', 99 );
		add_filter( 'the_title', 'ss_title',9999);
		add_filter( 'wp_title', 'ss_title', 10, 2);
		add_action( 'wp_head', 'ss_seo_meta', 1);
	}

}

function ss_func() {
	global $ss;
//	$ss->print_specs();


	//echo '<div>';
	//$accordion_tab = array();
	
	if ($ss->system) { $ss_system = json_decode($ss->system, true); $accordion_title = $ss->get_title($ss_system); $accordion_tab[] = array($accordion_title,"system"); }
	if ($ss->display) { $ss_display = json_decode($ss->display, true); $accordion_title = $ss->get_title($ss_display); $accordion_tab[] = array($accordion_title,"display"); }
	if ($ss->processor) { $ss_processor = json_decode($ss->processor, true); $accordion_title = $ss->get_title($ss_processor); $accordion_tab[] = array($accordion_title,"processor"); }
	if ($ss->memory) { $ss_memory = json_decode($ss->memory, true); $accordion_title = $ss->get_title($ss_memory); $accordion_tab[] = array($accordion_title,"memory"); }
	if ($ss->backcamera) { $ss_backcamera = json_decode($ss->backcamera, true); $accordion_title = $ss->get_title($ss_backcamera); $accordion_tab[] = array($accordion_title,"backcamera"); }
	if ($ss->frontcamera) { $ss_frontcamera = json_decode($ss->frontcamera, true); $accordion_title = $ss->get_title($ss_frontcamera); $accordion_tab[] = array($accordion_title,"frontcamera"); }
	if ($ss->opengl11) { $ss_opengl11 = json_decode($ss->opengl11, true); $accordion_title = $ss->get_title($ss_opengl11); $accordion_tab[] = array($accordion_title,"opengl11"); }
	if ($ss->opengl1x) { $ss_opengl1x = json_decode($ss->opengl1x, true); $accordion_title = $ss->get_title($ss_opengl1x); $accordion_tab[] = array($accordion_title,"opengl1x"); }
	if ($ss->opengl20) { $ss_opengl20 = json_decode($ss->opengl20, true); $accordion_title = $ss->get_title($ss_opengl20); $accordion_tab[] = array($accordion_title,"opengl20"); }
	if ($ss->opengl30) { $ss_opengl30 = json_decode($ss->opengl30, true); $accordion_title = $ss->get_title($ss_opengl30); $accordion_tab[] = array($accordion_title,"opengl30"); }
	if ($ss->graphicmodes) { $ss_graphicmodes = json_decode($ss->graphicmodes, true); $accordion_title = $ss->get_title($ss_graphicmodes); $accordion_tab[] = array($accordion_title,"graphicmodes"); }
	if ($ss->sensors) { $ss_sensors = json_decode($ss->sensors, true); $accordion_title = $ss->get_title($ss_sensors); $accordion_tab[] = array($accordion_title,"sensors"); }
	if ($ss->codecs) { $ss_codecs = json_decode($ss->codecs, true); $accordion_title = $ss->get_title($ss_codecs); $accordion_tab[] = array($accordion_title,"codecs"); }
	if ($ss->features) { $ss_features = json_decode($ss->features, true); $accordion_title = $ss->get_title($ss_features); $accordion_tab[] = array($accordion_title,"features"); }
	//if ($ss->specs) { $ss_specs = json_decode($ss->specs, true); }
	
	echo "
		<hr>
		<div class='row'>
		<div class='small-12 columns'>
	";

	// Test to display specs using accordion
	echo '<ul class="accordion" data-accordion>';
	foreach ($accordion_tab as $tab) {
		$link = '#'.$tab[1];
		if ($tab[1] == 'system') { $accordion = $ss->get_model($ss_system, 6, 6, 3, 9); }
		if ($tab[1] == 'display') { $accordion = $ss->get_model($ss_display, 6, 6, 4, 8); }
		if ($tab[1] == 'processor') { $accordion = $ss->get_model($ss_processor, 6, 6, 3, 9); }
		if ($tab[1] == 'memory') { $accordion = $ss->get_model($ss_memory, 7, 5, 4, 8); }
		if ($tab[1] == 'backcamera') { $accordion = $ss->get_model($ss_backcamera, 7, 5, 6, 6); }
		if ($tab[1] == 'frontcamera') { $accordion = $ss->get_model($ss_frontcamera, 7, 5, 6, 6); }
		if ($tab[1] == 'opengl11') { $accordion = $ss->get_model($ss_opengl11, 8, 4, 4, 8); }
		if ($tab[1] == 'opengl1x') { $accordion = $ss->get_model($ss_opengl1x, 8, 4, 4, 8); }
		if ($tab[1] == 'opengl20') { $accordion = $ss->get_model($ss_opengl20, 7, 5, 4, 8); }
		if ($tab[1] == 'opengl30') { $accordion = $ss->get_model($ss_opengl30, 7, 5, 4, 8); }
		if ($tab[1] == 'graphicmodes') { $accordion = $ss->get_model($ss_graphicmodes, 7, 5, 6, 6); }
		if ($tab[1] == 'sensors') { $accordion = $ss->get_model($ss_sensors, 5, 7, 4, 8); }
		if ($tab[1] == 'codecs') { $accordion = $ss->get_model($ss_codecs, 6, 6, 4, 8); }
		if ($tab[1] == 'features') { $accordion = $ss->get_model($ss_features, 6, 6, 4, 8); }
		echo "
  		<li class='accordion-navigation'>
    	<a href=$link role='tab' aria-controls=$tab[1]>$tab[0]</a>
    	";
    	//if ($ss->specs == $tab[1]) {
    	if ($tab[1] == 'system') {
    		echo "<div id=$tab[1] class='content active' role='tabpanel'>";
    	}
    	else {
    		echo "
    	<div id=$tab[1] class='content' role='tabpanel'>";
    	}
    	echo "$accordion</div></li>";
    }
  	echo '</ul>';
  	echo "</div></div>";


	/**
	echo "<pre>";
	print_r($ss->system);
	echo "<br />";
	print_r($ss->codecs);
	echo "</pre>";
	**/
	/**
	if (($ss->specs == 'system') && ($ss_system != "")) {
		echo $ss->print_model($ss_system, 3, 9)."<br />";
	}
	if (($ss->specs == 'display') && ($ss_display != "")) {
		echo $ss->print_model($ss_display, 3, 9)."<br />";
	}
	if (($ss->specs == 'processor') && ($ss_processor != "")) {
		echo $ss->print_model($ss_processor, 3, 9)."<br />";
	}
	if (($ss->specs == 'memory') && ($ss_memory != "")) {
		echo $ss->print_model($ss_memory, 3, 9)."<br />";
	}
	if (($ss->specs == 'backcamera') && ($ss_backcamera != "")) {
		echo $ss->print_model($ss_backcamera, 6, 6)."<br />";
	}
	if (($ss->specs == 'frontcamera') && ($ss_frontcamera != "")) {
		echo $ss->print_model($ss_frontcamera, 6, 6)."<br />";
	}
	if (($ss->specs == 'opengl11') && ($ss_opengl11 != "")) {
		echo $ss->print_model($ss_opengl11, 4, 8)."<br />";
	}
	if (($ss->specs == 'opengl1x') && ($ss_opengl1x != "")) {
		echo $ss->print_model($ss_opengl1x, 4, 8)."<br />";
	}
	if (($ss->specs == 'opengl20') && ($ss_opengl20 != "")) {
		echo $ss->print_model($ss_opengl20, 4, 8)."<br />";
	}
	if (($ss->specs == 'opengl30') && ($ss_opengl30 != "")) {
		echo $ss->print_model($ss_opengl30, 4, 8)."<br />";
	}
	if (($ss->specs == 'graphicmodes') && ($ss_graphicmodes != "")) {
		echo $ss->print_model($ss_graphicmodes, 6, 6)."<br />";
	}
	if (($ss->specs == 'sensors') && ($ss_sensors != "")) {
		echo $ss->print_model($ss_sensors, 4, 8)."<br />";
	}
	if (($ss->specs == 'codecs') && ($ss_codecs != "")) {
		echo $ss->print_model($ss_codecs, 4, 8)."<br />";
	}
	if (($ss->specs == 'features') && ($ss_features != "")) {
		echo $ss->print_model($ss_features, 4, 8)."<br />";
	}
	**/

}


class SS {

	public $action;
	public $seo_title;
	public $seo_desc;
	public $seo_keywords;
	public $brand;
	public $brandname;
	public $model;
	public $system;
	public $display;
	public $processor;
	public $memory;
	public $backcamera;
	public $frontcamera;
	public $opengl11;
	public $opengl1x;
	public $opengl20;
	public $opengl30;
	public $graphicmodes;
	public $sensors;
	public $codecs;
	public $features;



	public function __construct() {
		$this->seo_title = '';
		$this->seo_desc = '';
		$this->seo_keywords = '';
	}
	

	// Testing sql query from two tables
	public function get_brand() {
		global $wpdb;
		$query = 'SELECT `model.brandid`, `brand.brand` FROM `model`, `brand` WHERE `brand.brand` LIKE "'.$this->brand.'" AND `model.brandid` = `brand.brandid`;';
		$result = $wpdb->get_row($query, ARRAY_A);
		$this->brandname = $result['brand'];
	}


	public function get_specs() {
		global $wpdb;
		$query = 'SELECT * FROM `model` WHERE `model` LIKE "'.$this->model.'";';
		$result = $wpdb->get_row($query, ARRAY_A);
		$this->seo_title = $result['seo_model'];
		$this->system = $result['system'];
		$this->display = $result["display"];
		$this->processor = $result['processor'];
		$this->memory = $result['memory'];
		$this->backcamera = $result['backcamera'];
		$this->frontcamera = $result['frontcamera'];
		$this->opengl11 = $result['opengl11'];
		$this->opengl1x = $result['opengl1x'];
		$this->opengl20 = $result['opengl20'];
		$this->opengl30 = $result['opengl30'];
		$this->graphicmodes = $result['graphicmodes'];
		$this->sensors = $result['sensors'];
		$this->codecs = $result['codecs'];
		$this->features = $result['features'];
	}

	public function print_specs() {
		echo $this->model;


	}

	public function print_brand() {
		echo $this->system;
	}

	// Function to get title for accordion tabs in model display
	public function get_title($arr_field) {
		if ($arr_field) {
			foreach ($arr_field as $value) {
				if ($value[0] == "title") {
					$title = $value[1];
				}
				elseif ($value[0] == "subtitle") {
					$title .= " (".$value[1].")";
					return $title;
				}
			}
		}
	}

	// Function to display sql field that is an array
	public function get_model($arr_field, $col1, $col2, $col3, $col4) {
		$checktitle = false;
		if ($arr_field) {
			$string = '<div class="row">';
			$temptitle = "";
			foreach ($arr_field as $value) {
				if ($value[0] == "subtitle") {
					//$string .= " (".$value[1].")</b></div>";
					$temptitle = ""; // First title in the field is field name so it should be ignored for <hr> tag below which checks for title
					$checktitle = false;
				}
				if ($checktitle) {
					$string .= $temptitle."</b></div>";
					$checktitle = false;
				}
				if ($value[0] == "title") {
					// Checks if it is the first title in this category
					// Also checks if data starts with "media" then do not add line above
					if (($temptitle == "") || (substr($value[1],0,5) == "Media")) {
						$temptitle = '<div class="small-12 columns"><b>'.$value[1];
						//$string .= '<div class="small-12 columns"><b>'.$value[1];
						$checktitle = true;
					}
					// Else add a line above the title
					else {
						$temptitle = '<hr><div class="small-12 columns"><b>'.$value[1];
						$checktitle = true;
					}
				}
				elseif (($value[0] == "content") || (is_int($value[0]))) {
					$string .= '<div class="small-12 columns">'.$value[1].'</div>';
				} else {
					if (($value[0] != "subtitle") && ($value[0] != "Fingerprint")) {
						$string .= '<div class="small-'.$col1.' medium-'.$col3. ' columns">'.$value[0].':</div>';
						$string .= '<div class="small-'.$col2.' medium-'.$col4. ' columns">'.$value[1].'</div>';
					}
				}
			}
			/**
			if ($checktitle) {
				$string .= "</b></div>";
				$checktitle = false;
			}
			**/
			$string .= '</div>';
		}
		return $string;
	}

	function flatten_array($arr) {
		if (!$results) {
			$results = array();
			//global $title_counter;
			//$title_counter = 0;
		}
		if (is_array($arr)) {
			$storekey = "";
			foreach ($arr as $key => $value) {
				// If value is an array, do not store value, e.g. array[content] = Array
				if (!is_array($value)) {
					if ($key == "name") {
						$storekey = $value;
					}
					elseif ($key == "content") {
						// Merge (array[name] = value) + (array[content] = value) to become array[] = (name, content)
						if (($storekey) && ($storekey != "")) {
							$results[] = array($storekey,$value);
							$storekey = "";
						} else {
							$results[] = array($key,$value);
						}
					} else {
						$results[] = array($key,$value);
					}
				} else {
					// To check for $value that is stored under $value[entry][0] and put it under array("content",$value[0])
					if ($value[0][0]) {
						$results[] = array("content",$value[0]);
					}
					$results = array_merge($results, $this->flatten_array($value));
				}
			}
		}
		return $results;
	}
	/** List nested arrays and flatten array with recursive
	function nested_list(array $array){
		//$output = '<ul>';
		foreach($array as $key => $value){
			if(is_array($value)){
				$output .= $this->nested_list($value);
			}else{
				if (($key == "title") || ($key == "subtitle")) {
					$output .= '<b>';
					if ($key == "title") {
						$output .= "<br /><br />".$value;
					} else {
						$output .= " (".$value.")";
					}
					$output .= '</b>';
				}
				elseif ($key == "name") {
					$output .= "<br />".$value." : ";
				}
				elseif ($key == "content") {
					$output .= $value;
				}
			}
			//$output .= '<br />';
		}
		//$output .= '</ul>';
		return $output;
	}
	function array_flatten_recursive($array) {
		if($array) {
			$flat = array();
			foreach(new RecursiveIteratorIterator(new RecursiveArrayIterator($array), RecursiveIteratorIterator::SELF_FIRST) as $key=>$value) {
				if(!is_array($value)) {
					$flat[] = $value;
				}
			}

			return $flat;
		}
	}
	**/
}



// flush_rules() if our rules are not yet included
function my_flush_rules(){
	$rules = get_option( 'rewrite_rules' );

	if ((!isset($rules['(brand)/(.+)$'])) || (!isset($rules['(model)/(.+)$']))) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}
}

// Adding a new rule
function my_insert_rewrite_rules( $rules ) {
	$newrules = array();
	$newrules['brand/(.+)$'] = 'index.php?pagename=brand';
	$newrules['model/(.+)$'] = 'index.php?pagename=model';
	return $newrules + $rules;
}

//[ss] shortcode
/*
function ss_func( $xml ) {

	// 	getFromDatabase();
	$xml = './wp-content/uploads/xml/3Q_AC1024C_gs702a.xml';
	$array = json_decode(json_encode((array)simplexml_load_file($xml)),1);
	print_r($array);
	foreach($array as $row) {
		echo $row;
	}

}
*/

// add_shortcode( 'ss', 'ss_func' );

$ss = new SS();



add_shortcode( 'sspecs', 'ss_func' );

add_action( 'init', 'ss_seo_loader_init', 0);
add_filter( 'rewrite_rules_array','my_insert_rewrite_rules' );
add_action( 'wp_loaded','my_flush_rules' );


?>