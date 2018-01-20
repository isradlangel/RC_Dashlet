<?php
/**
 * Row & Column Dashlet v1.0 ©2018 Kenneth Brill (ken.brill@gmail.com)
 * Licensed by Kenneth Brill under the MIT license.
 */
require_once('include/api/SugarApi.php');
require_once('include/SugarQuery/SugarQuery.php');
require_once('modules/Reports/Report.php');
require_once('modules/Reports/SavedReport.php');
require_once('modules/Reports/schedule/ReportSchedule.php');
require_once('include/SugarPHPMailer.php');
require_once('include/modules.php');
require_once('config.php');

class ReportsDashlets2Api extends SugarApi
{
	//If a DB field is named one of these then turn it into a link
	protected $linkedNames = array('name', 'id');

	protected $reportMap = array();

	public function registerApiRest()
	{
		return array(
			'getSavedReports2' => array(
				'reqType'   => 'GET',
				'path'      => array('Reports', 'saved_reports2'),
				'pathVars'  => array('', ''),
				'method'    => 'getSavedReports2',
				'shortHelp' => 'Returns items from the saved_reports table based on a few criteria',
				'longHelp'  => 'modules/Reports/clients/base/api/help/ReportsDashletApiGetSavedReports.html',
			),
			'runReport'        => array(
				'reqType'   => 'GET',
				'path'      => array('Reports', 'runReport'),
				'pathVars'  => array('', ''),
				'method'    => 'runReport',
				'shortHelp' => 'Runs a report and returns the data as a table',
				'longHelp'  => 'modules/Reports/clients/base/api/help/Reports_runReport.html',
			),
		);
	}

	/**
	 * Retrieves all saved reports that meet args-driven criteria
	 *
	 * @param $api  ServiceBase The API class of the request
	 * @param $args array The arguments array passed in from the API
	 * @return array
	 */
	public function getSavedReports2($api, $args)
	{
		// Make sure the user isn't seeing reports they don't have access to
		require_once('modules/Reports/SavedReport.php');
		$modules = array_keys(getACLDisAllowedModules());
		$fieldList = array('id', 'name', 'module', 'report_type', 'content', 'chart_type', 'assigned_user_id');

		$sq = new SugarQuery();
		$sq->from(BeanFactory::getBean('Reports'));
		$sq->select($fieldList);
		$sq->orderBy('name', 'asc');

		// if there were restricted modules, add those to the query
		if (count($modules)) {
			$sq->where()->notIn('module', $modules);
		}

		$sq->where()->equals('report_type', 'tabular');

		$result = $sq->execute();
		// check acls
		foreach ($result as $key => &$row) {
			$savedReport = $this->getSavedReportFromData($row);

			if ($savedReport->ACLAccess('list')) {
				// for front-end to check acls
				$row['_acl'] = ApiHelper::getHelper($api, $savedReport)->getBeanAcl($savedReport, $fieldList);
			} else {
				unset($result[$key]);
			}
		}
		return $result;
	}

	/**
	 * @param $api
	 * @param $args
	 */
	public function runReport($api, $args)
	{
		global $current_user, $sugar_config, $current_language, $app_list_strings, $app_strings, $locale;
		global $report_modules, $modListHeader;
		$limit = $args['limit'];
		$app_list_strings = return_app_list_strings_language($current_language);
		$app_strings = return_application_language($current_language);

		$current_user = new User();
		$current_user->getSystemUser();

		$language = $sugar_config['default_language'];

		$app_list_strings = return_app_list_strings_language($language);
		$app_strings = return_application_language($language);

		$modListHeader = query_module_access_list($current_user);
//$report_modules = getAllowedReportModules($modListHeader);

		$theme = $sugar_config['default_theme'];
		$saved_report = new SavedReport();
		$saved_report->retrieve($args['rc_report_id']);

		$GLOBALS['log']->debug('-----> Generating Reporter');
		if (!empty($args['rc_report_id'])) {
			$reporter = new Report(html_entity_decode($saved_report->content));

			$GLOBALS['log']->debug('-----> Reporter settings attributes');
			$reporter->layout_manager->setAttribute("no_sort", 1);
			$module_for_lang = $reporter->module;
			$mod_strings = return_module_language($current_language, 'Reports');

			$GLOBALS['log']->debug('-----> Reporter Handling PDF output');
			$tableData = $this->template_handle_table($reporter, $limit, $saved_report);

			return $tableData;
		}
		return null;
	}

	/**
	 * Creates a SavedReport bean from query result
	 * @param $row
	 *
	 * @return SugarBean
	 */
	protected function getSavedReportFromData($row)
	{
		$savedReport = BeanFactory::getBean('Reports');
		$savedReport->populateFromRow($row);
		return $savedReport;
	}

	/**
	 * @param array $colWidth
	 * @return array
	 */
	private function calcCellWidths($colWidth)
	{
		$totalChars = array_sum($colWidth);
		foreach ($colWidth as $columnNumber => $numOfChars) {
			$percent = intval(($numOfChars / $totalChars) * 100);
			$retWidths[$columnNumber] = $percent;
		}
		return $retWidths;
	}

	private function template_handle_table(&$reporter, $limit, $saved_report)
	{
		if (!is_numeric($limit) || $limit == 0) {
			$limit = 100;
		}
		$reporter->plain_text_output = true;
		//disable paging so we get all results in one pass
		$reporter->enable_paging = false;
		$reporter->run_query();

		while ($hash = $reporter->db->fetchByAssoc($reporter->result)) {
			$this->reportMap[] = $hash;
		}
		$primaryID = false;
		if (isset($this->reportMap[0]['primaryid'])) {
			$primaryID = true;
		}

		mysqli_data_seek($reporter->result, 0);
		$reporter->_load_currency();
		$header_row = $reporter->get_header_row();

		$rowCount = 0;
		$body = array();
		$charWidth = array();
		while (($row = $reporter->get_next_row('result', 'display_columns', false, true)) != 0) {
			if ($primaryID) {
				$module = $reporter->focus->module_name;
				$id = $this->reportMap[$rowCount]['primaryid'];
				$new_arr = array($module . '/' . $id);
			} else {
				$new_arr = array('&nbsp;');
			}

			for ($i = 0; $i < count($row['cells']); $i++) {
				if (!isset($charWidth[$i])) {
					$charWidth[$i] = 0;
				}
				$cellContents = preg_replace("/\"/", "\"\"", from_html($row['cells'][$i]));
				if (strlen($cellContents) > $charWidth[$i]) {
					$charWidth[$i] = strlen($cellContents);
				}
				array_push($new_arr, $cellContents);
			}
			if ($rowCount <= $limit) {
				$body[] = $new_arr;
			} else {
				break;
			}
			$rowCount++;
		}
		$colWidths = $this->calcCellWidths($charWidth);
		$header = array(array('name' => 'PrimryIDField', 'width' => 0));
		$i = 0;
		foreach ($header_row as $cell) {
			array_push($header, array('text' => $cell, 'width' => $colWidths[$i]));
			$i++;
		}


		return array('body'        => $body,
					 'link'        => $primaryID,
					 'header'      => $header,
					 'report_name' => $saved_report->name,
					 'report_id'   => $saved_report->id);
	}
}
