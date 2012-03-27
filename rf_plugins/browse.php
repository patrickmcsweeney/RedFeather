<?php

array_push($pages, 'browse');
call_back_list('browse', array( 'load_data', 'render_top','render_browse','render_bottom'));
$function_map["render_browse"] = "render_browse";
	
if(!isset($_REQUEST['page']))
{
        $_REQUEST['page'] = "browse";
}


function render_browse()
{
	global $variables;

	$licenses = get_licenses();

	$variables["page"] .= '<div class="rf_search">
	Search these resources: <input id="rf_filter" onkeypress="filter()"type="text" value="" />
	<script type="text/javascript">
		function filter(){
			var filter = $("#rf_filter").val();
			if(filter == ""){
				$(".rf_resource").show();	
				return;
			}
			$(".rf_resource").hide();
			$(".rf_resource:contains("+$("#rf_filter").val()+")").show();
		}
	</script>
</div>
	';

	$variables["page"] .= '<div class="rf_browse_list">';
	foreach($variables["data"] as $filename => $data)
	{
		$url = $_SERVER["SCRIPT_NAME"]."?page=resource&file=".$filename;
		$variables["page"] .= sprintf(<<<BLOCK
<div class="rf_resource">
	<h2 class="rf_resource_title"><a href="$url">%s</a></h2>
	<p class="rf_description">%s</p>
	<span class="rf_last_modified"><span class="field_name">Last Modified:</span> %s</span>
	<span class="rf_license"><span class="field_name">License:</span> %s</span>
	<span class="rf_download"><a href="$filename">Download</a></span>
</div>
BLOCK
, $data["title"], $data["description"], date ("d F Y - H:i", filemtime($filename)), $licenses[$data["license"]]); 
	}
	$variables["page"] .= '</div>';
}

