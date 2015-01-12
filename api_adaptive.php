<?php
class api_adaptive
{
	const URL = 'https://live.adaptiveplanning.com/api/v7';

	private $user;
	private $pass;

	public $instance;
	public $version;
	public $isDefault = 'false';
	public $accounts = array();
	public $level;
	public $start;
	public $end;
	public $dimensions = array();
	public $departments = array();

	/**
	* Adaptive importCubeData
	*
	* @param mixed $sheet
	* @param mixed $actuals 'Plan' or 'Actuals'
	* @param mixed $version Version if $actuals is 'Plan'
	* @param mixed $fields  array of columns/fields
	* @param mixed $data    array of rows to import
	*
	* @return string
	*/
	public static function importCubeData(
        $user, $pass, $actuals, $version, $sheet, $fields, $data,
        $moveBPtr = "false", $allowParallel = "false",
        $isUserAssigned = "false", $output = false
    ) {
        // Hard coded for now
        //$moveBPtr = 'false';
        //$allowParallel = 'false';
        $isDefault = 'false';
        if (empty($version)) {
            $isDefault = "true";
        }
        //$isUserAssigned = 'false';
		$xmlCreate =
			'<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
			'<call method="importCubeData">' . "\n" .
			' <credentials login="' . $user . '" password="' . $pass . '"/>' . "\n" .
			' <importDataOptions planOrActuals="' . $actuals . '" moveBPtr="'.$moveBPtr.'" allowParallel="'.$allowParallel.'" />' . "\n" .
			' <version name="' . $version . '" isDefault="'.$isDefault.'" />' . "\n" .
			' <sheet name="' . $sheet . '" isUserAssigned="'.$isUserAssigned.'" />' . "\n" .
			' <rowData>' . "\n" .
			'  <header>' . implode('|', $fields) . '</header>' . "\n" .
			'  <rows>' . "\n";
		foreach ($data as $record) {
		    $xmlCreate .= '   <row>' . implode('|', $record) . "</row>\n";
		}

		$xmlCreate .=
			'  </rows>' . "\n" .
			' </rowData>' . "\n" .
			'</call>';
        if ($output) {
            header('content-type: application/xml');
            exit($xmlCreate);
        }

        $response = self::send($xmlCreate);
        return $response;
    }

    /**
    * Adaptive API::importStandardData
    *
    * @param mixed $user
    * @param mixed $pass
    * @param mixed $actuals
    * @param mixed $version
    * @param mixed $fields
    * @param mixed $data
    * @param mixed $output
    * @return string
    */
    public function importStandardData(
        $user, $pass, $actuals, $version, $fields, $data, $moveBPtr = "false",
        $allowParallel = "false", $output = false
    ) {
        // Hard coded for now
        $isDefault = "false";
        if (empty($version)) {
            $isDefault = "true";
        }
        //$allowParallel = "false";
        //$moveBPtr = "false";
        $xmlCreate =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<call method="importStandardData">' . "\n" .
            ' <credentials login="' . $user . '" password="' . $pass . '"/>' . "\n" .
            ' <importDataOptions planOrActuals="' . $actuals . '" allowParallel="' . $allowParallel . '" moveBPtr="' . $moveBPtr . '" />' . "\n" .
            ' <version name="' . $version . '" isDefault="' . $isDefault . '" />' . "\n" .
            ' <rowData>' . "\n" .
            '  <header>' . implode('|', $fields) . '</header>' . "\n" .
            '  <rows>' . "\n";
        foreach ($data as $record) {
            $xmlCreate .= '   <row>' . implode('|', $record) . "</row>\n";
        }

        $xmlCreate .=
            '  </rows>' . "\n" .
            ' </rowData>' . "\n" .
            '</call>';

        if ($output) {
            header('content-type: application/xml');
            exit($xmlCreate);
        }
        $response = self::send($xmlCreate);
        return $response;
    }

	/**
	* Adaptive exportData
	*
	* @param string $user
	* @param string $pass
	* @param string $version
	* @param string $start      i.e. Jan-2014
	* @param string $end        i.e. Jan-2014
	* @param string $level
	* @param array  $accounts   array of accounts
	* @param array  $dimensions array of dimensions
	* @param bool   $isDefault  if true, will select default version
	*
	* @return array of records
	*/
	public static function exportData(
		$user, $pass, $version, $start, $end, $levels, $accounts,
		$dimensions = array(), $filterDimensions = array(), $isDefault = false,
		$dumpXML = false
	) {
		$xmlCreate =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<call method="exportData">' . "\n" .
            ' <credentials login="'.(string)$user.'" password="'.(string)$pass.'" />' . "\n" .
            ' <version name="'.(string)$version.'" isDefault="'.(bool)$isDefault.'" />' . "\n" .
            ' <format useInternalCodes="true" includeUnmappedItems="true" />' . "\n" .
            ' <filters>';
		if (count($accounts) > 0) {
			$xmlCreate .= '  <accounts>' . "\n";
			foreach ((array)$accounts as $account) {
				$xmlCreate .= '   <account code="' . (string)$account .
					'" isAssumption="false" includeDescendants="true" />' . "\n";
			}
			$xmlCreate .= '  </accounts>' . "\n";
		}
		if (count($levels) > 0) {
			$xmlCreate .= '  <levels>' . "\n";
			foreach ($levels as $level) {
				$xmlCreate .= '   <level name="'.(string)$level.'" isRollup="false" includeDescendants="true" />' . "\n";
			}
			$xmlCreate .= '  </levels>' . "\n";
		}
		$xmlCreate .= '  <timeSpan start="'.(string)$start.'" end="'.(string)$end.'" />' . "\n";
		if (count($filterDimensions) > 0) {
			$xmlCreate .= '  <dimensionValues> . "\n"';
			foreach ($filterDimensions as $dim => $val) {
				$xmlCreate .= '   <dimensionValue dimName="' . $dim . '" name="' .
					$val . '" directChildren="true" />' . "\n";
			}
			$xmlCreate .= '  </dimensionValues>' . "\n";
		}
		$xmlCreate .= ' </filters>' . "\n";
		$dimensions = (array)$dimensions;
		if (count($dimensions) > 0) {
			$xmlCreate .= ' <dimensions>' . "\n";
			foreach ($dimensions as $dimension) {
				$xmlCreate .= '  <dimension name="' . $dimension . '" />' . "\n";
			}
			$xmlCreate .= ' </dimensions>' . "\n";

		}
		$xmlCreate .= ' <rules includeZeroRows="false" includeRollups="false" markInvalidValues="false" markBlanks="false" timeRollups="false"></rules>' . "\n";
		$xmlCreate .= '</call>' . "\n";
		if ($dumpXML) {
			header('content-type:application/xml');
			exit($xmlCreate);
		}
		$response = self::send($xmlCreate);
		$data = false;
		if (($response = simplexml_load_string($response)) !== false) {
			$attr = $response->attributes();
			if ((bool)$attr['success'] === true) {
				$data = str_getcsv((string)$response->output, "\n");
			}
		}
		return $data;
	}

	/**
	* get Adaptive versions
	*
	* @param string $user
	* @param string $pass
	*
	* @return mixed list of versions
	*/
	public static function exportVersions($user, $pass)
	{
		$results = false;
		$xmlCreate =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<call method="exportVersions">' . "\n" .
            ' <credentials login="'.$user.'" password="'.$pass.'" />' . "\n" .
            '</call>';
		$response = self::send($xmlCreate);
		if ($results = simplexml_load_string($response)) {
			$versions = (array)$results->output->versions;
			$results = self::parseVersions($versions);
		} else {
			$results = 'Error occurred while retrieving versions...' .
				__FILE__ . ' (' . __LINE__ . ')';
		}
		return $results;
	}

	/**
	* get Adaptive levels
	*
	* @param string $user
	* @param string $pass
	*
	* @return mixed list of levels
	*/
	public static function exportLevels($user, $pass)
	{
		$results = false;
		$xmlCreate =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<call method="exportLevels">' . "\n" .
            ' <credentials login="'.$user.'" password="'.$pass.'"/>' . "\n" .
            '</call>';
		$response = self::send($xmlCreate);
		if ($results = simplexml_load_string($response)) {
			$levels = (array)$results->output->levels;
			$results = self::parseLevels($levels);
			//$results = $response;
		} else {
			$results = 'Error occurred while retrieving levels...' .
				__FILE__ . ' (' . __LINE__ . ')';
		}
		return $results;
	}

	/**
	* get Adaptive dimensions
	*
	* @param string $user
	* @param string $pass
	*
	* @return mixed list of dimensions
	*/
	public static function exportDimensions($user, $pass)
	{
		$results = false;
		$xmlCreate =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<call method="exportDimensions">' . "\n" .
            ' <credentials login="'.$user.'" password="'.$pass.'"/>' . "\n" .
            '</call>';
		$response = self::send($xmlCreate);

		if ($results = simplexml_load_string($response)) {
			exit('<pre>'.print_r($results,true).'</pre>');
		} else {
			$results = 'Error occurred while retrieving dimensions...' .
				__FILE__ . ' (' . __LINE__ . ')';
		}
		return $results;
	}

	/**
	* get Adaptive accounts
	*
	* @param string $user
	* @param string $pass
	* @param bool   $output
	*
	* @return mixed list of accounts
	*/
	public static function exportAccounts($user, $pass, $output = false)
	{
		$results = false;
		$xmlCreate =
			'<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
			'<call method="exportAccounts">' . "\n" .
			'   <credentials login="' . $user . '" password="' . $pass . '"/>' . "\n" .
			'</call>';
		$response = self::send($xmlCreate);
		header('content-type:application/xml');
		exit($response);
		if ($results = simplexml_load_string($response)) {
			if (is_array($results)) {
				$accounts = $results->output->accounts;
				if ($output) {
					return $accounts;
				}
				$results = self::parseAccounts($accounts);
			} else {
				$results = $response;
			}
		} else {
			$results = 'Error occurred while retrieving accounts...' .
				__FILE__ . ' (' . __LINE__ . ')';
		}
		return $results;
	}

	/**
	 * erase actuals
	 * 
	 * @param string $user
	 * @param string $pass
	 * @param string $version
	 * @param string $type		GL/CUBE/CUSTOM
	 * @param string $sheet		sheet name, required for cube sheets
	 * @param string $start		period start date (MMM-YYYY)
	 * @param string $end		period end date (MMM-YYYY)
	 * @param string $includeCellNotes	"true"/"false"
	 *
	 * @return bool true on success
	 */
	public static function eraseActuals(
		$user, $pass, $version, $type, $sheet, $start, $end, $includeCellNotes = "false", 
		$dumpXML = false
	) {
		$results = false;
		$xmlCreate =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<call method="eraseActuals">' . "\n" .
            '  <credentials login="'.$user.'" password="'.$pass.'" />' . "\n" .
			'  <eraseOptions ' . "\n" .
			'    actualsVersionName="'.$version.'" ' . "\n" .
			'    accountType="'.strtoupper($type).'" ' . "\n";
		if (strtoupper($type) == 'CUBE') {
			$xmlCreate .= '    cubeSheetName="'.$sheet.'" ' . "\n";
		}
		$xmlCreate .=
			'    start="'.$start.'" ' . "\n" .
			'    end="'.$end.'" ' . "\n" .
			'    includeCellNotes="'.$includeCellNotes.'" />' . "\n" .
            '</call>';

		if ($dumpXML) {
			header('content-type:application/xml');
			exit($xmlCreate);
		}
		$response = self::send($xmlCreate);
		$results = self::processResponse($response);
		return $results;
	}

	/**
	* Send request to Adaptive, return the response
	*
	* @param string $xmlRequest valid xml submission string
	*
	* @return string xml-based response
	*/
	public static function send($xmlRequest)
	{
		$headers = array(
			"Content-type: application/xml",
			"Content-length: " . strlen($xmlRequest),
			"Connection: close",
		);

		try {
			// INITIATE CURL
			$curl = curl_init();
			$curlOptions = array(
				CURLOPT_URL => self::URL,
				CURLOPT_HEADER => false,
				CURLOPT_HTTPHEADER => $headers,
				CURLINFO_HEADER_OUT => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RETURNTRANSFER => true,
				//Seconds until timeout
				CURLOPT_TIMEOUT => 300,
				// set to POST
				CURLOPT_POST => true,
				//handle SSL cert
				CURLOPT_SSL_VERIFYPEER => false,
				// BUILD THE POST BODY
				CURLOPT_POSTFIELDS => $xmlRequest,
				// set to verbose (or not)
				CURLOPT_VERBOSE => true
			);
			curl_setopt_array($curl, $curlOptions);

			$response = curl_exec($curl);
			if ($response == false) {
				throw new Exception(curl_error($curl), curl_errno($curl));
			}
			curl_close($curl);
		} catch (Exception $e){
			trigger_error(
				sprintf(
					'Curl failed with error #%d: %s',
					$e->getCode(), $e->getMessage()
				),
				E_USER_ERROR
			);
		}
		return $response;
	}

	public static function processResponse($xml)
	{
		$status = '';
		if (($xml = simplexml_load_string($xml)) !== false) {
			foreach ($xml->attributes() as $name => $val) {
				if ($name == 'success' && $val == 'true') {
					if (count($xml->messages) > 0) {
						foreach ($xml->messages->message as $message) {
							$status .= '<span class="error">' . (string)$message . '</span>' . '<br />';
						}
					} else {
						$status = '<span class="success">Import Succeeded!</span><br />';
					}
				} else {
					if (count($xml->messages) > 0) {
						foreach ($xml->messages->message as $message) {
							$status .= '<span class="error">' . (string)$message . '</span>' . '<br />';
						}
					} else {
						$status = '<span class="error">An unknown error occurred, please review the Adaptive import history for details.</span><br />';
					}
				}
			}
		} else {
			$status = '<span class="error">Adaptive returned an invalid response.  Please review the Adaptive import history for details.</span></br />';
		}
		return $status;
	}

	/**
	* parse a potentially nested array
	*
	* @param array $arr to parse
	*
	* @return array of clean values
	*/
	private static function parseVersions($arr)
	{
		$return = false;
		if (count($arr) > 0) {
			if (array_key_exists('version', $arr)) {
                if (is_object($arr['version'])) {
                    $version = $arr['version'];
                    $attr = $version->attributes();
                    switch ($attr['type']) {
                        case 'VERSION_FOLDER':
                            $return = array_merge(
                                $return,
                                (array)self::parseVersions((array)$version)
                            );
                            break;

                        case 'ACTUALS':
                            continue;
                        case 'PLANNING':
                            $return[] = array(
                                'name' => (string)$attr['name'],
                                'type' => (string)$attr['type'],
                                'default' => (string)$attr['isDefaultVersion']
                            );
                            break;
                    }
                } else {
				$return = array();
                    $versions = $arr['version'];
				    foreach ($versions as $version) {
					    $attr = $version->attributes();
					    switch ($attr['type']) {
						    case 'VERSION_FOLDER':
							    $return = array_merge(
                                    $return,
                                    (array)self::parseVersions((array)$version)
                                );
							    break;

						    case 'ACTUALS':
							    continue;
						    case 'PLANNING':
							    $return[] = array(
								    'name' => (string)$attr['name'],
								    'type' => (string)$attr['type'],
								    'default' => (string)$attr['isDefaultVersion']
							    );
							    break;
					    }
				    }
                }
			}
		}
		if (is_array($return)) {
			sort($return);
		}
		return $return;
	}

	/**
	* parse a potentially nested array
	*
	* @param array $arr to parse
	*
	* @return array of clean values
	*/
	private static function parseLevels($arr)
	{
		$return = array();
		if (count($arr) > 0) {
			if (array_key_exists('level', $arr)) {
				$levels = $arr['level'];
				foreach ($levels as $level) {
					$attr = $level->attributes();
//					echo print_r($attr,true).'<br />';
//					continue;
					if (array_key_exists('level', $level)) {
						$return = array_merge(
							$return, self::parseLevels((array)$level)
						);
						continue;
					}
					if ( ! empty((string)$attr['name'])) {
						$return[] = array('name' => (string)$attr['name']);
					}
				}
			}
		}
		return $return;
	}

	/**
	* parse a potentially nested array
	*
	* @param array $arr to parse
	*
	* @return array of clean values
	*/
	private function parseAccounts($arr)
	{
		$results = array();
		if (count($arr) > 0) {
			if (array_key_exists('account', $arr)) {
				$accounts = $arr['account'];
				foreach ($accounts as $account) {
					$attr = $account->attributes();
					if (array_key_exists('account', $account)) {
						$results = array_merge(
							$results, self::parseAccounts((array)$account)
						);
						continue;
					}
					$code = (string)$attr['code'];
					$name = (string)$attr['name'];
//					if ($code >= '4000') {
						$results[] = $code;
//					}
				}
				$results = array_unique($results);
				sort($results);
			}
		}
		return $results;
	}
}

