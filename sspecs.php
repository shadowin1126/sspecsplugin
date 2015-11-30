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
		// List all models of selected brand
		if ($path[2]) {
			// Set the current brand
			$ss->brand = $path[2];
			// Get the current brand
			$ss->query = 'SELECT `brand`.`seo_brand`, `brand`.`brand`, `model`.`modelid`, `model`.`seo_model`, `model`.`model` FROM `model` JOIN `brand` ON `model`.`brandid` = `brand`.`brandid` WHERE `brand`.`brand` LIKE "'.$ss->brand.'";';
			$ss->get_brand();
		// List all brands
		} else {
			$ss->page = 'allbrand';
			$ss->query = 'SELECT DISTINCT `brand`.`seo_brand`, `brand`.`brand` FROM `model` JOIN `brand` ON `model`.`brandid` = `brand`.`brandid`;';
			$ss->get_brand();
		}
		
		// Replace Wordpress title / Seo ultimate title
		if (isset($GLOBALS['seo_ultimate'])) {
			remove_action('wp_head', array( $GLOBALS['seo_ultimate'], 'template_head' ), 1 );
		}
		add_filter( 'jetpack_enable_opengraph', '__return_false', 99 );
		add_filter( 'the_title', 'ss_title',9999);
		add_filter( 'wp_title', 'ss_title', 10, 2);
		add_action( 'wp_head', 'ss_seo_meta', 1);
	}

	elseif ($ss->action == 'model') {
		if ($path[2]) { // List specs of selected model
			
			// Set the current model
			$ss->model = $path[3];

			// Get the current spec
			$ss->get_specs();
		}







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

	if ($ss->action == 'brand') {
		$ss->print_brand();
	}
	elseif ($ss->action == 'model') {
		$ss->print_specs();
	}
}


class SS {

	public $action;
	public $page;
	public $seo_title;
	public $seo_desc;
	public $seo_keywords;
	public $query;
	public $brand;
	public $get_brand;
	public $model;
	public $modelid;
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
	

	// SQL query for selected brand
	public function get_brand() {
		global $wpdb;
		$this->get_brand = $wpdb->get_results($this->query);
		
		// Set page title
		if ($this->get_brand[0]->model) { $this->seo_title = $this->get_brand[0]->seo_brand; }
		else { $this->seo_title = 'Brand'; }
	}

	// SQL query for selected model
	public function get_specs() {
		global $wpdb;
		/**
		if ($this->modelid) { // Checks for duplicate model names
			$query = 'SELECT * FROM `model` WHERE `model` LIKE "'.$this->model.'" AND `modelid` LIKE "'.$this->modelid.'";';
		} 
		**/
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
			$string .= '</div>';
		}
		return $string;
	}

	
	// Function to print all brand
	public function print_brand() {
		echo "
			<hr>
			<div class='row'>
			<div class='small-12 columns'>
			<div class='row'>
		";
		/**
		// Brand page to display popular models on top
		if ($this->page == 'allbrand') {
			echo "
				<div class='small-12 columns'>
				<ul class='small-block-grid-2 large-block-grid-4'>
					<li><a href='/brand/htc/'><img src='/../wp-content/uploads/brand/htc.gif' alt='htc'></a></li>
					<li><a href='/brand/samsung/'><img src='/../wp-content/uploads/brand/samsung.gif' alt='samsung'></a></li>
					<li><a href='/brand/lg/'><img src='/../wp-content/uploads/brand/lg.gif' alt='lg'></a></li>
					<li><a href='/brand/xiaomi/'><img src='/../wp-content/uploads/brand/xiaomi.gif' alt='xiaomi'></a></li>
					<li><a href='/brand/asus/'><img src='/../wp-content/uploads/brand/asus.gif' alt='asus'></a></li>
					<li><a href='/brand/sony/'><img src='/../wp-content/uploads/brand/sony.gif' alt='sony'></a></li>
					<li><a href='/brand/acer/'><img src='/../wp-content/uploads/brand/acer.gif' alt='acer'></a></li>
					<li><a href='/brand/lenovo/'><img src='/../wp-content/uploads/brand/lenovo.gif' alt='lenovo'></a></li>
					<li><a href='/brand/gigabyte/'><img src='/../wp-content/uploads/brand/gigabyte.gif' alt='gigabyte'></a></li>
					<li><a href='/brand/oppo/'><img src='/../wp-content/uploads/brand/oppo.gif' alt='oppo'></a></li>
					<li><a href='/brand/celkon/'><img src='/../wp-content/uploads/brand/celkon.gif' alt='celkon'></a></li>
					<li><a href='/brand/casio/'><img src='/../wp-content/uploads/brand/casio.gif' alt='casio'></a></li>
					<li><a href='/brand/pantech/'><img src='/../wp-content/uploads/brand/pantech.gif' alt='pantech'></a></li>
					<li><a href='/brand/maxwest/'><img src='/../wp-content/uploads/brand/maxwest.gif' alt='maxwest'></a></li>
					<li><a href='/brand/micromax/'><img src='/../wp-content/uploads/brand/micromax.gif' alt='micromax'></a></li>
					<li><a href='/brand/huawei/'><img src='/../wp-content/uploads/brand/huawei.gif' alt='huawei'></a></li>
					<li><a href='/brand/toshiba/'><img src='/../wp-content/uploads/brand/toshiba.gif' alt='toshiba'></a></li>
					<li><a href='/brand/motorola/'><img src='/../wp-content/uploads/brand/motorola.gif' alt='motorola'></a></li>
					<li><a href='/brand/zte/'><img src='/../wp-content/uploads/brand/zte.gif' alt='zte'></a></li>
					<li><a href='/brand/spice/'><img src='/../wp-content/uploads/brand/spice.gif' alt='spice'></a></li>
				</ul>
				</div></div>
				<div class='row'>
			";
		}
		**/
		$array_count = COUNT($this->get_brand);	// To check for last
		$i = 0;									// item in array
		foreach ($this->get_brand as $brand) {
			// List all brand
			if ($this->page == 'allbrand') {
				$i++;
				if ($i < $array_count) { echo "<div class='small-12 medium-6 columns'>"; }
				else { echo "<div class='small-12 medium-6 columns end'>"; }
				echo "<a href=$brand->brand/>$brand->seo_brand</a>";
				echo "</div>";
			// List models for selected brand
			} else {
				$i++;
				if ($i < $array_count) { echo "<div class='small-12 medium-6 columns'>"; }
				else { echo "<div class='small-12 medium-6 columns end'>"; }
				echo "<a href=/../model/$brand->brand/$brand->model/>$brand->seo_model</a>";
				echo "</div>";
			}
		}
		echo "</div></div></div>";
	}

	public function print_specs() {
		if ($this->system) { $ss_system = json_decode($this->system, true); $accordion_title = $this->get_title($ss_system); $accordion_tab[] = array($accordion_title,"system"); }
		if ($this->display) { $ss_display = json_decode($this->display, true); $accordion_title = $this->get_title($ss_display); $accordion_tab[] = array($accordion_title,"display"); }
		if ($this->processor) { $ss_processor = json_decode($this->processor, true); $accordion_title = $this->get_title($ss_processor); $accordion_tab[] = array($accordion_title,"processor"); }
		if ($this->memory) { $ss_memory = json_decode($this->memory, true); $accordion_title = $this->get_title($ss_memory); $accordion_tab[] = array($accordion_title,"memory"); }
		if ($this->backcamera) { $ss_backcamera = json_decode($this->backcamera, true); $accordion_title = $this->get_title($ss_backcamera); $accordion_tab[] = array($accordion_title,"backcamera"); }
		if ($this->frontcamera) { $ss_frontcamera = json_decode($this->frontcamera, true); $accordion_title = $this->get_title($ss_frontcamera); $accordion_tab[] = array($accordion_title,"frontcamera"); }
		if ($this->opengl11) { $ss_opengl11 = json_decode($this->opengl11, true); $accordion_title = $this->get_title($ss_opengl11); $accordion_tab[] = array($accordion_title,"opengl11"); }
		if ($this->opengl1x) { $ss_opengl1x = json_decode($this->opengl1x, true); $accordion_title = $this->get_title($ss_opengl1x); $accordion_tab[] = array($accordion_title,"opengl1x"); }
		if ($this->opengl20) { $ss_opengl20 = json_decode($this->opengl20, true); $accordion_title = $this->get_title($ss_opengl20); $accordion_tab[] = array($accordion_title,"opengl20"); }
		if ($this->opengl30) { $ss_opengl30 = json_decode($this->opengl30, true); $accordion_title = $this->get_title($ss_opengl30); $accordion_tab[] = array($accordion_title,"opengl30"); }
		if ($this->graphicmodes) { $ss_graphicmodes = json_decode($this->graphicmodes, true); $accordion_title = $this->get_title($ss_graphicmodes); $accordion_tab[] = array($accordion_title,"graphicmodes"); }
		if ($this->sensors) { $ss_sensors = json_decode($this->sensors, true); $accordion_title = $this->get_title($ss_sensors); $accordion_tab[] = array($accordion_title,"sensors"); }
		if ($this->codecs) { $ss_codecs = json_decode($this->codecs, true); $accordion_title = $this->get_title($ss_codecs); $accordion_tab[] = array($accordion_title,"codecs"); }
		if ($this->features) { $ss_features = json_decode($this->features, true); $accordion_title = $this->get_title($ss_features); $accordion_tab[] = array($accordion_title,"features"); }
		//if ($this->specs) { $ss_specs = json_decode($this->specs, true); }
	
		echo "
			<hr>
			<div class='row'>
			<div class='small-12 columns'>
		";

		// Display specs in accordion
		echo '<ul class="accordion" data-accordion>';
		foreach ($accordion_tab as $tab) {
			$link = '#'.$tab[1];
			if ($tab[1] == 'system') { $accordion = $this->get_model($ss_system, 6, 6, 3, 9); }
			if ($tab[1] == 'display') { $accordion = $this->get_model($ss_display, 6, 6, 4, 8); }
			if ($tab[1] == 'processor') { $accordion = $this->get_model($ss_processor, 6, 6, 3, 9); }
			if ($tab[1] == 'memory') { $accordion = $this->get_model($ss_memory, 7, 5, 4, 8); }
			if ($tab[1] == 'backcamera') { $accordion = $this->get_model($ss_backcamera, 7, 5, 6, 6); }
			if ($tab[1] == 'frontcamera') { $accordion = $this->get_model($ss_frontcamera, 7, 5, 6, 6); }
			if ($tab[1] == 'opengl11') { $accordion = $this->get_model($ss_opengl11, 8, 4, 4, 8); }
			if ($tab[1] == 'opengl1x') { $accordion = $this->get_model($ss_opengl1x, 8, 4, 4, 8); }
			if ($tab[1] == 'opengl20') { $accordion = $this->get_model($ss_opengl20, 7, 5, 4, 8); }
			if ($tab[1] == 'opengl30') { $accordion = $this->get_model($ss_opengl30, 7, 5, 4, 8); }
			if ($tab[1] == 'graphicmodes') { $accordion = $this->get_model($ss_graphicmodes, 7, 5, 6, 6); }
			if ($tab[1] == 'sensors') { $accordion = $this->get_model($ss_sensors, 5, 7, 4, 8); }
			if ($tab[1] == 'codecs') { $accordion = $this->get_model($ss_codecs, 6, 6, 4, 8); }
			if ($tab[1] == 'features') { $accordion = $this->get_model($ss_features, 6, 6, 4, 8); }
			echo "
			<li class='accordion-navigation'>
			<a href=$link role='tab' aria-controls=$tab[1]>$tab[0]</a>
			";
			//if ($this->specs == $tab[1]) {
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

// Widget to display popular brand
class brand_widget extends WP_Widget {
	// Creating the widget
	function __construct() {
		parent::__construct(
			'brand_widget', // Base ID
			__('#Brand Widget', 'text_domain'), // Widget name
			array( 'description' => __( 'Secret Specs widget to display popular brand', 'text_domain' ), ) // Widget description
		);
	}

	// Front-end
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

	// Before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] .'<h5><b>'. $title .'</b></h5>'. $args['after_title'];

	// Display output
		global $ss;	
		if ($ss->action == 'brand') {
			
			echo "
				<div class='row'>
				<div class='small-12 columns'>
				<ul class='small-block-grid-1 large-block-grid-2'>
					<li><a href='/brand/htc/'><img src='/../wp-content/uploads/brand/htc.gif' alt='htc'></a></li>
					<li><a href='/brand/samsung/'><img src='/../wp-content/uploads/brand/samsung.gif' alt='samsung'></a></li>
					<li><a href='/brand/lg/'><img src='/../wp-content/uploads/brand/lg.gif' alt='lg'></a></li>
					<li><a href='/brand/xiaomi/'><img src='/../wp-content/uploads/brand/xiaomi.gif' alt='xiaomi'></a></li>
					<li><a href='/brand/asus/'><img src='/../wp-content/uploads/brand/asus.gif' alt='asus'></a></li>
					<li><a href='/brand/sony/'><img src='/../wp-content/uploads/brand/sony.gif' alt='sony'></a></li>
					<li><a href='/brand/acer/'><img src='/../wp-content/uploads/brand/acer.gif' alt='acer'></a></li>
					<li><a href='/brand/lenovo/'><img src='/../wp-content/uploads/brand/lenovo.gif' alt='lenovo'></a></li>
					<li><a href='/brand/gigabyte/'><img src='/../wp-content/uploads/brand/gigabyte.gif' alt='gigabyte'></a></li>
					<li><a href='/brand/oppo/'><img src='/../wp-content/uploads/brand/oppo.gif' alt='oppo'></a></li>
					<li><a href='/brand/celkon/'><img src='/../wp-content/uploads/brand/celkon.gif' alt='celkon'></a></li>
					<li><a href='/brand/casio/'><img src='/../wp-content/uploads/brand/casio.gif' alt='casio'></a></li>
					<li><a href='/brand/pantech/'><img src='/../wp-content/uploads/brand/pantech.gif' alt='pantech'></a></li>
					<li><a href='/brand/maxwest/'><img src='/../wp-content/uploads/brand/maxwest.gif' alt='maxwest'></a></li>
					<li><a href='/brand/micromax/'><img src='/../wp-content/uploads/brand/micromax.gif' alt='micromax'></a></li>
					<li><a href='/brand/huawei/'><img src='/../wp-content/uploads/brand/huawei.gif' alt='huawei'></a></li>
					<li><a href='/brand/toshiba/'><img src='/../wp-content/uploads/brand/toshiba.gif' alt='toshiba'></a></li>
					<li><a href='/brand/motorola/'><img src='/../wp-content/uploads/brand/motorola.gif' alt='motorola'></a></li>
					<li><a href='/brand/zte/'><img src='/../wp-content/uploads/brand/zte.gif' alt='zte'></a></li>
					<li><a href='/brand/spice/'><img src='/../wp-content/uploads/brand/spice.gif' alt='spice'></a></li>
				</ul>
				</div></div>
				<br />
			";
		}
		echo $args['after_widget'];
	}

	// Backend
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
	// Admin Form
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}
} // Class country_widget ends here

// Register and load the widget
function load_widget() {
    register_widget( 'brand_widget' );
}


add_shortcode( 'sspecs', 'ss_func' );

add_action( 'widgets_init', 'load_widget' );
add_action( 'init', 'ss_seo_loader_init', 0);
add_filter( 'rewrite_rules_array','my_insert_rewrite_rules' );
add_action( 'wp_loaded','my_flush_rules' );


?>