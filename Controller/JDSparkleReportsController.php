<?php
/*************************************************************************************************
 * @file           JDSparkleReportsController.php
 *
 * Part of plugin `JDSparkle`
 *
 * @details
 *
 * For applications that make requests via the Sparkle upgrade mechanism:
 * - handles machine data collection
 * - provides appcast XML for products
 * - provides release notes for updates
 *
 * @date           2014-02-12
 * @author         Jim Derry
 * @copyright      ©2014 by Jim Derry and balthisar.com
 * @copyright      MIT License (http://www.opensource.org/licenses/mit-license.php)
 *************************************************************************************************/


App::uses('AppController', 'Controller');


/** This class implements all actions for `JDSparkleReports`. */
class JDSparkleReportsController extends AppController
{
	/** Specify the layout to use for views. */
	public $layout = 'default';

	/** Specify a different layout to use for release notes. */
	public $layoutReleaseNotes = 'blank';

	/**
	 * Provides a secret password for the reports, if you don't want to use ACL.
	 * Make this null if you're using ACL, otherwise it's required.
	 */
	private $secretpassword = 'secretpassword';


	/**-------------------------------------------------------------------------**
	 * Returns the release notes as requested.
	 *
	 * @details
	 * View files are located in the plugin View folder:
	 * `app/Plugin/JDSparkle/View/AppReleaseNotes/`
	 **-------------------------------------------------------------------------**/
	public function releasenotes()
	{
		$path  = func_get_args();
		$count = count($path);
		if ( !$count )
		{
			$this->redirect('/');
		}
		$page = $subPage = $title_for_layout = null;

		if ( !empty($path[0]) )
		{
			$page = $path[0];
		}
		if ( !empty($path[1]) )
		{
			$subPage = $path[1];
		}
		if ( !empty($path[$count - 1]) )
		{
			$title_for_layout = Inflector::humanize($path[$count - 1]);
		}

		$this->layout = $this->layoutReleaseNotes;
		$this->set(compact('page', 'subPage', 'title_for_layout'));
		$this->render('AppReleaseNotes' . DS . implode('/', $path));
	}


	/**-------------------------------------------------------------------------**
	 * Returns the AppCast feed after logging the GET data into the database.
	 *
	 * @details
	 * View files are located in the plugin View folder:
	 * `app/Plugin/JDSparkle/View/AppCasts/`
	 * These view files will be returned as XML to the browser.
	 **-------------------------------------------------------------------------**/
	public function updatecheck()
	{
		// The app feed we want should be in the first argument.
		$path  = func_get_args();
		$count = count($path);

		if ( $count < 1 )
		{
			// With routes.php properly setup, we should never
			// arrive here. But, just in case.
			throw new NotFoundException();
		}

		// Make sure the view exists
		$path = $path[0];
		if ( !file_exists(APP . 'Plugin' . DS . 'JDSparkle' . DS . 'View' . DS . 'AppCasts' . DS . $path . '.ctp') )
		{
			throw new NotFoundException();
		}

		// Pass the date to our model to record
		$this->JDSparkleReport->addReport($this->request->query);

		// Generate the XML view.
		$this->layout = 'blank';
		$this->RequestHandler->respondAs('xml');
		$this->render('AppCasts' . DS . $path);
	}


	/**-------------------------------------------------------------------------**
	 * Returns a brief report about all apps in the database.
	 *
	 * @details
	 * Shows data for a all apps in the database with optional support for
	 * secretpassword. Depending on routes.php, the access format should be
	 * something similar to
	 * `http://example.com/softwareupdates/overview/secretpassword`
	 **-------------------------------------------------------------------------**/
	public function overview()
	{
		$path  = func_get_args();
		$count = count($path);

		if ( ($this->secretpassword) && ($count < 1) )
		{
			throw new ForbiddenException();
		}

		if ( ($this->secretpassword) && ($path[0] != $this->secretpassword) )
		{
			throw new ForbiddenException();
		}

		$reportData    = $this->JDSparkleReport->getOverviewData('', 60);
		$reportDataAll = $this->JDSparkleReport->getOverviewData('', PHP_INT_MAX);

		$this->set(compact('reportData', 'reportDataAll'));
		$this->set('secretpassword', $this->secretpassword);

		return;
	}


	/**-------------------------------------------------------------------------**
	 * Returns a brief report about the app specified.
	 *
	 * @details
	 * Shows data for a single app with optional support for secretpassword.
	 * Depending on routes.php, the access format should be something
	 * similar to
	 * `http://example.com/softwareupdates/appdetails/myapp.app/secretpassword`
	 * @throws NotFoundException
	 * @throws ForbiddenException
	 **-------------------------------------------------------------------------**/
	public function details()
	{
		$path  = func_get_args();
		$count = count($path);

		if ( ($count < 1) || ($this->secretpassword && $count < 2) )
		{
			throw new NotFoundException();
		}

		if ( ($this->secretpassword) && ($path[1] != $this->secretpassword) )
		{
			throw new ForbiddenException();
		}

		$reportData = $this->JDSparkleReport->getReportDataForApp($path[0]);
		$this->set(compact('reportData'));

		return;
	}


	/**-------------------------------------------------------------------------**
	 * Generate some random data to populate the database with. Used for
	 * development; maybe you'll want to use it for testing.
	 *
	 * @throws ForbiddenException
	 **-------------------------------------------------------------------------**/
	public function randomdata()
	{
		$path  = func_get_args();
		$count = count($path);

		if ( ($this->secretpassword) && ($count < 1) )
		{
			throw new ForbiddenException();
		}

		if ( ($this->secretpassword) && ($path[0] != $this->secretpassword) )
		{
			throw new ForbiddenException();
		}

		$this->JDSparkleReport->buildSampleData(50);

		return;
	}

} // class

