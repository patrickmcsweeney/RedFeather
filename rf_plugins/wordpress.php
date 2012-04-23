<?php 
$function_map['load_data'] = 'wordpress_load_data';

function wordpress_load_data()
{
        global $variables;

	include('wp-content/plugins/wordpress_redfeather.php');

        $variables['data'] = rf_wordpress_load_data();


        if(!is_array($variables["data"]) )
        {
                $variables["data"]= array();
        }

}

