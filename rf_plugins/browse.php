<?php

array_push($pages, 'browse');
call_back_list('browse', array( 'load_data', 'render_top','render_browse','render_bottom'));
$function_map["render_browse"] = "render_browse";
	

function render_browse()
{
	global $variables;

	$variables["page"] .= '<div class="rf_search">
	Filter: <input id="rf_filter" onkeypress="filter()"type="text" value="" />
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
		$variables["page"] .= sprintf(<<<BLOCK
<div class="rf_resource">
	<h3 class="rf_resource_title">%s</h3>
	<p class="rf_description">%s</p>
	<div class="rf_last_modified">Last Modified: %s</div>
	<div class="rf_licence">Licence: </div>
	<div class="rf_download"><a href="$filename">Download</a></div>
</div>
BLOCK
, $data["title"], $data["description"], date ("d F Y - H:i", filemtime($filename))); 
	}
	$variables["page"] .= '</div>';
}

