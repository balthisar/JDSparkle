<?php
/*************************************************************************************************
 * @file           JDSparkleReport.php
 *
 * Part of plugin `JDSparkle`
 *
 * @details
 *
 * This file represents the interface to individual reports.
 *
 * @date           2014-02-12
 * @author         Jim Derry
 * @copyright      ©2014 by Jim Derry and balthisar.com
 * @copyright      MIT License (http://www.opensource.org/licenses/mit-license.php)
 *************************************************************************************************/


App::uses('AppModel', 'Model');


/** This class represents the interface to reports. */
class JDSparkleReport extends AppModel
{
	/* You shouldn't have to change any of these */
	public $useTable = 'reports';                     ///< Database table this model uses.
	public $useDbConfig = 'JDSparkleDB';              ///< Database configuration in `app/config`
	public $primaryKey = 'id';                        ///< Primary key.
	public $order = [                                 ///< Default sorting order.
	                  'app_name'    => 'ASC',
	                  'report_date' => 'ASC'
	];
	public $hasMany = [                               ///< Default relations for this model.
	                    'JDSparkleRecord' => [
		                    'className'  => 'JDSparkle.JDSparkleRecord',
		                    'foreignKey' => 'report_id',
	                    ]
	];


	/**-------------------------------------------------------------------------**
	 * Returns a data structure needed to display an application report.
	 *
	 * @param string  $appName      Name of the app as from appName query.
	 * @param integer $periodInDays Reporting period start date, in days.
	 * @return array
	 **-------------------------------------------------------------------------**/
	public function getReportDataForApp( $appName, $periodInDays = 60 )
	{
		$startDate = strtotime("-$periodInDays days", strtotime(date('Y-m-d')));

		// We want four major sections of data here:
		// - app header
		// - statistics using all data
		// - unique IP address counts by date
		// - statistics using only last data from each IP

		/*-----------------------*
		 * App header
		 *-----------------------*/
		$result['appHeader'] = $this->getOverviewData($appName, $periodInDays)[0]['JDSparkleReport'];


		/*-----------------------*
		 * All statistics
		 *-----------------------*/
		$this->order = [
			'ip_address'  => 'ASC',
			'report_date' => 'DESC',
		];
		$conditions = [ 'JDSparkleReport.app_name' => $appName ];
		$conditions['UNIX_TIMESTAMP(JDSparkleReport.report_date) >='] = $startDate;

		$result['all_data'] = $this->flattenIntoQuantities($this->find('all', [ 'conditions' => $conditions ]));


		/*-----------------------*
		 * Unique IP's by date
		 *-----------------------*/
		$intermediate = $this->find(
			'all', [
				     'fields'     => [ 'count(distinct ip_address) as ip_count', 'date(report_date) as for_date' ],
				     'group'      => 'date(report_date)',
				     'order'      => [ 'date(report_date)' => 'DESC' ],
				     'conditions' => $conditions,
				     'recursive'  => -1
			     ]
		);
		foreach ( $intermediate as $item )
		{
			$result['ip_counts'][$item[0]['for_date']] = $item[0]['ip_count'];
		}


		/*-----------------------*
		   for each IP address
		 *-----------------------*/
		// CakePHP does so many things easily,
		// but sub-queries ain't one of 'em.
		$db       = $this->getDataSource();
		$subQuery = $db->buildStatement(
			[
				'fields'     => [ 'max(t2.report_date)' ],
				'table'      => $db->fullTableName($this),
				'alias'      => 't2',
				'conditions' => [ 't2.ip_address = `JDSparkleReport`.`ip_address`', 't2.app_name = `JDSparkleReport`.`app_name`' ],
			], $this
		);

		$subQuery           = " `report_date` = ($subQuery)";
		$subQueryExpression = $db->expression($subQuery);
		$conditions[]       = $subQueryExpression;

		$result['ip_data'] = $this->flattenIntoQuantities($this->find('all', [ 'conditions' => $conditions ]));

		return $result;
	}


	/**-------------------------------------------------------------------------**
	 * Returns a data structure needed to display an overview report.
	 *
	 * @param string  $appName      Name of the app as from appName query.
	 * @param integer $periodInDays Reporting period start date, in days.
	 * @return array
	 **-------------------------------------------------------------------------**/
	public function getOverviewData( $appName = '', $periodInDays = 60 )
	{
		$startDate                                                    = strtotime("-$periodInDays days", strtotime(date('Y-m-d')));
		$conditions                                                   = $appName ? [ 'JDSparkleReport.app_name' => $appName ] : [ ];
		$conditions['UNIX_TIMESTAMP(JDSparkleReport.report_date) >='] = $startDate;
		$result                                                       = $this->find('all', [ 'fields' => [ 'DISTINCT JDSparkleReport.app_name' ], 'recursive' => -1, 'conditions' => $conditions ]);
		foreach ( $result as &$item )
		{
			$local                                                        = & $item['JDSparkleReport'];
			$conditions                                                   = [ 'JDSparkleReport.app_name' => $local['app_name'] ];
			$local['date_first_unconstrained']                            = $this->field('min(report_date)', $conditions);
			$conditions['UNIX_TIMESTAMP(JDSparkleReport.report_date) >='] = $startDate;
			$local['date_first']                                          = $this->field('min(report_date)', $conditions);
			$local['date_last']                                           = $this->field('max(report_date)', $conditions);
			$local['report_count']                                        = $this->field('count(app_name)', $conditions);
			$local['distinct_ip_count']                                   = $this->field('count(DISTINCT ip_address)', $conditions);
		}

		return $result;
	}


	/**-------------------------------------------------------------------------**
	 * Converts all of the database results into lists of quantities for each
	 * property.
	 *
	 * @param array $dbResultSet A database result set to flatten into stats.
	 * @return array
	 **-------------------------------------------------------------------------**/
	private function flattenIntoQuantities( $dbResultSet )
	{
		$recordCount = count($dbResultSet);
		$secondary   = [ ];

		// The flattening operating gets all of the counts.
		// We could do all of the math in this loop for code
		// simplicity, but that's needlessly slow.
		foreach ( $dbResultSet as $record )
		{
			foreach ( $record['JDSparkleRecord'] as $item )
			{
				if ( array_key_exists($item['key'], $secondary) )
				{
					if ( array_key_exists($item['value'], $secondary[$item['key']]) )
					{
						// Increment Quantity
						$secondary[$item['key']][$item['value']]['count']++;
					}
					else
					{
						// Add Value and Quantity
						$secondary[$item['key']][$item['value']]['count'] = 1;
					}
				}
				else
				{
					// Add Key, Value, and Quantity.
					$secondary[$item['key']][$item['value']]['count'] = 1;
				}
				// sort based on the 'count'
				uasort(
					$secondary[$item['key']], function ( $a, $b )
					{
						return $b['count'] - $a['count'];
					}
				);
			}
		}

		// Now add the percentages to each attribute.
		foreach ( $secondary as &$outer )
		{
			foreach ( $outer as &$item )
			{
				$item['fraction'] = $item['count'] / $recordCount;
				$item['percent']  = sprintf("%.1f%%", $item['fraction'] * 100);
			}
		}

		return $secondary;
	}


	/**-------------------------------------------------------------------------**
	 * Adds a lot of random, sample data to the database for testing.
	 *
	 * @details
	 * Each `$appNames` will add 500 records each consisting of random data.
	 *
	 * @param integer $recordsEach Quantity of random records to add for each app.
	 **-------------------------------------------------------------------------**/
	public function buildSampleData( $recordsEach = 500 )
	{
		$appNames  = [ 'Tidy.app', 'wizard', 'Ghost' ];
		$endDate   = strtotime(date('Y-m-d h:m:s'));
		$startDate = strtotime("-100 days", $endDate);

		$appFields[] = [ 'fieldname' => 'appVersion', 'data' => [ 'v1', 'v2', 'v3' ] ];
		$appFields[] = [ 'fieldname' => 'cpuFreqMHz', 'data' => [ '1600', '2200', '2800', '3200' ] ];
		$appFields[] = [ 'fieldname' => 'cpu64bit', 'data' => [ 'Y', 'N' ] ];
		$appFields[] = [ 'fieldname' => 'cpusubtype', 'data' => [ 'type1', 'type2' ] ];
		$appFields[] = [ 'fieldname' => 'cputype', 'data' => [ '6802', '68000', 'PPC', 'Z-80' ] ];
		$appFields[] = [ 'fieldname' => 'lang', 'data' => [ 'en', 'es', 'zh' ] ];
		$appFields[] = [ 'fieldname' => 'model', 'data' => [ 'iMac', 'MacPro', 'MacBook' ] ];
		$appFields[] = [ 'fieldname' => 'ncpu', 'data' => [ 'one', 'two', 'three', 'four' ] ];
		$appFields[] = [ 'fieldname' => 'osVersion', 'data' => [ '10.9', '10.8', '10.7', 'WinME' ] ];
		$appFields[] = [ 'fieldname' => 'ramMB', 'data' => [ '4096', '8192', '16384', '32768' ] ];

		foreach ( $appNames as $app )
		{
			$reportData = [ ];
			for ( $i = 0; $i < $recordsEach; $i++ )
			{

				$reportData['report_date'] = strftime("%Y-%m-%d %H:%M:%S", mt_rand($startDate, $endDate));
				$reportData['ip_address']  = '192.168.1.' . mt_rand(1, 20);
				$reportData['appName']     = $app;

				foreach ( $appFields as $field )
				{
					$reportData[$field['fieldname']] = $field['data'][array_rand($field['data'])];
				}

				$this->addReport($reportData, true);
			}
		}

	}


	/**-------------------------------------------------------------------------**
	 * Adds a report and records to the database.
	 *
	 * @param array $reportData Array of key-value pairs to add.
	 * @param boolean $fakeInfo Indicates we will use fake report information.
	 *                          This supports random data generation for testing.
	 **-------------------------------------------------------------------------**/
	public function addReport( $reportData, $fakeInfo = false )
	{
		$reportData = array_map('strtolower', $reportData);
		$reportData = array_change_key_case($reportData);

		if ( array_key_exists('appname', $reportData) )
		{
			 /*-----------------------*
			   Report Information
			  *-----------------------*/
			if ( !$fakeInfo )
			{
				$report_date = strftime("%Y-%m-%d %H:%M:%S");
				$ip_address  = $_SERVER["REMOTE_ADDR"];
				$app_name    = $reportData['appname'];
			}
			else
			{
				// $fakeInfo supports our random data generator.
				$report_date = $reportData['report_date'];
				$ip_address  = $reportData['ip_address'];
				$app_name    = $reportData['appname'];
				unset($reportData['report_date']);
				unset($reportData['ip_address']);
			}
			// We have array of Key=>Value, but we need to format it
			// into Key=>Key and Value=>Value for each item.
			$innerArray = [ ];
			foreach ( $reportData as $key => $value )
			{
				$innerArray[] = compact('key', 'value');
			}

			if ( $innerArray )
			{
				$savearray = [
					'JDSparkleReport' => compact('report_date', 'ip_address', 'app_name'),
					'JDSparkleRecord' => $innerArray,
				];

				if ( !$this->saveAll($savearray) )
				{
					debug("Something went wrong in JDSparkle. I should throw an exception. Maybe version 2.0.");
				}
			}
		}
	}

} // class
