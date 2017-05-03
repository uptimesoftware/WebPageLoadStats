<?php
	if (getenv('UPTIME_URL')) {
		$statusURL = getenv('UPTIME_URL');
	} else $statusURL = $argv[2];
	
	if (getenv('UPTIME_REDIRECTACTION')) {
		$followRedirect = getenv('UPTIME_REDIRECTACTION');
	} else $followRedirect = $argv[3];
	
	if (getenv('UPTIME_VERBOSECERT')) {
		$verboseCert = getenv('UPTIME_VERBOSECERT');		
	} else $verboseCert = $argv[4];

	$fileName="cert.log.".rand();
	
	// grab CURL version for processing cert different ways
	$curlVersion = curl_version();
	$curlVer = strtok($curlVersion['version'], '.') . '.' . strtok('.');

	function curlRequest($FromLocation,$VerifyPeer,$VerifyHost,$fileName) {
		global $statusURL, $verboseCert, $followRedirect, $curlVer;
		
		// Initialize CURL with providing full https URL of the file location
		$Channel = curl_init($FromLocation);

		// We are not sending any headers
		curl_setopt($Channel, CURLOPT_HEADER, 1);

		// VERBOSE and output file options - set to 1 for debugging and uncomment the next two lines
		curl_setopt($Channel, CURLOPT_VERBOSE, 0);
 		//$error_FH = fopen($fileName,"w");
		//curl_setopt($Channel, CURLOPT_STDERR, $error_FH);

		// Disable PEER SSL Verification: If you are not running with SSL or if you don't have valid SSL
		curl_setopt($Channel, CURLOPT_SSL_VERIFYPEER, 0);

		// ENABLE HOST (the site you are sending request to) SSL Verification,
        // if Host can have certificate which is invalid / expired / not signed by authorized CA.
		curl_setopt($Channel, CURLOPT_SSL_VERIFYHOST, 2);
		
		//connect only dont load page
		//curl_setopt($Channel, CURLOPT_CONNECT_ONLY, 1);
		
		// load certificate info
		curl_setopt($Channel, CURLOPT_CERTINFO, 1);
		
		// dont output the page
		curl_setopt($Channel, CURLOPT_RETURNTRANSFER, true);
		
		// get last modified date of file
		curl_setopt($Channel, CURLOPT_FILETIME, true);

		// follow redirects
		if ($followRedirect == 'true') {
			curl_setopt($Channel, CURLOPT_FOLLOWLOCATION, true);
		} else {
			curl_setopt($Channel, CURLOPT_FOLLOWLOCATION, false);
		}
		// Set SSL version to default most secure
		curl_setopt($Channel, CURLOPT_SSLVERSION, 0);

		// Execute CURL command
		curl_exec($Channel);
 		
		$info = curl_getinfo($Channel);

		// Close the CURL channel
		curl_close($Channel);
 		
		if (isset($error_PH)) {
			fclose($error_FH);
		}
	
		//output everything for testing
		//print_r($info);
		
		// main output
		
		if (isset($info['content_type'])) {
			$content = preg_split('/;/', $info['content_type'], -1, PREG_SPLIT_NO_EMPTY);
			echo "content_type " . trim($content[0]) . "\r\n";
			echo "content_encoding " . trim($content[1]) . "\r\n";
		}
		
		if ($info['http_code']) {
			$http_codes = parse_ini_file("responsecodes.ini");
			
			// echo results
			echo "http_code ".$info['http_code'] . "-" . $http_codes[$info['http_code']]."\r\n";
		}
		
		echo "header_size ".$info['header_size']."\r\n";
		echo "request_size ".$info['request_size']."\r\n";
		
		if ($info['filetime'] != -1) {
			//echo "filetime ".$info['filetime']."\r\n";
			echo "filetime ".date("d-m-Y H:i:s", $info['filetime'])."\r\n";
		}
		
		if ($info['redirect_count'] == 1) {
			echo "redirect_count ".$info['redirect_count']."\r\n";
			echo "redirect_time ".$info['redirect_time']."\r\n";
		}
		
		echo "namelookup_time ".($info['namelookup_time'] * 1000)."\r\n";
		echo "connect_time ".($info['connect_time'] * 1000)."\r\n";
		echo "pretransfer_time ".($info['pretransfer_time'] * 1000)."\r\n";
		echo "starttransfer_time ".($info['starttransfer_time'] * 1000)."\r\n";
		echo "total_time ".($info['total_time'] * 1000)."\r\n";
		echo "size_download ".$info['size_download']."\r\n";
		echo "speed_download ".$info['speed_download']."\r\n";
		echo "download_content_length ".$info['download_content_length']."\r\n";

		// PRINT OUT THE SSL INFO IF ANY	
		
		if ($info['ssl_verify_result']) {
			echo "ssl_verify_result ".$info['ssl_verify_result']."\r\n";
			if ($verboseCert == 'true') {
				if ($curlVer < '7.51' ) {
					if (isset($info['certinfo'][0]['Issuer'])) {
						echo "cert_issuer ".$info['certinfo'][0]['Issuer']."\r\n";
					} else echo "cert_issuer blank\r\n";
					
					if (isset($info['certinfo'][0]['Subject'])) {
						echo "cert_subject ".$info['certinfo'][0]['Subject']."\r\n";
					} else echo "cert_subject blank\r\n";
				} 
				
				if ($curlVer >= '7.51' ) {
					//for testing raw output prior to parsing and cleaning
					//echo "cert_issuer string ".$info['certinfo'][0]['Issuer']."\r\n";
					//echo "cert_subject string ".$info['certinfo'][0]['Subject']."\r\n";
					//replace ugly characters in certificate registrations

					if (isset($info['certinfo'][0]['Issuer'])) {
						$cert_issuer = str_getcsv ( cookDN($info['certinfo'][0]['Issuer']));
						foreach ($cert_issuer as $index) {
							echo "cert_issuer_".trim($index)."\r\n";
						}
					} else echo "cert_issuer blank\r\n";
					
					if (isset($info['certinfo'][0]['Subject'])) {
						$cert_subject = str_getcsv( cookDN($info['certinfo'][0]['Subject']));
						foreach ($cert_subject as $index) {
							echo "cert_subject_".trim($index)."\r\n";
						}
					}  else echo "cert_subject blank\r\n";	
				}
			}

			$expiryDate	= date('Y-m-d H:i:s T', (strtotime($info['certinfo'][0]['Expire date'],0)));
			$curDate = date('Y-m-d H:i:s T', strtotime("now"));
			echo "certificate_expiry_date ".$info['certinfo'][0]['Expire date']."\r\n";
			echo "certificate_expiry_days ".dateDiffInDays($expiryDate, $curDate)."\r\n";
			
			// info about cert?
			//for testing
			//print_r($info['certinfo'][0]);
		}
	}

	function cookDN($DN) {
		$replaceMatch = array(' ,','O = "','OU = "','=','  ');
		$replaceWith = array(',','"O = ','"OU = ','',' ');
		$cookedDN = str_replace($replaceMatch, $replaceWith, $DN);
		
		//echo "cookedDN - ".$which." ".$cookedDN."\r\n";
		return $cookedDN;		
	}
	
	function dateDiffInDays($dateExpiry, $dateCur) {
		// Convert date/time to seconds and get the difference and then return the amount in days
		//print "\nFrom: $dateExpiry\nCurrent: $dateCur\n";
		$dateExpiry = strtotime($dateExpiry, 0);
		$dateCur = strtotime($dateCur, 0);
		$difference = $dateExpiry - $dateCur;	// Difference in seconds
		$difference = ($difference / 60 / 60 / 24);
		$difference = intval($difference);
		// Make sure there are no negative days left
		/*
		if ($difference < 0) {
			$difference = 0;
		}*/
		return $difference;
	}

// Function Usage
$output = curlRequest("$statusURL","false","false",$fileName);
?>