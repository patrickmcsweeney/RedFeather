<?php 
call_back_list("rss", array( 'load_data', 'render_rss' ) );
array_push($pages, 'resource');
$function_map['render_rss'] = 'render_rss';

function render_rss() {
	global $variables;
	
	header("Content-type: application/rss+xml");

	echo '<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:media="http://search.yahoo.com/mrss/"><channel>
    <title>RedFeather RSS</title>
    <link>'.$variables["rf_url"].'</link>
    <atom:link rel="self" href="'.$variables['rf_url'].'?page=rss" type="application/rss+xml" xmlns:atom="http://www.w3.org/2005/Atom"></atom:link>
    <description></description>
    <language>en</language>
';
	foreach($variables['data'] as $file => $data)
	{
		if(!$data['title']) { continue; }
		$resource_url = htmlentities($variables['rf_url'].'?page=resource&file='.$file);
		print '<item><pubDate>';
		$mtime = "";
		if(is_file($file)){
			$mtime = filemtime($file);
		}
		print date ("d M Y H:i:s O", $mtime);
		print '</pubDate>
  <title>'.htmlentities($data['title']).'</title>
  <link>'.$resource_url.'</link>
  <guid>'.$resource_url.'</guid>
  <description>'.htmlentities($data['description']).'</description>
</item>';
  
	}

	print '</channel></rss>';
}
