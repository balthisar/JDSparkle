JDSparkle Plugin for CakePHP
============================

Easily add support for the Sparkle upgrade mechanism for Mac OS X applications
to your CakePHP website. JDSparkle will:

* Serve your AppCast xml files to the Sparkle updater.
* Serve your update notes to Sparkle clients via Cake standard view files
  using the template of your choice.
* Log system profiling data to your database if provided as part of
  of the xml request.
  - Supports any arbitrary tags in addition to Sparkle's defaults.
* Some rudimentary reporting of all system profiles with support for
  all data or a day-limited range. Also tries to limit results by
  unique IP address to help filter out frequent update checks.


Requirements
------------
Tested with configuration below, and may or may not work in earlier releases:

* PHP 5.2+
* CakePHP 2.3+
* A Sparkle-enhanced application for Mac OS X. Sparkle ports for other platforms
  should probably work as well.


API Documentation
-----------------
http://balthisar.github.io/JDSparkle


Installation
------------

### Download the zip file:
- Download this: https://github.com/balthisar/CakePHP-JDSparkle/archive/master.zip
- Unzip that download.
- Move or copy the `JDSparkle` directory into your `app/Plugin directory`.


### Or clone via github:

If you’re aware of this option then I presume you already know how to use git
and Github.

### And then load the plugin in your `app/Config/bootstrap.php` file:

	CakePlugin::load(array('JDSparkle' => array('routes' => true)));

### Finally setup your database:

By defaults JDSparkle attempts to use the database connection `JDSparkleDB`,
which is probably not setup in your database. You can either add it to your
`app/Config/database.php` or change the `$useDbConfig` settings in the
two model class files `JDSparkleRecord.php` and `JDSparkleReport.php`.

Finally import the schema file from the CakePHP shell:

	cake schema create --plugin JDSparkle


### Final Finally: Generate Fake Sample Data:

If you'd like to generate some fake, sample data for testing the reporting
features, you can (using the supplied default routing) navigate to
`http://www.example.com/softwareupdates/randomdata/secretpassword`.


How to Use
----------

### First a note about security

You should set up your own security system using CakePHP access control lists.
For convenience JDSparkle ships with an unsafe password system that's sent as
clear text. In the class file `JDSparkleReportsController.php` you should
set the variable `$secretpassword` to a null string to turn off this feature.
If you'd like to use this feature on your testing server, then at least consider
changing the default from "secretpassword" to something else.


### A note about sample code

- The sample code will use `www.example.com` as the web host. Obviously use your
  own server.
- The sample code will use `softwareupdates` as the main path to access the
  controller. You can considering changing this plugin's routes to point to
  any path that you want.
- For admin type actions, they will be shown using the "secretpassword" as
  described above. Once you've set up your own authentication, simply omit
  the password from your URL's.


### General Workflow

- The application using Sparkle will check for updates using the url
  `http://www.example.com/softwareupdates/updatecheck/appnameFile`.
    - Note the absence of the `.xml` extension.
    - This is the value you should place in the `SUFeedURL` in your
      application's `info.plist` file.
    - As currently implemented, JDSparkle will _not_ log IP data for
      these types of requests.
- If you are collecting system profiling information then the suitable
  GET request will be similar to
  `http://www.example.com/softwareupdates/updatecheck/appnameFile?appName=xxx&?…`.
    - Note the absence of the `.xml` extension.
    - This is the value you should place in the `SUFeedURL` in your
      application's `info.plist` file.
- Your XML appcast files must be placed in the plugin's `View/AppCasts`
  directory as `appname.ctp`. See the section below about "Potential
  cause for confusion."
    - Remember that `.ctp` files are PHP files. Some PHP configurations
      might interpret your `<?xml` declaration as a PHP declaration and
      you will be unhappy. To be safe simply echo the `<?xml` declaration
      with PHP.
- Your release notes must be placed in the plugin's `View/AppReleaseNotes`
  directory matching the what you have in the appcast XML
  `<sparkle:releaseNotesLink>` tag.
    - The `<sparkle:releaseNotesLink>` tag should have the URL in a form
      similar to `http://www.example.com/softwareupdates/releasenotes/appnameVersionXXX`.
    - Again per Cake conventions the URL typically does not have a file
      extension, as this is just a normal CakePHP view.
    - The default CakePHP layout for these files is "blank.ctp" but you
      can change this in the controller.
- Finally the update package should be placed anywhere on your server that
  can be served via your CakePHP application, typically somewhere in
  `app/webroot`. There's no special configuration for this that's any different
  than Sparkle's normal configuration. That is, in your XML `<enclosure` point
  to the update package just as your normally do.


#### Potential cause for confusion

Note that above the use of "appnameFile" applies to URL request that
Sparkle itself makes (and the file system representation). It is **not**
specifically related to the "appName" request parameter that Sparkle will
provide if you are using system profiling.

However the admin actions **do use** the "appName" request parameter in
their requests, since this is how they're identified in the database.


Actions Overview
----------------

- `updatecheck` as described above.

- `releasenotes` as described above.

- `randomdata` as described above.

- `overview` is an admin function that provides an overview of all of your
  apps' collect profiling information. It can be accessed at
  `http://www.example.com/softwareupdates/overview/secretpassword`.

- `details` is an admin function that provides specific details for a single
  app for which JDSparkle has collected profiling information. Access it at
  `http://www.example.com/softwareupdates/details/appname.app/secretpassword`.
  - Note: in this case, the field `appname.app` is _not_ the same as the
    appnameFile described further above. It is the value of the appName
    request parameter that Sparkle sends as part of the GET request when
    profiling is enabled.


Please look at the source code (or Doxygen docs)
------------------------------------------------

The source code is fairly verbosely documented. You don’t have to understand
the source code, but please read all of the comments at the top of the main
files.


License
-------
Copyright (c) 2013 by Jim Derry

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
