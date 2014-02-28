<?php
/*************************************************************************************************
 * @file           routes.php
 *
 * @brief
 *
 * Part of plugin `JDSparkle`
 *
 * @details
 *
 * Routes configuration for JDSparkle.
 *
 * Use this file to to define the base URL path for everything
 * in this plugin.
 *
 *
 * @note
 *
 * For more information and examples look at the comments in the source file.
 *
 * @date           2014-02-23
 * @author         Jim Derry
 * @copyright      ©2014 by Jim Derry and balthisar.com
 * @copyright      MIT License (http://www.opensource.org/licenses/mit-license.php)
 *************************************************************************************************/


/*
	Configure our JDSparkle routes.
*/

// These are actions that require parameters (such as accepting a slug)
Router::connect(
	'/softwareupdates/:action/:slug',
	array(
		'plugin'     => 'JDSparkle',
		'controller' => 'JDSparkleReports',
		'action'     => ':action'
	),
	array(
		'action' => 'updatecheck|releasenotes|details|overview|randomdata',
		'pass'   => array( 'slug' )
	)
);

// If secretpassword is being used for security, then this route is needed for details.
Router::connect(
	'/softwareupdates/:action/:slug/:password',
	array(
		'plugin'     => 'JDSparkle',
		'controller' => 'JDSparkleReports',
		'action'     => ':action'
	),
	array(
		'action' => 'details',
		'pass'   => array( 'slug', 'password' )
	)
);
