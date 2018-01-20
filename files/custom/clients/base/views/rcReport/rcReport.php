<?php
/**
 * Row & Column Dashlet v1.0 ©2018 Kenneth Brill (ken.brill@gmail.com)
 * Licensed by Kenneth Brill under the MIT license.
 */
$viewdefs['base']['view']['rcReport'] = array(
	'dashlets' => array(
		array(
			'label' => 'Rows & Columns Reports',
			'description' => 'A Dashlet to add Rows & Columns reports to dashlets',
			'config' => array(

			),
			'preview' => array(

			),
		)
	),
    'dashlet_config_panels' => array(
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'rc_report_id',
                    'label' => 'Report',
                    'type' => 'enum',
                    'options' => array(''=>''),
                    //to be filled in later by JavaScript
                ),
                array(
                    'name' => 'limit',
                    'label' => 'LBL_RSS_FEED_ENTRIES_COUNT',
                    'type' => 'enum',
                    'options' => array(
                    	10=>'10',
						20=>'20',
						25=>'25',
						50=>'50',
						100=>'100',
						150=>'150',
						200=>'200',
						250=>'250',
						300=>'300',
					),
                ),
                array(
                    'name' => 'auto_refresh',
                    'label' => 'LBL_REPORT_AUTO_REFRESH',
                    'type' => 'enum',
                    'options' => 'sugar7_dashlet_reports_auto_refresh_options',
                ),
            ),
        ),
    ),
);
