<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1); 
error_reporting(E_ALL);

$pages = array();
$functions = array();
$function_map = array('load_data'=>'load_data', 'save_data'=>'save_data', 'render_resource'=>'render_resource', 'render_top'=>'render_top', 'render_bottom'=>'render_bottom', 'render_manage_list'=>'render_manage_list');
$variables = array('page'=>'');
$variables['metadata_file'] = "rf_data.php";

// ensures that the metadata file exists
touch($variables['metadata_file']);


array_push($pages, 'resource');
call_back_list('resource', array( 'load_data', 'render_top','render_resource','render_bottom'));

array_push($pages, 'manage_resources');
call_back_list('manage_resources', array( 'save_data', 'load_data', 'render_top','render_manage_list','render_bottom'));


if(isset($_REQUEST['page']))
{
	call($_REQUEST['page']);
}
else
{
	call('resource');
}

print $variables['page'];

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
</head><body>';
}

function render_resource()
{
	global $variables;
	$variables['page'] .= '<h1>RedFeather</h1>';

	foreach($variables['data'] as $file){
		$variables['page'] .= "<div>".$variables['data'][$file]." - <a href='$file'>$file</a></div>";
	}
}

function render_manage_list()
{
	global $variables;
	$variables['page'] .= '<h1>RedFeather</h1>';

	$dir = "./";
	if ($dh = opendir($dir)) {

		$new_file_count = 0;
		$manage_resources_html = '';
		$files_found_list = array();
		
		// Probably breaks with windows and other things which dont use /
		$php_file = array_pop(explode("/", $_SERVER["SCRIPT_NAME"]));
		$variables["page"] .= "<form action='$php_file?page=manage_resources' method='POST'>\n";
		while (($file = readdir($dh)) !== false) {		
	
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
		closedir($dh);
		
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

}

function render_bottom()
{
	global $variables;
	$variables['page'] .= '</body>
</html>';
}
