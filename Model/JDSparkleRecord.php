<?php
/*************************************************************************************************
 * @file           JDSparkleRecord.php
 *
 * Part of plugin `JDSparkle`
 *
 * @details
 *
 * This file represents the interface to record key-value pairs.
 *
 * @date           2014-02-12
 * @author         Jim Derry
 * @copyright      ©2014 by Jim Derry and balthisar.com
 * @copyright      MIT License (http://www.opensource.org/licenses/mit-license.php)
 *************************************************************************************************/


App::uses('AppModel', 'Model');


/** This class represents the interface to records. */
class JDSparkleRecord extends AppModel
{
	public $useTable = 'records';           ///< Database table this model uses.
	public $useDbConfig = 'JDSparkleDB';    ///< Database configuration in `app/config`
	public $primaryKey = 'id';              ///< Primary key.
	public $order = [ 'key' => 'ASC' ];     ///< Default sorting order.
	public $belongsTo = [                   ///< Default relations for this model.
		'JDSparkleReport' => [
			'className'  => 'JDSparkle.JDSparkleReport',
			'foreignKey' => 'report_id'
		]
	];
}
