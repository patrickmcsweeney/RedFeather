<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1); 
error_reporting(E_ALL);

$pages = array();
$functions = array();
$function_map = array('load_data'=>'load_data', 'save_data'=>'save_data', 'render_resource'=>'render_resource', 'render_top'=>'render_top', 'render_bottom'=>'render_bottom', 'render_manage_list'=>'render_manage_list');
$variables = array('page'=>'');
$variables['metadata_file'] = "rf_data.php";
$variables['plugin_dir'] = "rf_plugins";

// ensures that the metadata file exists
touch($variables['metadata_file']);


array_push($pages, 'resource');
call_back_list('resource', array( 'load_data', 'render_top','render_resource','render_bottom'));

array_push($pages, 'manage_resources');
call_back_list('manage_resources', array( 'save_data', 'load_data', 'render_top','render_manage_list','render_bottom'));

if(is_dir($variables["plugin_dir"]))
{
	if ($dh = opendir($variables["plugin_dir"])) 
	{
		while (($file = readdir($dh)) !== false) 
		{
			if(is_file($variables['plugin_dir'].'/'.$file) && preg_match('/\.php$/', $file))
			{
				include($variables['plugin_dir'].'/'.$file);
			}
		}
		closedir($dh);
	}

}

if(isset($_REQUEST['page']))
{
	call($_REQUEST['page']);
}
else
{
	call('resource');
}

print $variables['page'];


// FUNCTIONS FROM HERE ON DOWN
function call($function_name)
{
	global $functions, $function_map;
	foreach( $functions[$function_name] as $function )
	{
		call_user_func($function_map[$function]);
	}
}

function call_back_list($function_name, $list=Null)
{
	global $functions;
	if($list == Null)
	{
		if(isset($functions[$function_name]))
		{
			return $functions[$function_name];
		}
		
		return array();
	}

	$functions[$function_name] = $list;
	return True;
}

function load_data()
{
	global $variables;

	
	$variables['data'] = unserialize(file_get_contents($variables['metadata_file']));

	if(!is_array($variables["data"]) )
	{
		$variables["data"]= array();
	}

}

function save_data()
{
	global $variables;

	if(isset($_REQUEST['filenames']) && is_array($_REQUEST['filenames']))
	{	
		for($i=0; $i < count($_REQUEST["filenames"]); $i++)
		{
			$variables["data"][$_REQUEST["filenames"][$i]]["title"] = $_REQUEST["titles"][$i];
			$variables["data"][$_REQUEST["filenames"][$i]]["description"] = $_REQUEST["descriptions"][$i];
		}
		$fh = fopen($variables["metadata_file"], "w");
		fwrite($fh,serialize($variables['data']));
		fclose($fh);

	}
}

function render_top()
{
	global $variables;
	$variables['page_title'] = $_REQUEST['page'];
	$variables['page'] .= 
'<html><head>
	<title>'.$variables['page_title'].'</title>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
</head><body>
<div class="rf_content">';
}

function render_resource()
{
	global $variables;
	$data = $variables['data'][$_REQUEST['file']];
	$this_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?page=resource&file='.$_REQUEST['file'];
	$bits = explode('/', $this_url);
	array_pop($bits);
	$file_url = implode('/', $bits).'/'.$_REQUEST['file'];
	$variables['page'] .= '<h1>RedFeather - '.$data['title'].'</h1>';

	$variables['page'] .= '<div class="rf_resource_main">';
	
	$variables['page'] .= '<div class="rf_resource_metadata">';

	$variables['page'] .= '<h2>Description</h2>';
	$variables['page'] .= '<p>'.$data['description'].'</p>';

	$variables['page'] .= '<h2>Resource details</h2>';
	$variables['page'] .= '<table><tbody>';

	$variables['page'] .= '<tr><td>Updated:</td><td>'.date ("d F Y H:i:s.", filemtime($_REQUEST['file'])).'</td></tr>';
	$variables['page'] .= '<tr><td>Licence:</td><td>'.$data['licence'].'</td></tr>';
	$variables['page'] .= '<tr><td>Link here:</td><td>'.$this_url.'</td></tr>';
	$variables['page'] .= '<tr><td>Link here:</td><td>'.$file_url.'</td></tr>';
	$variables['page'] .= '</tbody></table>';

	$variables['page'] .= '</div>';

	$variables['page'] .= '<iframe src="http://docs.google.com/viewer?embedded=true&url='.urlencode($file_url).'" width="600" height="780" style="border: none;"></iframe>';
	$variables['page'] .= '<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_GB/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, "script", "facebook-jssdk"));</script>
<div class="fb-comments" data-href="'.$this_url.'" data-num-posts="2" data-width="470"></div>';

}

function render_manage_list()
{
	global $variables;
	$variables['page'] .= '<h1>RedFeather</h1>';

	$dir = "./";

	$new_file_count = 0;
	$manage_resources_html = '';
	$files_found_list = array();
		
	// Probably breaks with windows and other things which dont use /
	$php_file = array_pop(explode("/", $_SERVER["SCRIPT_NAME"]));
	$variables["page"] .= "<form action='$php_file?page=manage_resources' method='POST'>\n";
	foreach (scandir($dir) as $file)
	{

		if(is_dir($dir.$file)){continue;}
		if($file == $php_file){continue;}
		if($file == $variables["metadata_file"]){continue;}
		if(preg_match("/^\./", $file)){continue;}

		$file_line =  "filename: $file : filetype: " . filetype($dir . $file) . "<br />\n";
		if (isset($variables['data']["$file"])) {
			$data = $variables['data']["$file"];
			array_push($files_found_list, $file);
			$new_style_rule = '';
		}
		else
		{
			$data = array('title'=>'','description'=>'');
			$new_style_rule = ' rf_new_resource';
			$new_file_count++;
		}
			$manage_resources_html .= sprintf( <<<BLOCK
<div class="rf_metadata_input$new_style_rule">
<table><tbody>
<tr><td>File name:</td><td><a href='$file' target='_blank'>$file</td></tr>
<tr><td>Title:</td><td><input name="titles[]" value="%s" autocomplete="off" /></td></tr>
<tr><td>Description:</td><td><textarea name="descriptions[]" autocomplete="off">%s</textarea></td></tr>
<tr><td>Licence:</td><td><select name="licence">
	<option value="foo bar baz" autocomplete="off">Foo Bar Baz</option>
</select></td></tr></tbody></table>
<input type="hidden" name="filenames[]" value="$file" />
</div>
BLOCK
, $data["title"], $data["description"] );
			
	}
		
	// if there are any new resources give info at the top
	if ($new_file_count)
	{	
		$variables["page"] .= "<p>$new_file_count new files found.</p>";
	}

	// check whether any files are missing
	$missing_resources_html = '';
	$missing_resource_autonumber = 1;

	foreach ($variables['data'] as $key => $value) {
		if (! in_array($key, $files_found_list))
		{
			$missing_resources_html .= sprintf( <<<BLOCK
<div id="missing$missing_resource_autonumber"><p>Resource not found: $key <input type="hidden" name="titles[]" value="%s" /><input type="hidden" name="descriptions[]"/><input type="hidden" name="filenames[]" value="$key" /><a href="#" onclick="javascript:$('#missing$missing_resource_autonumber').remove();">delete metadata</a></p></div>
</div>
BLOCK
, $value["title"], $value["description"] );
			$missing_resource_autonumber++;
		}
	}
	
	$variables["page"] .= $missing_resources_html;
	$variables["page"] .= $manage_resources_html;
	$variables["page"] .= "<input type='submit' value='Save' />\n";
	$variables["page"] .= "</form>\n";

}

function render_bottom()
{
	global $variables;
	$variables['page'] .= '</div></body>
</html>';
}
