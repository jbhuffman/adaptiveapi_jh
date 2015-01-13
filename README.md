adaptiveapi_jh
==============

# Adaptive Insights API calls in PHP

Follows version 7 of the Adaptive Insights API.  It is a static class that implements all of the current API methods.


```
// Usage
require_once 'api_adaptive.php';

use \JHuffman\API;

// will return any errors or other messages from an import INTO Adaptive
$response = Adaptive::importCubeData($user, $pass, $actuals, $version, $sheet, $fields, $data, $moveBPtr, $allowParallel, $isUserAssigned, $output);

// will return any errors or other messages from an import INTO Adaptive
$response = Adaptive::importStandardData($user, $pass, $actuals, $version, $fields, $data, $moveBPtr, $allowParallel, $output);

// will return data FROM Adaptive based on passed in options
$response = Adaptive::exportData($user, $pass, $version, $start, $end, $levels, $accounts, $dimensions, $filterDimensions, $isDefault, $output);

// get Adaptive "Versions"
$response = Adaptive::exportVersions($user, $pass);

// get Adaptive "Levels"
$response = Adaptive::exportLevels($user, $pass);

// get Adaptive "Dimensions"
$response = Adaptive::exportDimensions($user, $pass);

// get Adaptive "Accounts"
$response = Adaptive::exportAccounts($user, $pass, $output);

// erase Actuals for a specific range, version, sheet
$response = Adaptive::eraseActuals($user, $pass, $version, $type, $sheet, $start, $end, $includeCellNotes, $output);

// sends the request.  This most likely won't be called by your script as it is called within each data methods
// can be used to deal with any new api methods that may arise
$response = Adaptive::send($xmlRequest);

// process the xml response, parses any messages that are returned
$response = processResponse($xml);

```

parseVersions() is a private method to flatten the version structure into a single dimension array

parseLevels() is a private method to flatten the level structure into a single dimension array

parseAccounts() is a private method to flatten the account structure into a single dimension array