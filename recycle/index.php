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

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;

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





$data = loadData();

foreach ($data as $section => $rows) {
        print $section." ".count($rows)."<br/>\n";
        print_r(array_keys($rows[0]));exit;
}


/**
 * Down the CSV from Google
 */
function loadData() {
        global $accessToken;

        $serviceRequest = new DefaultServiceRequest($accessToken);
        ServiceRequestFactory::setInstance($serviceRequest);

        $spreadsheetService = new Google\Spreadsheet\SpreadsheetService();
        $spreadsheetFeed = $spreadsheetService->getSpreadsheets();
        $spreadsheet = $spreadsheetFeed->getByTitle('Chinese Architects from the 70s');
        $worksheetFeed = $spreadsheet->getWorksheets();

        // HKSA attr
        $worksheet = $worksheetFeed->getByTitle('HKSA attr');
        $listFeed = $worksheet->getListFeed();
        foreach ($listFeed->getEntries() as $entry) {
                $data['stairs'][] = $entry->getValues();
        }

        return $data;
}
