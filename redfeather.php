<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1); 
error_reporting(E_ALL);
$pages = array();
$functions = array();
$function_map = array('load_data'=>'load_data', 'save_data'=>'save_data', 'render_resource'=>'render_resource', 'render_top'=>'render_top', 'render_bottom'=>'render_bottom', 'render_manage_list'=>'render_manage_list');
$variables = array('page'=>'');

array_push($pages, 'resource');
call_back_list('resource', array( 'load_data', 'render_top','render_resource','render_bottom'));

array_push($pages, 'manage_resources');
call_back_list('manage_resources', array( 'load_data', 'save_data', 'load_data', 'render_top','render_manage_list','render_bottom'));


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
	
	$variables['data'] = unserialize(file_get_contents("rf_meta.php"));
	if(! is_array($variables["data"]) )
	{
		$variables['data'] = array();
	}
}

function save_data()
{
	if(is_array($_REQUEST["filenames"]))
	{	
		for($i=0; $i < count($_REQUEST["filenames"]); $i++)
		{
			$variables["data"][$_REQUEST["filenames"][$i]]["title"] = $_REQUEST["titles"][$i];
			$variables["data"][$_REQUEST["filenames"][$i]]["description"] = $_REQUEST["descriptions"][$i];
		}
		$fh = fopen("rf_meta.php", "w");
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

	$manage_list = "";
	$dir = "./";
	if ($dh = opendir($dir)) {

		#Probably breaks with windows and other things which dont use /
		$php_file = array_pop(explode("/", $_SERVER["SCRIPT_NAME"]));
		$variables["page"] .= "<form action='$php_file?page=manage_resources' method='POST'>\n";
		while (($file = readdir($dh)) !== false) {
			
			if(is_dir($dir.$file)){continue;}
			if($file == $php_file){continue;}
			if($file == "rf_meta.php"){continue;}

			$file_line =  "filename: $file : filetype: " . filetype($dir . $file) . "<br />\n";
			$data = $variables["data"]["$file"];
			$variables["page"] .= sprintf( <<<BLOCK
<div class="metadata-input">
<input name="titles[]" value="%s" /> <br />
<textarea name="descriptions[]">%s</textarea> <br />
<select name="licence">
	<option value="foo bar baz" />
</select><br />
<input type="hidden" name="filenames[]" value="$file" />
</div>
BLOCK
, $data["title"], $data["description"] );
			
		}
		closedir($dh);
		$variables["page"] .= "<input type='submit' value='Save' />\n";
		$variables["page"] .= "</form>\n";
	}

	foreach($variables['data'] as $file => $val){
		$variables['page'] .= "<div>".$variables['data'][$file]['title']." - <a href='$file'>$file</a></div>";
	}
	$variables["page"] .= $manage_list;
}

function render_bottom()
{
	global $variables;
	$variables['page'] .= '</body>
</html>';
}
