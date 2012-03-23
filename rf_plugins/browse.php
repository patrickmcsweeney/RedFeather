<?php

array_push($pages, 'browse');
call_back_list('browse', array( 'load_data', 'render_top','render_browse','render_bottom'));
$function_map["render_browse"] = "render_browse";
	

function render_browse()
{
	global $variables;
	foreach($variables["data"] as $filename => $data)
	{
		$variables["page"] .= sprintf(<<<BLOCK
<div>
	<h3>%s</h3>
	<p>%s</p>
	<span>Last Modified: %s</span>
	<span>Licence: </span>
	<span><a href="$filename">Download</a></span>
</div>
BLOCK
, $data["title"], $data["description"], date ("d F Y H:i:s.", filemtime($filename))); 
	}
}

