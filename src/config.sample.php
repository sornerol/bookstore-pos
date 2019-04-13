<?php
/*
 *Edit this file with your MySQL database's credentials. Rename the file to config.php afterwards
 */


/*
 * ENVIRONMENT is used to select which database to connect to.
 * 'prod' connects to the production database
 * 'dev' connects to the development database.
 */

define('ENVIRONMENT', 'prod');

//set the login credentials for your production installation's database here
if(ENVIRONMENT == 'prod')
{
	define('DB_NAME', 'DATABASE_NAME_GOES_HERE');
	define('DB_USER', 'DATABASE_USER_GOES_HERE');
	define('DB_PASSWORD', 'DATABASE_PASSWORD_GOES_HERE');
	define('DB_HOST', 'DATABASE_HOST_GOES_HERE');
}

//set the login credentials for your testing/development installation's database here
else if(ENVIRONMENT == 'dev')
{
	define('DB_NAME', 'DATABASE_NAME_GOES_HERE');
	define('DB_USER', 'DATABASE_USER_GOES_HERE');
	define('DB_PASSWORD', 'DATABASE_PASSWORD_GOES_HERE');
	define('DB_HOST', 'DATABASE_HOST_GOES_HERE');
}

//if ENVIRONMENT is not set correctly, let the user know and exit.
else
{
	exit('<b>Fatal error:</b> ENVIRONMENT is not properly set. Edit config.php and try again.');
}

//sets how many records to show on the search page
define('RECORS_PER_PAGE',20);

//Google Books API endpoint
define('GOOGLE_BOOKS_EP','https://www.googleapis.com/books/v1/volumes?q=isbn:');

//Current software version string
define('SW_VERSION','1.1.0');

