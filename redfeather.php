<?php
error_reporting(E_ALL);
$pages = array();
$functions = array();
$variables = array('page'=>'');

array_push($pages, 'resource');
call_back_list('resource', array( 'load_data', 'render_top','render_resource','render_bottom'));

array_push($pages, 'manage_resources');
call_back_list('manage_resources', array( 'load_data', 'render_top','render_manage_list','render_bottom'));

if(isset($_REQUEST['page']))
{
	call($_REQUEST['page']);
}
else
{
	call('resource');
}

print $variables['page'];

function call($function_name, $arg_array=array())
{
	global $functions;
	foreach( $functions[$function_name] as $function )
	{
		array_push($results, call_user_func($function));
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
}

function render_top()
{
	global $variables;
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

	foreach($variables['data'] as $file){
		$variables['page'] .= "<div>".$variables['data'][$file]." - <a href='$file'>$file</a></div>";
	}
}

function render_bottom()
{
	global $variables;
	$variables['page'] .=
'</body>
</html>';
}
