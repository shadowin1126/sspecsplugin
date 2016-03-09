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
	$seo_keywords .= 'Android';

	$ss_seo = '
<!-- ss SEO -->

	<link rel="author" href="https://plus.google.com/u/0/105697456818218161068"/>
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
	<meta name="twitter:site" content="@secret_specs"/>
	<meta name="google-site-verification" content="7HwbOdRomrvwyGKJ-BfbtzNRpEVXsFLEQyK0LGpzBEU"/>
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
		$title = $ss->title;
	}
	return $title;
}




function ss_seo_loader_init() {
	global $wpdb;
	global $ss;
	$urlArr = parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_STRIPPED));
	$path = explode('/', $urlArr['path']);
	$ss->action = $path[1];
	
	// Brand page
	if ($ss->action == 'brand') {
	
		// List all models of selected brand
		if (($path[2]) && (!$path[3])) {
			// Set the current brand
			$ss->brand = $path[2];
			// Get the current brand
			$ss->query = 'SELECT `brand`.`seo_brand`, `brand`.`brand`, `model`.`modelid`, `model`.`seo_model`, `model`.`model` FROM `model` JOIN `brand` ON `model`.`brandid` = `brand`.`brandid` WHERE `brand`.`brand` LIKE "'.$ss->brand.'";';
			// SQL query for current brand
			$ss->get_brand = $wpdb->get_results($ss->query);
			// To check whether brand exists in database
			$check_brand = false;
			foreach ($ss->get_brand as $result) {
				$brandcheck = $result->brand;
				if (($brandcheck) && ($brandcheck == $ss->brand)) {
					$check_brand = true;
				}
			}
			// If doesn't exist then return
			if (!$check_brand) {
				header('Location: /brand/');
				exit;
			}
			// Passed brand check and continue to load all models of selected brand
			$ss->get_brand();

		// List all brands
		}
		elseif (!$path[2]) {
			$ss->page = 'allbrand';
			$ss->query = 'SELECT `brand`.`seo_brand`, `brand`.`brand`, `model`.`model` FROM `model` JOIN `brand` ON `model`.`brandid` = `brand`.`brandid`;';
			$ss->get_brand = $wpdb->get_results($ss->query);
			
			$ss->get_brand();
		}
		// Else return to brands main page
		else {
			header('Location: /brand/');
			exit;
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
	
	// Model page
	elseif ($ss->action == 'model') {
	
		// List secret codes of selected model
		if (($path[4]) && ($path[4] == 'secret-code') && (!$path[5])) {
			$ss->page = 'secret';
			
			// Set the current brand and model
			$ss->brand = $path[2];
			$ss->model = $path[3];
			
			// Get the secret code
			$ss->query = 'SELECT `brand`.`brand`, `secret`.`code`, `secret`.`remarks` FROM `secret` JOIN `brand` ON `secret`.`brandid` = `brand`.`brandid`;';
			$ss->query2 = 'SELECT `model`.`model`, `model`.`seo_model`, `model`.`system`, `brand`.`brand` FROM `model` JOIN `brand` ON `model`.`brandid` = `brand`.`brandid` WHERE `model` LIKE "'.$ss->model.'";';
			
			// SQL query for secret code
			$ss->get_secret = $wpdb->get_results($ss->query);
			$ss->get_model = $wpdb->get_row($ss->query2);
			
			// To check whether model exists in database
			$check_brand = false;
			$check_model = false;
			$brandcheck = $ss->get_model->brand;
			$modelcheck = $ss->get_model->model;
			if (($brandcheck) && ($brandcheck == $ss->brand)) {
				$check_brand = true;
			}
			if (($modelcheck) && ($modelcheck == $ss->model)) {
				$check_model = true;
			}
			// If doesn't exist then return
			if ((!$check_brand) || (!$check_model)) {
				header('Location: /model/');
				exit;
			}
			
			// Passed model check and continue to load secret codes of selected model
			$ss->get_secret();
		}
		
		// Show firmware of selected model
		elseif (($path[4]) && ($path[4] == 'firmware') && (!$path[5])) {
			$ss->page = 'firmware';
			
			// Set the current brand and model
			$ss->brand = $path[2];
			$ss->model = $path[3];
			
			// Get the firmware
			$ss->query = 'SELECT `model`.`model`, `model`.`seo_model`, `model`.`system`, `brand`.`brand`, `brand`.`url` FROM `model` JOIN `brand` ON `model`.`brandid` = `brand`.`brandid` WHERE `model` LIKE "'.$ss->model.'";';
			
			// SQL query for firmware
			$ss->get_firmware = $wpdb->get_row($ss->query);
			
			// To check whether model exists in database
			$check_brand = false;
			$check_model = false;
			$brandcheck = $ss->get_firmware->brand;
			$modelcheck = $ss->get_firmware->model;
			if (($brandcheck) && ($brandcheck == $ss->brand)) {
				$check_brand = true;
			}
			if (($modelcheck) && ($modelcheck == $ss->model)) {
				$check_model = true;
			}
			// If doesn't exist then return
			if ((!$check_brand) || (!$check_model)) {
				header('Location: /model/');
				exit;
			}
			
			// Passed model check and continue to load firmware of selected model
			$ss->get_firmware();
		}
		
		
		// List specs of selected model
		elseif (($path[2]) && (!$path[4])) {
			
			// Set the current brand (for prev and next button purposes)
			$ss->brand = $path[2];
			
			// Set the current model
			$ss->model = $path[3];

			// Get the current spec
			$ss->query = 'SELECT * FROM `model` JOIN `brand` ON `model`.`brandid` = `brand`.`brandid` WHERE `model` LIKE "'.$ss->model.'";';
			
			// SQL query for current spec
			$ss->get_spec = $wpdb->get_row($ss->query, ARRAY_A);
			// To check whether model exists in database
			$check_brand = false;
			$check_model = false;
			$brandcheck = $ss->get_spec['brand'];
			$modelcheck = $ss->get_spec['model'];
			if (($brandcheck) && ($brandcheck == $ss->brand)) {
				$check_brand = true;
			}
			if (($modelcheck) && ($modelcheck == $ss->model)) {
				$check_model = true;
			}
			// If doesn't exist then return
			if ((!$check_brand) || (!$check_model)) {
				header('Location: /model/');
				exit;
			}
			// Passed brand check and continue to load all models of selected brand
			$ss->get_specs();
		}
		
		// List all models
		elseif (!$path[2]) {
			$ss->page = 'allmodel';
			$ss->query = 'SELECT `brand`.`brand`, `model`.`model`, `model`.`seo_model` FROM `model` JOIN `brand` ON `model`.`brandid` = `brand`.`brandid`;';
			$ss->get_model();
		}
		// Else return to models main page
		else {
			header('Location: /model/');
			exit;
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
	// Main page
	elseif (!$ss->action) {
		// require('wp-blog-header.php');

		$model_max = 3892;
		for ($i=0;$i<20;$i++) {
			$rand[$i] = rand(1,$model_max);
		}
		$ss->query = 'SELECT `model`.`model`, `model`.`seo_model`, `brand`.`brand`
		FROM `model` JOIN `brand`
		ON `model`.`brandid` = `brand`.`brandid`
		WHERE `model`.`modelid` = "'.$rand[0].'"
		OR `model`.`modelid` = "'.$rand[1].'"
		OR `model`.`modelid` = "'.$rand[2].'"
		OR `model`.`modelid` = "'.$rand[3].'"
		OR `model`.`modelid` = "'.$rand[4].'"
		OR `model`.`modelid` = "'.$rand[5].'"
		OR `model`.`modelid` = "'.$rand[6].'"
		OR `model`.`modelid` = "'.$rand[7].'"
		OR `model`.`modelid` = "'.$rand[8].'"
		OR `model`.`modelid` = "'.$rand[9].'"
		OR `model`.`modelid` = "'.$rand[10].'"
		OR `model`.`modelid` = "'.$rand[11].'"
		OR `model`.`modelid` = "'.$rand[12].'"
		OR `model`.`modelid` = "'.$rand[13].'"
		OR `model`.`modelid` = "'.$rand[14].'"
		OR `model`.`modelid` = "'.$rand[15].'"
		OR `model`.`modelid` = "'.$rand[16].'"
		OR `model`.`modelid` = "'.$rand[17].'"
		OR `model`.`modelid` = "'.$rand[18].'"
		OR `model`.`modelid` = "'.$rand[19].'"
		LIMIT 20;';
		//$ss->query = 'SELECT `model`.`model`, `model`.`seo_model`, `brand`.`brand` FROM `model` JOIN `brand` ON `model`.`brandid` = `brand`.`brandid` ORDER BY RAND() LIMIT 20;';
		$ss->get_random();
	}

}

function ss_func() {
	global $ss;
	
	?>
	<!-- Ads -->
	<div class="row">
		<div class="small-12 columns">
			<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<!-- secretspecs-top -->
			<ins class="adsbygoogle"
				 style="display:block"
				 data-ad-client="ca-pub-0047723350429793"
				 data-ad-slot="7551038658"
				 data-ad-format="auto"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script>
		</div>
	</div>
	<br />
	<?php
	
	if (!$ss->action) {
		$ss->print_main();
	}
	elseif ($ss->action == 'brand') {
		$ss->print_brand();
	}
	elseif ($ss->action == 'model') {
		if ($ss->page == 'secret') {
			$ss->print_secret();
		}
		elseif ($ss->page == 'firmware') {
			$ss->print_firmware();
		}
		elseif ($ss->page == 'allmodel') {
			$ss->print_model();
		} else {
			$ss->print_specs();
		}
	}
	
	?>
	<!-- Ads -->
	<br />
	<div class="row">
		<div class="small-12 columns">
			<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
			<!-- secretspecs-bottom -->
			<ins class="adsbygoogle"
				 style="display:block"
				 data-ad-client="ca-pub-0047723350429793"
				 data-ad-slot="1504505059"
				 data-ad-format="auto"></ins>
			<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
			</script>
		</div>
	</div>
	<?php
}


class SS {

	public $action;
	public $page;
	public $title;
	public $seo_title;
	public $seo_desc;
	public $seo_keywords;
	public $query;
	public $query2;				// second query on secret codes page to get model name
	public $brand;
	public $get_brand;
	public $get_model;
	public $get_spec;
	public $get_secret;
	public $get_firmware;
	public $model;
	public $modelid;
	public $url;
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
	public $field_system;			// 
	public $field_display_reso;		// 
	public $field_display_size;		//
	public $field_processor;		// 
	public $field_internal_storage;	// 
	public $field_external_storage;	// variables for auto generated paragraphs
	public $field_system_ram;		// in model specs page
	public $field_backcamera_pixel;	//
	public $field_frontcamera_pixel;//
	public $models_arr;			// sql results for models widget and prev/next button in model page
	public $model_count;		// model count for models widget and prev/next button in model page
	public $model_current;		// current model for models widget and prev/next button in model page
	public $button_prev;		// Link for prev model button
	public $button_next;		// Link for next model button
	public $button_prevtitle;
	public $button_nexttitle;



	public function __construct() {
		$this->seo_title = '';
		$this->seo_desc = '';
		$this->seo_keywords = '';
	}
	
	// SQL query for random models
	public function get_random() {
		global $wpdb;
		$this->get_random = $wpdb->get_results($this->query);
	}

	// SQL query for selected brand
	public function get_brand() {
		global $wpdb;
		// $this->get_brand = $wpdb->get_results($this->query);
		$this->seo_brand = $this->get_brand[0]->seo_brand;
		
		// Set page title and description
		if ($this->get_brand[0]->modelid) {
			$this->title = $this->seo_brand;
			$this->seo_title = $this->seo_brand." devices";
			$this->seo_desc = $this->seo_brand.' devices full specifications, in-depth hardware informations and secret codes.';
			$this->seo_keywords = $this->seo_brand.", Specifications, Secret Codes";
		}
		else {
			$this->title = "Brand";
			$this->seo_title = "Listing of devices by Brand";
			$this->seo_desc = "Secret Specs is the ultimate website for phone and mobile device full specifications, in-depth hardware informations and secret codes. Listing all mobile device brands.";
			$this->seo_keywords = "Samsung, Sony, HTC, Huawei, Xiaomi";
		}
	}

	// SQL query to list all models
	public function get_model() {
		global $wpdb;
		
		$this->get_model = $wpdb->get_results($this->query);
		
		// Set page title and description
		$this->title = "Model";
		$this->seo_title = "Listing of devices by Model";
		$this->seo_desc = "Secret Specs is the ultimate website for phone and mobile device full specifications, in-depth hardware informations and secret codes. Listing all device models.";
		$this->seo_keywords = "Samsung, Sony, HTC, Huawei, Xiaomi";
	}

	// SQL query for selected model
	public function get_specs() {
		global $wpdb;

		
		$result = $this->get_spec;
		$this->system = $result['system'];										// To get these variables
		$this->seo_model = $result['seo_model'];								// for seo_title, seo_desc
		$this->field_build_id = $this->get_subfield($this->system, 'Build ID');	// and seo_keywords below
		$this->title = $this->seo_model." - Specifications";
		$this->seo_title = $this->seo_model." | ".$this->field_build_id." | Specifications";
		$this->seo_desc = $this->seo_model.", ".$this->field_build_id.", Android With Full Specifications, In-Depth Hardware Informations Including System, Display, Processor, Memory, Back Camera, Graphic Modes, Sensors, Codecs, Features.";
		$this->seo_keywords = ucwords($this->brand).", ".$this->seo_model.", ".$this->field_build_id;
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
		$this->field_system = $this->get_subfield($this->system, 'Android Version');			// 
		$this->field_display_reso = $this->get_subfield($this->display, 'Size');				// 
		$this->field_display_size = $this->get_subfield($this->display, 'Physical Size');		//
		$this->field_processor = $this->get_subfield($this->processor, 'Processor');			// To get these variables
		$this->field_internal_storage = $this->get_subfield($this->memory, 'Internal Storage');	// for auto generated
		$this->field_external_storage = $this->get_subfield($this->memory, 'External Storage');	// paragraph
		$this->field_system_ram = $this->get_subfield($this->memory, 'System RAM');				//
		$this->field_backcamera_pixel = $this->get_subfield($this->backcamera, 'subtitle');		//
		$this->field_frontcamera_pixel = $this->get_subfield($this->frontcamera, 'subtitle');	//
		
		//to get current model number in the database
		$this->models_arr = $wpdb->get_results( "SELECT `model`.`model`, `model`.`seo_model` FROM `model` JOIN `brand` ON `model`.`brandid` = `brand`.`brandid` WHERE `brand`.`brand` = '$this->brand'" );

		$checkmodel = 0;
		foreach ($this->models_arr as $model) {
			if ($model->model != $this->model) {
				$checkmodel++;
			}
			else {
				$this->model_current = $checkmodel;
			}
		}

		//to get link for prev model and next model
		$this->model_count = $wpdb->num_rows;
		if ($this->model_count == 1) {	// When there is only one model for this brand
			$prevmodel = 0;
			$nextmodel = 0;
		}
		elseif ($this->model_current == 0) {
			$prevmodel = $this->model_count - 1;
			$nextmodel = $this->model_current + 1;
		}
		elseif ($this->model_current == $this->model_count - 1) {
			$prevmodel = $this->model_current - 1;
			$nextmodel = 0;
		}
		else {
			$prevmodel = $this->model_current - 1;
			$nextmodel = $this->model_current + 1;
		}
		$this->button_prev = "//secretspecs.com/model/".$this->brand."/".$this->models_arr[$prevmodel]->model."/";
		$this->button_next = "//secretspecs.com/model/".$this->brand."/".$this->models_arr[$nextmodel]->model."/";
		$this->button_prevtitle = $this->models_arr[$prevmodel]->seo_model;
		$this->button_nexttitle = $this->models_arr[$nextmodel]->seo_model;
	}
	
	// SQL query for secret code
	public function get_secret() {
		global $wpdb;
		
		//$this->get_model = $wpdb->get_row($this->query2);
		//$this->get_secret = $wpdb->get_results($this->query);
		$this->seo_model = $this->get_model->seo_model;
		$this->system = $this->get_model->system;
		$this->field_build_id = $this->get_subfield($this->system, 'Build ID');
		
		// Set page title and description
		$this->title = $this->seo_model." - Secret Codes";
		$this->seo_title = $this->seo_model." | ".$this->field_build_id." | Secret Codes";
		$this->seo_desc = $this->seo_model." - ".$this->field_build_id." software and hardward infomation including IMEI, Factory Reset, GPS Test, MAC Address, Debug, LCD Test, Audio Test, Sensor Test, Firmware Info";
		$this->seo_keywords = $this->seo_keywords = ucwords($this->brand).", ".$this->seo_model.", ".$this->field_build_id.", Secret Codes";
	}
	
	// SQL query for firmware
	public function get_firmware() {
		global $wpdb;
		
		$this->seo_model = $this->get_firmware->seo_model;
		$this->url = $this->get_firmware->url;
		$this->system = $this->get_firmware->system;
		$this->field_build_id = $this->get_subfield($this->system, 'Build ID');
		
		// Set page title and description
		$this->title = $this->seo_model." - Latest Firmware";
		$this->seo_title = $this->seo_model." | ".$this->field_build_id." | Latest Firmware";
		$this->seo_desc = "Download the latest drivers, manuals, firmware and software for your ".$this->seo_model." - ".$this->field_build_id;
		$this->seo_keywords = $this->seo_keywords = ucwords($this->brand).", ".$this->seo_model.", ".$this->field_build_id.", Firmware";
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
	public function get_modelspecs($arr_field, $col1, $col2, $col3, $col4) {
		$checktitle = false;
		if ($arr_field) {
			$string = '<div class="row">';
			$temptitle = "";
			foreach ($arr_field as $value) {
				if ($value[0] == "subtitle") {
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
	
	// Function to search for a field in SQL database that is stored in json format
	public function get_subfield($arr_field, $search_field) {
		if ($arr_field) {
			$ss_array = json_decode($arr_field, true);
			foreach ($ss_array as $value) {
				if ($value[0] == $search_field) {
					$found_field = $value[1];
					return $found_field;
				}
			}
		}
	}

	// Function to print main page
	public function print_main() {
		/**
		$searchstr1 = "/search/?q";
		$searchstr2 = "mainsearch-text";
		echo "
		<div class='row'>
			<br />
			<div class='panel callout radius'>
				<h2>Search the world of radio</h2>
				<form action='/search/' onsubmit='location.href=$searchstr1 + document.getElementById($searchstr2).value; return false;'>
				<div class='row'>
					<div class='large-12 small-12 columns'>
					<div class='small-10 columns'>
						<input id='mainsearch-text' type='text' name='q' autocomplete='off' class='form-control input-sm ng-pristine ng-valid' placeholder='Search'>
					</div>
					<div class='small-2 columns'>
						<a class='button postfix' onclick='location.href=$searchstr1 + document.getElementById($searchstr2).value;'>Go</a>
					</div>
					</div>
				</div>
				</form>
				<br /><br />
				<p>Search from tens of thousands of radio stations from all over the world playing only the best music from every genre.</p>
			</div>
		</div>
		";
		**/
		/**
		$args = array( 'numberposts' => 6, 'post_status'=>"publish",'post_type'=>"post",'orderby'=>"post_date");
		$postslist = get_posts( $args );
		echo '<ul id="latest_posts">';
		foreach ($postslist as $post) :  setup_postdata($post); ?> 
			<li><strong><?php the_date(); ?></strong><br />
			<a href="<?php the_permalink(); ?>" title="<?php the_title();?>"> <?php the_title(); ?></a>
			</li>
		<?php endforeach; ?>
		 </ul>
		 <?php
		**/
		echo '
			<div class="row">
			<div class="small-12 columns">
				<div class="row">
				<div class="small-12 medium-6 columns">
					<h4>Popular Brands</h4>
					<hr>
					<h5 class="subheader">
					<a href="//secretspecs.com/brand/acer/" title="Acer">Acer</a><br />
					<a href="//secretspecs.com/brand/asus/" title="Asus">Asus</a><br />
					<a href="//secretspecs.com/brand/casio/" title ="Casio">Casio</a><br />
					<a href="//secretspecs.com/brand/celkon/" title="Celkon">Celkon</a><br />
					<a href="//secretspecs.com/brand/gigabyte/" title="Gigabyte">Gigabyte</a><br />
					<a href="//secretspecs.com/brand/huawei/" title="Huawei">Huawei</a><br />
					<a href="//secretspecs.com/brand/htc/" title="HTC">HTC</a><br />
					<a href="//secretspecs.com/brand/lenovo/" title="Lenovo">Lenovo</a><br />
					<a href="//secretspecs.com/brand/lg/" title="LG">LG</a><br />
					<a href="//secretspecs.com/brand/maxwest/" title="Maxwest">Maxwest</a><br />
					<a href="//secretspecs.com/brand/micromax/" title="Micromax">Micromax</a><br />
					<a href="//secretspecs.com/brand/motorola/" title="Motorola">Motorola</a><br />
					<a href="//secretspecs.com/brand/oppo/" title="Oppo">Oppo</a><br />
					<a href="//secretspecs.com/brand/pantech/" title="Pantech">Pantech</a><br />
					<a href="//secretspecs.com/brand/samsung/" title="Samsung">Samsung</a><br />
					<a href="//secretspecs.com/brand/sony/" title="Sony">Sony</a><br />
					<a href="//secretspecs.com/brand/spice/" title="Spice">Spice</a><br />
					<a href="//secretspecs.com/brand/toshiba/" title="Toshiba">Toshiba</a><br />
					<a href="//secretspecs.com/brand/xiaomi/" title="Xiaomi">Xiaomi</a><br />
					<a href="//secretspecs.com/brand/zte/" title="ZTE">ZTE</a><br />
					</h5>
				</div>
				<div class="small-12 medium-6 columns">
					<h4>Random Models</h4>
					<hr>
					<h5 class="subheader">
		';
		foreach ($this->get_random as $row) {
			echo "<a href=\"//secretspecs.com/model/$row->brand/$row->model/\" title=\"$row->seo_model\">$row->seo_model</a><br />";
		}
		echo "
			</h5>
			</div>
			</div></div></div>
			<br />
		";
	}
	
	// Function to print all brand
	public function print_brand() {
		echo "
			<hr>
			<div class=\"row\">
			<div class=\"small-12 columns\">
			<div class=\"row\">
		";
		
		$dup_brand = $this->get_brand[0]->brand;	// To get distinct brand
		$current_count = 0;							// To count number of models for each brand
		$current_brand = $this->get_brand[0]->brand;
		$current_seobrand = $this->get_brand[0]->seo_brand;
		$array_count = COUNT($this->get_brand);	// To check for last
		$i = 0;									// item in array
		foreach ($this->get_brand as $brand) {
			// List all brand
			if ($this->page == 'allbrand') {
				$i++;
				if ($i < $array_count) {
					if ($brand->brand != $dup_brand) {
						echo "<div class=\"small-12 medium-6 columns\" style=\"height:25pt\">";
						echo "<a href=\"//secretspecs.com/brand/$current_brand/\" title=\"$current_seobrand\">$current_seobrand ($current_count)</a>";
						echo "</div>";
						$dup_brand = $brand->brand;
						$current_count = 1;
					} else {
						$current_count++;
					}
					$current_brand = $brand->brand;
					$current_seobrand = $brand->seo_brand;
				} else {
					if ($brand->brand != $dup_brand) {
						echo "<div class=\"small-12 medium-6 columns\" style=\"height:25pt\">";
						echo "<a href=\"//secretspecs.com/brand/$current_brand/\" title=\"$current_seobrand\">$current_seobrand ($current_count)</a>";
						echo "</div>";
						$current_count = 1;
					}
					echo "<div class=\"small-12 medium-6 columns end\" style=\"height:25pt\">";
					echo "<a href=\"//secretspecs.com/brand/$brand->brand/\" title=\"$brand->seo_brand\">$brand->seo_brand ($current_count)</a>";
					echo "</div>";
				}
			// List models for selected brand
			} else {
				$i++;
				if ($i < $array_count) { echo "<div class=\"small-12 medium-6 columns\" style=\"height:25pt\">"; }
				else { echo "<div class=\"small-12 medium-6 columns end\" style=\"height:25pt\">"; }
				echo "<a href=\"//secretspecs.com/model/$brand->brand/$brand->model/\" title=\"$brand->seo_model\">$brand->seo_model</a>";
				echo "</div>";
			}
		}
		echo "</div></div></div>";
	}
	
	public function print_model() {
		echo "
			<hr>
			<div class=\"row\">
			<div class=\"small-12 columns\">
			<div class=\"row\">
		";
		$array_count = COUNT($this->get_model);	// To check for last
		$i = 0;									// item in array
		foreach ($this->get_model as $model) {
			// List all models
			$i++;
			if ($i < $array_count) { echo "<div class=\"small-12 medium-6 columns\" style=\"height:25pt\">"; }
			else { echo "<div class=\"small-12 medium-6 columns end\" style=\"height:25pt\">"; }
			echo "<a href=\"//secretspecs.com/model/$model->brand/$model->model/\" title=\"$model->seo_model\">$model->seo_model</a>";
			echo "</div>";
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
			<div class=\"row\">
			<div class=\"small-12 columns\">
			<div class=\"left\">
			<a class=\"small button\" href=\"$this->button_prev\" title=\"$this->button_prevtitle\"><i class=\"fa fa-chevron-circle-left fa-lg\"></i>&nbsp; Prev</a>
			</div>
			<div class=\"right\">
			<a class=\"small button\" href=\"$this->button_next\" title=\"$this->button_nexttitle\">Next &nbsp;<i class=\"fa fa-chevron-circle-right fa-lg\"></i></a>
			</div>
			</div></div>
			<br />
			<a class=\"button\" href=\"//secretspecs.com/model/$this->brand/$this->model/secret-code/\" title=\"Secret Codes for $this->seo_model\">$this->seo_model Secret Codes</a><br />
			<a class=\"button\" href=\"//secretspecs.com/model/$this->brand/$this->model/firmware/\" title=\"Firmware for $this->seo_model\">$this->seo_model Firmware</a><br />
		";
		// Review - System, Processor, Display and Memory
		echo "
			<br />
			<h2>Review of $this->seo_model</h2>
			The $this->seo_model is runnning Android $this->field_system, comes with a $this->field_display_size touchscreen display with a resolution of $this->field_display_reso, and is powered by $this->field_processor.
			The RAM measures at $this->field_system_ram. The $this->seo_model packs $this->field_internal_storage of internal storage
		";
		if (($this->field_external_storage) && ($this->field_external_storage != "")) {
			echo " and supports expendable storage of up to $this->field_external_storage.";
		} else {
			echo ".";
		}
		// Review - Camera
		// - If both front and back camera exist
		if (($this->field_backcamera_pixel) && ($this->field_backcamera_pixel != "") && ($this->field_frontcamera_pixel) && ($this->field_frontcamera_pixel != "")) {
			echo "
				<br /><br />
				It came with a $this->field_backcamera_pixel primary camera on the rear and a $this->field_frontcamera_pixel front shooter for selfies.
			";
		}
		// - If only back camera exist
		elseif (($this->field_backcamera_pixel) && ($this->field_backcamera_pixel != "")) {
			echo "
				<br /><br />
				It came with a $this->field_backcamera_pixel camera on the rear.
			";
		}
		// - If only front camera exist
		elseif (($this->field_frontcamera_pixel) && ($this->field_frontcamera_pixel != "")) {
			echo "
				<br /><br />
				It came with a $this->field_frontcamera_pixel front camera.
			";
		}
		echo "
			<br /><br />
			<h2>Full Specifications of $this->seo_model</h2>
			<div class=\"row\">
			<div class=\"small-12 columns\">
		";

		// Display specs in accordion
		echo "<ul class=\"accordion\" data-accordion>";
		foreach ($accordion_tab as $tab) {
			$link = '#'.$tab[1];
			if ($tab[1] == 'system') { $accordion = $this->get_modelspecs($ss_system, 6, 6, 3, 9); }
			if ($tab[1] == 'display') { $accordion = $this->get_modelspecs($ss_display, 6, 6, 4, 8); }
			if ($tab[1] == 'processor') { $accordion = $this->get_modelspecs($ss_processor, 6, 6, 3, 9); }
			if ($tab[1] == 'memory') { $accordion = $this->get_modelspecs($ss_memory, 7, 5, 4, 8); }
			if ($tab[1] == 'backcamera') { $accordion = $this->get_modelspecs($ss_backcamera, 7, 5, 6, 6); }
			if ($tab[1] == 'frontcamera') { $accordion = $this->get_modelspecs($ss_frontcamera, 7, 5, 6, 6); }
			if ($tab[1] == 'opengl11') { $accordion = $this->get_modelspecs($ss_opengl11, 8, 4, 4, 8); }
			if ($tab[1] == 'opengl1x') { $accordion = $this->get_modelspecs($ss_opengl1x, 8, 4, 4, 8); }
			if ($tab[1] == 'opengl20') { $accordion = $this->get_modelspecs($ss_opengl20, 7, 5, 4, 8); }
			if ($tab[1] == 'opengl30') { $accordion = $this->get_modelspecs($ss_opengl30, 7, 5, 4, 8); }
			if ($tab[1] == 'graphicmodes') { $accordion = $this->get_modelspecs($ss_graphicmodes, 7, 5, 6, 6); }
			if ($tab[1] == 'sensors') { $accordion = $this->get_modelspecs($ss_sensors, 5, 7, 4, 8); }
			if ($tab[1] == 'codecs') { $accordion = $this->get_modelspecs($ss_codecs, 6, 6, 4, 8); }
			if ($tab[1] == 'features') { $accordion = $this->get_modelspecs($ss_features, 6, 6, 4, 8); }
			echo "
				<li class=\"accordion-navigation\">
				<a href=\"$link\" aria-controls=$tab[1]>$tab[0]</a>
			";
			if ($tab[1] == 'system') {
				echo "<div id=$tab[1] class=\"content active\" role=\"tabpanel\">";
			}
			else {
				echo "
				<div id=$tab[1] class=\"content\" role=\"tabpanel\">";
			}
			echo "$accordion</div></li>";
		}
		echo "</ul>";
		echo "</div></div>";
	}
	
	public function print_secret() {
		echo "
			<hr>
			<div class=\"row\">
			<div class=\"small-12 columns\">
			<a class=\"button\" href=\"//secretspecs.com/model/$this->brand/$this->model/\" title=\"Specifications for $this->seo_model\">$this->seo_model Full Specifications</a><br />
			<a class=\"button\" href=\"//secretspecs.com/model/$this->brand/$this->model/firmware/\" title=\"Firmware for $this->seo_model\">$this->seo_model Firmware</a><br />
			<div class=\"row\">
		";
		
		// List all generic secret codes
		echo "
			<div class=\"small-12 columns\">
			<h2>Generic Secret Codes</h2>
			</div>
		";
		$checkbrand = "";
		foreach ($this->get_secret as $secret) {
			if ($secret->brand == 'generic') {
				echo "<div class=\"small-12 columns\">";
				echo "<div class=\"panel\">";
				echo "<h5><strong>".$secret->code."</strong></h5>";
				echo $secret->remarks;
				echo "</div></div>";
			}
			if ($secret->brand == $this->brand) {		// To check whether there are secret codes for this brand
				$checkbrand = true;
			}
		}
		if ($checkbrand) {
		
			// List all secret codes for this brand
			$brand = ucwords($this->brand);
			echo "
				<br />
				<div class=\"small-12 columns\">
				<h2>$brand Secret Codes</h2>
				</div>
			";
			foreach ($this->get_secret as $secret) {
				if ($secret->brand == $this->brand) {
					echo "<div class=\"small-12 columns\">";
					echo "<div class=\"panel\">";
					echo "<h5><strong>".$secret->code."</strong></h5>";
					echo $secret->remarks;
					echo "</div></div>";
				}
			}
		}
		echo "</div></div></div>";
	}
	
	public function print_firmware() {
		echo "
			<hr>
			<div class=\"small-12 coulmns\">
			<a class=\"button\" href=\"//secretspecs.com/model/$this->brand/$this->model/\" title=\"Specifications for $this->seo_model\">$this->seo_model Full Specifications</a><br />
			<a class=\"button\" href=\"//secretspecs.com/model/$this->brand/$this->model/secret-code/\" title=\"Secret Codes for $this->seo_model\">$this->seo_model Secret Codes</a><br />
			<div class=\"row\">
		";
		
		// Display firmware
		echo "
			<div class=\"small-12 columns\">
				<h2>Firmware for $this->seo_model</h2>
			</div>
			<div class=\"small-12 columns\">
				<ul>
				<li><a href=\"$this->url\" title=\"$this->seo_model\" target=\"_blank\">Latest $this->seo_model firmware</a></li>
				</ul>
			</div>
		";

		echo "</div></div>";
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
		if ($ss->action) {
			
			echo "
				<div class=\"row\">
				<div class=\"small-12 columns\">
				<div class=\"panel\">
				<h4>Popular Brands</h4>
				<br />
				<ul class=\"small-block-grid-2\">
					<li><a href=\"//secretspecs.com/brand/htc/\" title=\"HTC\"><img src=\"//secretspecs.com/wp-content/uploads/brand/htc.gif\" alt=\"htc\"></a></li>
					<li><a href=\"//secretspecs.com/brand/samsung/\" title=\"Samsung\"><img src=\"//secretspecs.com/wp-content/uploads/brand/samsung.gif\" alt=\"samsung\"></a></li>
					<li><a href=\"//secretspecs.com/brand/lg/\" title=\"LG\"><img src=\"//secretspecs.com/wp-content/uploads/brand/lg.gif\" alt=\"lg\"></a></li>
					<li><a href=\"//secretspecs.com/brand/xiaomi/\" title=\"Xiaomi\"><img src=\"//secretspecs.com/wp-content/uploads/brand/xiaomi.gif\" alt=\"xiaomi\"></a></li>
					<li><a href=\"//secretspecs.com/brand/asus/\" title=\"Asus\"><img src=\"//secretspecs.com/wp-content/uploads/brand/asus.gif\" alt=\"asus\"></a></li>
					<li><a href=\"//secretspecs.com/brand/sony/\" title=\"Sony\"><img src=\"//secretspecs.com/wp-content/uploads/brand/sony.gif\" alt=\"sony\"></a></li>
					<li><a href=\"//secretspecs.com/brand/acer/\" title=\"Acer\"><img src=\"//secretspecs.com/wp-content/uploads/brand/acer.gif\" alt=\"acer\"></a></li>
					<li><a href=\"//secretspecs.com/brand/lenovo/\" title=\"Lenovo\"><img src=\"//secretspecs.com/wp-content/uploads/brand/lenovo.gif\" alt=\"lenovo\"></a></li>
					<li><a href=\"//secretspecs.com/brand/gigabyte/\" title=\"Gigabyte\"><img src=\"//secretspecs.com/wp-content/uploads/brand/gigabyte.gif\" alt=\"gigabyte\"></a></li>
					<li><a href=\"//secretspecs.com/brand/oppo/\" title=\"Oppo\"><img src=\"//secretspecs.com/wp-content/uploads/brand/oppo.gif\" alt=\"oppo\"></a></li>
					<li><a href=\"//secretspecs.com/brand/celkon/\" title=\"Celkon\"><img src=\"//secretspecs.com/wp-content/uploads/brand/celkon.gif\" alt=\"celkon\"></a></li>
					<li><a href=\"//secretspecs.com/brand/casio/\" title=\"Casio\"><img src=\"//secretspecs.com/wp-content/uploads/brand/casio.gif\" alt=\"casio\"></a></li>
					<li><a href=\"//secretspecs.com/brand/pantech/\" title=\"Pantech\"><img src=\"//secretspecs.com/wp-content/uploads/brand/pantech.gif\" alt=\"pantech\"></a></li>
					<li><a href=\"//secretspecs.com/brand/maxwest/\" title=\"Maxwest\"><img src=\"//secretspecs.com/wp-content/uploads/brand/maxwest.gif\" alt=\"maxwest\"></a></li>
					<li><a href=\"//secretspecs.com/brand/micromax/\" title=\"Micromax\"><img src=\"//secretspecs.com/wp-content/uploads/brand/micromax.gif\" alt=\"micromax\"></a></li>
					<li><a href=\"//secretspecs.com/brand/huawei/\" title=\"Huawei\"><img src=\"//secretspecs.com/wp-content/uploads/brand/huawei.gif\" alt=\"huawei\"></a></li>
					<li><a href=\"//secretspecs.com/brand/toshiba/\" title=\"Toshiba\"><img src=\"//secretspecs.com/wp-content/uploads/brand/toshiba.gif\" alt=\"toshiba\"></a></li>
					<li><a href=\"//secretspecs.com/brand/motorola/\" title=\"Motorola\"><img src=\"//secretspecs.com/wp-content/uploads/brand/motorola.gif\" alt=\"motorola\"></a></li>
					<li><a href=\"//secretspecs.com/brand/zte/\" title=\"ZTE\"><img src=\"//secretspecs.com/wp-content/uploads/brand/zte.gif\" alt=\"zte\"></a></li>
					<li><a href=\"//secretspecs.com/brand/spice/\" title=\"Spice\"><img src=\"//secretspecs.com/wp-content/uploads/brand/spice.gif\" alt=\"spice\"></a></li>
				</ul>
				</div></div></div>
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

// Widget to list models by brand
// Creating the widget
class model_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'model_widget', // Base ID
			__('#Model Widget', 'text_domain'), // Widget name
			array( 'description' => __( 'Secret Specs widget to list models by brand', 'text_domain' ), ) // Widget description
		);
	}

// Front-end
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

// Before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] .'<h5>'. $title .'</h5>'. $args['after_title'];

// Display output
		global $ss;
		
		if (($ss->action == 'model') && ($ss->brand) && ($ss->models_arr)) {
			$brand = ucwords($ss->brand);
			echo "
				<div class=\"row\">
				<div class=\"small-12 columns\">
				<div class=\"panel\">
				<h4>Other Models of $brand</h4>
				<br />
			";
			if ($ss->model_count <= 10) {	// When there are not more than 10 models for this brand
				foreach ($ss->models_arr as $model) {
					echo "<a href=\"//secretspecs.com/model/$ss->brand/$model->model/\" title=\"$model->seo_model\">$model->seo_model</a><br />";
				}
			}
			elseif ($ss->model_current < 5) {	// When the current model is within the first 5 in the list
				for ($i = 0; $i < 10; $i++) {
					$model = $ss->models_arr[$i]->model;
					$seo_model = $ss->models_arr[$i]->seo_model;
					echo "<a href=\"//secretspecs.com/model/$ss->brand/$model/\" title=\"$seo_model\">$seo_model</a><br />";
				}
			}
			elseif ((($ss->model_count) - $ss->model_current) < 5) {	// When the current model is within the last 5 in the list
				for ($i = $ss->model_count - 10; $i < $ss->model_count; $i++) {
					$model = $ss->models_arr[$i]->model;
					$seo_model = $ss->models_arr[$i]->seo_model;
					echo "<a href=\"//secretspecs.com/model/$ss->brand/$model/\" title=\"$seo_model\">$seo_model</a><br />";
				}
			}
			else {
				// Display 5 models before current
				for ($i = $ss->model_current - 5; $i < $ss->model_current; $i++) {
					$model = $ss->models_arr[$i]->model;
					$seo_model = $ss->models_arr[$i]->seo_model;
					echo "<a href=\"//secretspecs.com/model/$ss->brand/$model/\" title=\"$seo_model\">$seo_model</a><br />";
				}
				// Display 5 models after current
				for ($i = $ss->model_current + 1; $i < $ss->model_current + 6; $i++) {
					$model = $ss->models_arr[$i]->model;
					$seo_model = $ss->models_arr[$i]->seo_model;
					echo "<a href=\"//secretspecs.com/model/$ss->brand/$model/\" title=\"$seo_model\">$seo_model</a><br />";
				}
			}
			/**
			elseif ($this->model_current == $this->model_count - 1) {
				$prevmodel = $this->model_current - 1;
				$nextmodel = 0;
			}
			else {
				$prevmodel = $this->model_current - 1;
				$nextmodel = $this->model_current + 1;
			}
			
			foreach ($ss->models_arr as $model) {
				echo "<a href='/model/$ss->brand/$model->model/'>$model->seo_model</a><br />";
			}
			**/
			echo "
				</div></div></div>
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
} // Class model_widget ends here


// Register and load the widget
function load_widget() {
    register_widget( 'brand_widget' );
    register_widget( 'model_widget' );
}


add_shortcode( 'sspecs', 'ss_func' );

add_action( 'widgets_init', 'load_widget' );
add_action( 'init', 'ss_seo_loader_init', 0);
add_filter( 'rewrite_rules_array','my_insert_rewrite_rules' );
add_action( 'wp_loaded','my_flush_rules' );


?>