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

	<meta property="og:site_name" content="ss Intel" />
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
	$ss_system = json_decode($ss->system, true);
	$ss_backcamera = json_decode($ss->backcamera, true);
	//echo $ss->nested_list($ss_system);
	//echo $ss->nested_list($ss_backcamera);
	//echo "<br /><br />";
	
	echo "<pre>";
	print_r($ss->flatten_array($ss_backcamera));

}


class SS {

	public $action;
	public $seo_title;
	public $seo_desc;
	public $seo_keywords;
	public $model;
	public $system;
	public $backcamera;
	
	
	
	
	public function __construct() {
		$this->seo_title = '';
		$this->seo_desc = '';
		$this->seo_keywords = '';
	}
	
	
	public function get_specs() {
		global $wpdb;
		$query = 'SELECT * FROM `model` WHERE `model` LIKE "'.$this->model.'";';
		$result = $wpdb->get_row($query, ARRAY_A);
		$this->seo_title = $result['model'];
		$this->system = $result['system'];
		$this->backcamera = $result['backcamera'];
		
		
//		return $result;
	}
	
	public function print_specs() {
		
		echo $this->model;
	
	
	
	}
	
	public function print_android() {
		echo $this->model;
	}
	
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
	
	function flatten_array($arr) {
		if (!$results) {
			$results = array();
		}
		if (is_array($arr)) {
			$storekey = "";
			foreach ($arr as $key => $value) {
				// If value is an array, do not store value, e.g. array[content] = Array
				if (!is_array($value)) {
					if ($key == "title") {
						$results["title"] = $value;
					}
					elseif ($key == "subtitle") {
						$results["subtitle"] = $value;
					}
					// Use temp key to store value in order to merge later if array[name] and array[content] exist
					elseif ($key == "name") {
						$storekey = $value;
					}
					elseif ($key == "content") {
						// Merge (array[name] = value) + (array[content] = value) to become array[name] = content
						if (($storekey) && ($storekey != "")) {
							$results[$storekey] = $value;
							$storekey = "";
						} else {
							$results["content"] = $value;
						}
					}
				}
				$results = array_merge($results, $this->flatten_array($value));
			}
		}
		

		return $results;
	}
	
	/**
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

	if ( ! isset( $rules['(model)/(.+)$'] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}
}

// Adding a new rule
function my_insert_rewrite_rules( $rules ) {
	$newrules = array();
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