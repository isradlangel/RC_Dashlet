/**
 * Row & Column Dashlet v1.0 ©2018 Kenneth Brill (ken.brill@gmail.com)
 * Licensed by Kenneth Brill under the MIT license.
 *
 * The following items are configurable.
 *
 * - {Integer} limit Limit imposed to the number of records shown.
 * - {Integer} refresh How often (minutes) should refresh the data collection.
 *
 * @class View.Views.Base.rcReportView
 * @alias SUGAR.App.view.views.BasercReportView
 * @extends View.View
 */
({
	plugins: ['Dashlet', 'CssLoader'],

	css: [
		'custom/javascript/DataTables/datatables.min.css'
	],

	/**
	 * Default options used when none are supplied through metadata.
	 *
	 * Supported options:
	 * - timer: How often (minutes) should refresh the data collection.
	 * - limit: Limit imposed to the number of records shown from the report.
	 *
	 * @property {Object}
	 * @protected
	 */
	_defaultOptions: {
		limit: 100,
		auto_refresh: 0
	},

	_tableRender: false,

	/**
	 * @inheritdoc
	 */
	initialize: function (options) {
		options.meta = options.meta || {};
		this._super('initialize', [options]);
		this.loadData(options.meta);
	},

	/**
	 * Init dashlet settings for Rows & Columns Reports
	 */
	initDashlet: function (view) {
		// check if we're on the config screen
		if (this.meta.config) {
			var options = {};
			// Get and set values for limits and refresh
			options.limit = this.settings.get('limit') || this._defaultOptions.limit;
			this.settings.set('limit', options.limit);

			options.auto_refresh = this.settings.get('auto_refresh') || this._defaultOptions.auto_refresh;
			this.settings.set('auto_refresh', options.auto_refresh);
			options.rc_report_id = this.settings.get('rc_report_id');
			this.meta.panels = this.dashletConfig.dashlet_config_panels;
			this.getAllSavedReports();
		} else {
			var autoRefresh = this.settings.get('auto_refresh');
			if (autoRefresh > 0) {
				if (this.timerId) {
					clearTimeout(this.timerId);
				}

				this._scheduleReload(autoRefresh * 1000 * 60);
			}
		}
	},

	/**
	 * Makes a call to Reports/saved_reports to get any items stored in the
	 * saved_reports table
	 */
	getAllSavedReports: function () {
		var params = {
				has_charts: true
			},
			url = app.api.buildURL('Reports/saved_reports2', null, null, params);

		app.api.call('read', url, null, {
			success: _.bind(this.parseAllSavedReports, this)
		});
	},

	/**
	 * Parses items passed back from Reports/saved_reports endpoint into enum options
	 *
	 * @param {Array} reports an array of saved reports returned from the endpoint
	 */
	parseAllSavedReports: function (reports) {
		this.reportOptions = {};
		this.reportAcls = {};

		_.each(reports, function (report) {
			// build the reportOptions key/value pairs
			this.reportOptions[report.id] = report.name;
			this.reportAcls[report.id] = report._acl;
		}, this);

		// find the rc_report_id field
		var reportsField = _.find(this.fields, function (field) {
			return field.name == 'rc_report_id';
		});

		if (reportsField) {
			// set the initial rc_report_id to the first report in the list
			// if there are reports to show and we have not already saved this
			// dashlet yet with a report ID
			if (reports && (!this.settings.has('rc_report_id') || _.isEmpty(this.settings.get('rc_report_id')))) {
				this.settings.set('rc_report_id', _.first(reports).id);
			}

			// set field options and render
			reportsField.items = this.reportOptions;
			reportsField._render();
		}
	},

	/**
	 * Schedules chart data reload
	 *
	 * @param {Number} delay Number of milliseconds which the reload should be delayed for
	 * @private
	 */
	_scheduleReload: function (delay) {
		this.timerId = setTimeout(_.bind(function () {
			this.context.resetLoadFlag();
			this.loadData({
				              success: function () {
					              this._scheduleReload(delay);
				              }
			              });
		}, this), delay);
	},
	/**
	 * Handles the response of the API request and sets data from
	 * the result
	 *
	 * @param {Object} data Response from the rcReport API call
	 */
	handleTable: function (data) {
		if (this.disposed) {
			return;
		}

		// Load up the template
		_.extend(this, data);
		this.render();
		$(document).ready(function () {
			var table = $('#' + data.report_id).DataTable(
				{
					retrieve: true,
					searching: false,
					lengthChange: false,
					scrollY: '300px',
					scrollX: true,
					scrollCollapse: true,
					paging: false,
					fixedColumns: {
						heightMatch: 'none'
					},
					stateSave: true,
					stateSaveCallback: function (settings, data) {
						localStorage.setItem('DataTables_' + settings.sInstance, JSON.stringify(data));
					},
					stateLoadCallback: function (settings) {
						return JSON.parse(localStorage.getItem('DataTables_' + settings.sInstance));
					}
				});
			table.column( 0 ).visible( false );
			$('#' + data.report_id).on('click', 'tbody tr', function() {
				if(data.link) {
					//get the value of the TD using the API
					var rowData = table.row( this ).data();
					console.log('REST CALL : ', rowData[0]);

					var link = '#' + rowData[0]
					if (!_.isEmpty(link)) {
						app.router.redirect(link);
					}
				}
			})
		});
		this._tableRender = true;
	},

	/**
	 * Loads a report from the runReport endpoint.
	 *
	 * @param {Object} options The metadata that drives this request
	 */
	loadData: function (options) {
		if (options && options.rc_report_id) {
			var callbacks = {success: _.bind(this.handleTable, this), error: _.bind(this.handleTable, this)},
				limit = options.limit || this._defaultOptions.limit,
				params = {rc_report_id: options.rc_report_id, limit: limit},
				apiUrl = app.api.buildURL('Reports/runReport', 'read', '', params);

			app.api.call('read', apiUrl, {}, callbacks);
		}
	}
});
