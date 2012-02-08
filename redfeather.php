<?php
$functions = array();

function call($function_name, $arg_array)
{
	foreach( $function as $functions[$function_name] )
	{
		call_user_func($function, $arg_array);
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

	$functions[$function_name] = array();
	return True;
}
