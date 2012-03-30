<?php 
session_start();
$function_map['authenticate'] = 'authenticate';
$variables["users"] = array("admin"=>"shoes");
function authenticate() {
	global $variables, $function_map, $_SESSION, $_POST;
	
	if(isset($_SESSION["current_user"]))
	{
		return ;
	}
	if (isset($_POST["username"]) && isset($_POST["password"]) 
		&& isset($variables['users'][$_POST["username"]]) 
		&& $variables['users'][$_POST["username"]]==$_POST["password"]) 
	{
		$_SESSION["current_user"]=$_POST["username"];
		return;
	}
	
	
	call_user_func($function_map['render_top']);

	$variables['page'] .= '<form method="post" action="'.$variables['rf_file'].'?'.$_SERVER['QUERY_STRING'].'">
	Username: <input type="text" name="username" />
	Password: <input type="password" name="password" />
	<input type="submit" value="Login" />
	</form>';
	call_user_func($function_map['render_bottom']);

	print $variables['page'];
	exit;
}
