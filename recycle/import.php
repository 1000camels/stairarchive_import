<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

/**
 * Autoload files of https://github.com/google/google-api-php-client
 *
 */ 
require 'vendor/autoload.php';


/**
 * AUTHENTICATE
 *
 */
// These settings are found on google developer console
const CLIENT_APP_NAME = 'Stairs Archive Import';
const CLIENT_ID       = 'stair-archive.apps.googleusercontent.com';
const CLIENT_EMAIL    = 'stairs-archive-import@stair-archive.iam.gserviceaccount.com';
const CLIENT_KEY_PATH = 'StairArchive.p12'; // PATH_TO_KEY = where you keep your key file
const CLIENT_KEY_PW   = 'notasecret';
 
$objClientAuth  = new Google_Client ();
$objClientAuth -> setApplicationName (CLIENT_APP_NAME);
$objClientAuth -> setClientId (CLIENT_ID);
/*$objClientAuth -> setAssertionCredentials (new Google_Auth_AssertionCredentials (
    CLIENT_EMAIL, 
    array('https://spreadsheets.google.com/feeds','https://docs.google.com/feeds'), 
    file_get_contents (CLIENT_KEY_PATH), 
    CLIENT_KEY_PW
));
$objClientAuth->getAuth()->refreshTokenWithAssertion();
$objToken  = json_decode($objClientAuth->getAccessToken());
$accessToken = $objToken->access_token;*/

/**
 * Initialize the service request factory
 */ 
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;
 
$serviceRequest = new DefaultServiceRequest($accessToken);
ServiceRequestFactory::setInstance($serviceRequest);
 
 
/**
 * Get spreadsheet by title
 */
$spreadsheetTitle = 'YOUR-SPREADSHEET-TITLE';
$spreadsheetService = new Google\Spreadsheet\SpreadsheetService();
$spreadsheetFeed = $spreadsheetService->getSpreadsheets();
$spreadsheet = $spreadsheetFeed->getByTitle($spreadsheetTitle);

print 'here';
