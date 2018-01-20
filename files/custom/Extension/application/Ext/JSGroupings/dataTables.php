<?php
/**
 * Row & Column Dashlet v1.0 ©2018 Kenneth Brill (ken.brill@gmail.com)
 * Licensed by Kenneth Brill under the MIT license.
 */
foreach ($js_groupings as $key => $groupings)
{
	foreach  ($groupings as $file => $target)
	{
		//if the target grouping is found
		if ($target == 'include/javascript/sugar_grp7.min.js')
		{
			//append the custom JavaScript file
			$js_groupings[$key]['custom/javascript/DataTables/datatables.min.js'] = 'include/javascript/sugar_grp7.min.js';
		}
		break;
	}
}