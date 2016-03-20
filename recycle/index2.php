<?php
/*
 * Copyright 2013 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', TRUE);


// Modified by Karl Kranich to demonstrate the Sheets API

require_once 'vendor/autoload.php';
include_once 'google-api-php-client/examples/templates/base.php';

/************************************************
  Make an API request authenticated with a service
  account.
 ************************************************/

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
  Make sure the Books API is enabled on this
  account as well, or the call will fail.
 ************************************************/

// Karl chose putenv - edit this path if your json file is named differently or in a different folder than this file
putenv('GOOGLE_APPLICATION_CREDENTIALS=service-account.json');

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
$service = new Google_Service_Drive($client);

// The file ID was copied from a URL while editing the sheet in Chrome
$fileId = '1dJ8eXgon1md2Et2OqupbpSz_5VdG5l19PPu2Q8s_k-s';  // replace with your file identifier




// Section 1: Uncomment to get file metadata with the drive service
// This is also the service that would be used to create a new spreadsheet file
//$results = $service->files->get($fileId);
//var_dump($results);

// Section 2: Uncomment to get list of worksheets
// $url = "https://spreadsheets.google.com/feeds/worksheets/$fileId/private/full";
// $method = 'GET';
// $httpClient = new GuzzleHttp\Client();
// $client->authorize($httpClient);
// $request = $httpClient->createRequest($method, $url);
// $response = $httpClient->send($request);
// $statusCode = $response->getStatusCode();
// $reason = $response->getReasonPhrase();
// $body = $response->getBody();
// echo "$statusCode, $reason\n\n$body\n";

// Section 3: Uncomment to get the table data
// Found the following URL in the output from section 2, looking for href after link rel='http://schemas.google.com/g/2005#feed'
$url = "https://spreadsheets.google.com/feeds/list/$fileId/od6/private/full";
$method = 'GET';
$httpClient = new GuzzleHttp\Client();
$client->authorize($httpClient);
$request = $httpClient->createRequest($method, $url);
$response = $httpClient->send($request);
$statusCode = $response->getStatusCode();
$reason = $response->getReasonPhrase();
$body = $response->getBody();
echo "$statusCode, $reason\n\n$body\n";

// Section 4: Uncomment to add a row to the sheet
// The same URL can be used to POST new rows to the spreadsheet
// $url = "https://spreadsheets.google.com/feeds/list/$fileId/od6/private/full";
// $method = 'POST';
// $headers = ['Content-Type' => 'application/atom+xml'];
// $postBody = '<entry xmlns="http://www.w3.org/2005/Atom" xmlns:gsx="http://schemas.google.com/spreadsheets/2006/extended"><gsx:gear>more gear</gsx:gear><gsx:quantity>99</gsx:quantity></entry>';
// $httpClient = new GuzzleHttp\Client();
// $client->authorize($httpClient);
// $request = $httpClient->createRequest($method, $url, ['headers' => $headers, 'body' => $postBody]);
// $response = $httpClient->send($request);
// $statusCode = $response->getStatusCode();
// $reason = $response->getReasonPhrase();
// $body = $response->getBody();
// echo "$statusCode, $reason\n\n$body\n";

// Section 5: Uncomment to edit a row
// You'll need to get the etag and row ID, and send a PUT request to the edit URL
// $rowid = 'cre1l';                 // got this and the etag from the table data output from section 3
// $etag = 'NQ8VCRBLVCt7ImA.';
// $url = "https://spreadsheets.google.com/feeds/list/$fileId/od6/private/full/$rowid";
// $method = 'PUT';
// $headers = ['Content-Type' => 'application/atom+xml', 'GData-Version' => '3.0'];
// $postBody = "<entry xmlns=\"http://www.w3.org/2005/Atom\" xmlns:gsx=\"http://schemas.google.com/spreadsheets/2006/extended\" xmlns:gd=\"http://schemas.google.com/g/2005\" gd:etag='&quot;$etag&quot;'><id>https://spreadsheets.google.com/feeds/list/$fileid/od6/$rowid</id><gsx:gear>phones</gsx:gear><gsx:quantity>6</gsx:quantity></entry>";
// $httpClient = new GuzzleHttp\Client();
// $client->authorize($httpClient);
// $request = $httpClient->createRequest($method, $url, ['headers' => $headers, 'body' => $postBody]);
// $response = $httpClient->send($request);
// $statusCode = $response->getStatusCode();
// $reason = $response->getReasonPhrase();
// $body = $response->getBody();
// echo "$statusCode, $reason\n\n$body\n";
