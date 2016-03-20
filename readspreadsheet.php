<?php


error_reporting(E_ALL);
ini_set('display_errors', 'on');


/**
 * Developed based upon this page: http://karl.kranich.org/2015/04/16/google-sheets-api-php/
 */

function getAccessToken() {
  global $accessToken, $fileId;

  require_once realpath(dirname(__FILE__) . '/vendor/autoload.php');
  include_once "google-api-php-client/examples/templates/base.php";

  $client = new Google_Client();

  /************************************************
    ATTENTION: Fill in these values, or make sure you
    have set the GOOGLE_APPLICATION_CREDENTIALS
    environment variable. You can get these credentials
    by creating a new Service Account in the
    API console. Be sure to store the key file
    somewhere you can get to it - though in real
    operations you'd want to make sure it wasn't
    accessible from the webserver!
   ************************************************/
  putenv("GOOGLE_APPLICATION_CREDENTIALS=service-account-credentials.json");

  if ($credentials_file = checkServiceAccountCredentialsFile()) {
    // set the location manually
    $client->setAuthConfig($credentials_file);
  } elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
    // use the application default credentials
    $client->useApplicationDefaultCredentials();
  } else {
    echo missingServiceAccountDetailsWarning();
    exit;
  }

  $client->setApplicationName("Sheets API Testing");
  $client->setScopes(['https://www.googleapis.com/auth/drive','https://spreadsheets.google.com/feeds']);

  // The file ID was copied from a URL while editing the sheet in Chrome
  $fileId = '1dJ8eXgon1md2Et2OqupbpSz_5VdG5l19PPu2Q8s_k-s';

  // Access Token is used for Steps 2 and beyond
  $tokenArray = $client->fetchAccessTokenWithAssertion();
  $accessToken = $tokenArray["access_token"];

}


/**
 * Uncomment to parse table data with SimpleXML
 */
function getStairs() {
  global $accessToken, $fileId;

  $stairs = array();

  $url = "https://spreadsheets.google.com/feeds/list/$fileId/1/private/full";
  $method = 'GET';
  $headers = ["Authorization" => "Bearer $accessToken", "GData-Version" => "3.0"];
  $httpClient = new GuzzleHttp\Client(['headers' => $headers]);
  $resp = $httpClient->request($method, $url);
  $body = $resp->getBody()->getContents();
  $tableXML = simplexml_load_string($body);

  foreach ($tableXML->entry as $key => $entry) {
    $stair = new stdClass();

    //$etag = $entry->attributes('gd', TRUE);
    //$id = $entry->id;

    foreach ($entry->children('gsx', TRUE) as $column) {
      $colName = $column->getName();
      $colValue = (string) $column;
      //echo "$colName : $colValue<br/>\n";
      $stair->$colName = $colValue;
    }

    $stairid = trim($stair->stairid);
    $stairs[$stairid] = $stair;
  }

  //print '<pre>'.print_r($stairs,TRUE).'</pre>';
  return $stairs;
}
