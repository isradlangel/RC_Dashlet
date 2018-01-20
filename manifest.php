<?php
$manifest = array(
	'acceptable_sugar_flavors'  => array('CE', 'PRO', 'CORP', 'ENT', 'ULT'),
	'acceptable_sugar_versions' => array(
		'exact_matches' => array(),
		'regex_matches' => array('(.*?)\.(.*?)\.(.*?)$'),
	),
	'author'                    => 'Kenneth Brill',
	'description'               => 'Leads Queue',
	'icon'                      => '',
	'is_uninstallable'          => true,
	'name'                      => 'Dashlet_test',
	'published_date'            => '2018-01-19 18:50:17',
	'type'                      => 'module',
	'version'                   => '1.0'
);

$installdefs = array(
	'id'          => 'CUSTOM1516409410',
	'copy'        =>
		array(
			0 =>
				array(
					'from'      => '<basepath>/files/custom/clients/base/views/rcReport/rcReport.js',
					'to'        => 'custom/clients/base/views/rcReport/rcReport.js',
					'timestamp' => '2018-01-19 15:34:02',
				),
			1 =>
				array(
					'from'      => '<basepath>/files/custom/Extension/application/Ext/JSGroupings/dataTables.php',
					'to'        => 'custom/Extension/application/Ext/JSGroupings/dataTables.php',
					'timestamp' => '2018-01-19 15:07:56',
				),
			2 =>
				array(
					'from'      => '<basepath>/files/custom/clients/base/views/rcReport/rcReport.hbs',
					'to'        => 'custom/clients/base/views/rcReport/rcReport.hbs',
					'timestamp' => '2018-01-19 15:07:56',
				),
			3 =>
				array(
					'from'      => '<basepath>/files/custom/clients/base/views/rcReport/rcReport.php',
					'to'        => 'custom/clients/base/views/rcReport/rcReport.php',
					'timestamp' => '2018-01-19 15:07:56',
				),
			4 =>
				array(
					'from'      => '<basepath>/files/custom/modules/Reports/clients/base/api/ReportsDashlets2Api.php',
					'to'        => 'custom/modules/Reports/clients/base/api/ReportsDashlets2Api.php',
					'timestamp' => '2018-01-19 15:07:56',
				),
			5 =>
				array(
					'from'      => '<basepath>/files/custom/Extension/application/Ext/JSGroupings/addCssLoaderPlugin.php',
					'to'        => 'custom/Extension/application/Ext/JSGroupings/addCssLoaderPlugin.php',
					'timestamp' => '2018-01-19 15:04:17',
				),
			6 =>
				array(
					'from'      => '<basepath>/files/custom/include/javascript/sugar7/plugins/CssLoader.js',
					'to'        => 'custom/include/javascript/sugar7/plugins/CssLoader.js',
					'timestamp' => '2018-01-19 15:04:17',
				),
			7 =>
				array(
					'from'      => '<basepath>/files/custom/javascript/DataTables/datatables.min.css',
					'to'        => 'custom/javascript/DataTables/datatables.min.css',
					'timestamp' => '2018-01-19 14:55:08',
				),
			8 =>
				array(
					'from'      => '<basepath>/files/custom/javascript/DataTables/datatables.min.js',
					'to'        => 'custom/javascript/DataTables/datatables.min.js',
					'timestamp' => '2018-01-19 14:54:34',
				),
			9 =>
				array(
					'from'      => '<basepath>/files/custom/javascript/DataTables/images',
					'to'        => 'custom/javascript/DataTables/images',
					'timestamp' => '2018-01-19 14:54:34',
				),
		),
	'pre_execute' =>
		array(),
);