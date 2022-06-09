<?php
	header('Access-Control-Allow-Origin: https://etel.sabacloud.com');

	if( !$_POST )
	{
		// if there is no post then quit	   
	   	exit;
	   	die();
	}

	if ( isset( $_POST['URL'] ) )
	{	
		$URL = $_POST['URL'];

		if ( strpos( $URL, "sabacloud.com" ) == false )
		{			
			die();
		}
	} 
	// get the time - london time
	date_default_timezone_set('Europe/London');
	$requestTime = new DateTime("now");
	$endTime = date("Y-m-d H:i:s", strtotime( $requestTime->format("Y-m-d H:i:s"))+(60*15) );
	
	// get the token then get the list of courses
	$access_token = callCurl();	
	$getCourseList = callCurlAuth( $access_token );
	$courseList = array();

	foreach (  $getCourseList['out_course_list'] as $course )
 	{
 		if ( strpos( $course['course_title'], "Light Vehicle MOT") !== false )
 		{
 			$courseInfo = array( 'CourseID' => $course['course_id'],
 				'CourseTitle' => $course['course_title']
 				);

 			array_push($courseList, $courseInfo);
 		} 		
 	}

	// return package
	$data = array(  'CourseList' =>  $courseList,
					'Token' => $access_token,
					'RequestTime' => $requestTime->format("Y-m-d H:i:s"),
					'EndTime' => $endTime
					 );

	echo json_encode($data);

	// request with the token - will have to see if this is what we call going forard
	function callCurlAuth( $token )
	{		
		$url = 'https://emw1-apigw.dm-em.informaticacloud.com/t/edxmnqmnhspezeqwswo0ui.com/GETCOURSELIST?in_partner=ETEL';
		// ch = curl handle
		$ch = curl_init();		
		// set the curl options https://www.php.net/manual/en/function.curl-setopt.php
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// set back to get ( we posted for teh first call )
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
		// dont need to verify ourselfs ( i.e. use ssl cert)
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, $options );
		// attempts to set the bearer authorization token in the header ( yes all three of them )
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json;'
			// "Authorization: Bearer " .$token, 
			 ));
		curl_setopt($ch, CURLOPT_HTTPAUTH ,  CURLAUTH_BEARER );
		curl_setopt($ch, CURLOPT_XOAUTH2_BEARER, $token );

		$requestData = curl_exec($ch);
		curl_close($ch);
		if ($requestData === FALSE) {	  		
	  		// print_r( "cUrl error (#%d): %s<br>\n" . curl_errno($ch) . htmlspecialchars(curl_error($ch)) );
	        return "cUrl error (#%d): %s<br>\n" . curl_errno($ch) . htmlspecialchars(curl_error($ch));
		}		
		$jsonObj = json_decode($requestData, true);			
		return $jsonObj;		
	}
	
	function callCurl()
	{
		// required for the request
		$client = "1TlV3EgU82lia2HPpp9gOL";
		$secret = "R7DS4cfAN"; 
		$url = "https://dm-em.informaticacloud.com/authz-service/oauth/token";	
		$value = "access_token";

		// create the payload
		$options = array(
		    'client_id' => $client,
		    'client_secret' => $secret,
		    'redirect_uri' => "https://etel.sabacloud.com",
		    'grant_type' => 'client_credentials'
		);

		// ch = curl handle
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $options );
		$requestData = curl_exec($ch);
		curl_close($ch);

		if ($requestData === FALSE) {
	    // printf("cUrl error (#%d): %s<br>\n", curl_errno($ch), htmlspecialchars(curl_error($ch))) ;
	           return "cUrl error (#%d): %s<br>\n" . curl_errno($ch) . htmlspecialchars(curl_error($ch));
		}	
		$jsonObj = json_decode($requestData, true);
		// cast the return as a json object keeping structure	
		if ($value != null)
		{
			return $jsonObj[$value];
		}
		return $jsonObj;		
	}
?>