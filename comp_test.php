<?php
require_once 'lib/nusoap.php';
include('lib/global_vars_functions.php'); 
ini_set( 'max_execution_time', '7200' );

//print_r($_SERVER);

//echo $_SERVER['SERVER_NAME'];
//exit;

//print_r($_SERVER);
//exit;

// Login Process to the siebel CRM OnDemand server

/*

//media labs test

$serverpath = $server_media_lab."IdService.svc";
$soapaction = "http://api.scp.stage.medialabinc.com/IIdService/GetAllCommunityCUIds";
$param ='<?xml version="1.0" encoding="utf-8"?>
					  <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
					  <s:Header>
					  <o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
					  <o:UsernameToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
					  <o:Username>LennarApiUser</o:Username>
					  <o:Password  Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">medialab99</o:Password>
					  </o:UsernameToken>
					  </o:Security>
					  </s:Header>
					  <s:Body>
					  <GetAllCommunityCUIds xmlns="http://api.scp.stage.medialabinc.com">
					  </GetAllCommunityCUIds>
					  </s:Body>
					  </s:Envelope>';
$client = new nusoap_client($serverpath);
$client->setHTTPProxy('http://www-proxy-adc.us.oracle.com' , 80 , '' , '');
$debug='';
//$client->setHeaders("$debug");
$client->soap_defencoding = 'UTF-8';
$response = $client->send($param,$soapaction,0,360);
$soapdata=htmlspecialchars($client->responseData, ENT_QUOTES);

//$dbg=htmlspecialchars($client->debug_str, ENT_QUOTES);
echo '<pre>';

//echo 'debug is '.$dbg;
echo '</pre>';
echo '<pre>';
print_r($response);
echo '</pre>';
echo $serverpath;
//exit;


*/

//siebel ondemand test
error_reporting('E_ALL');



$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$siebel_conn_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $siebel_conn_headers);
//curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
//curl_setopt($handle, CURLOPT_PROXY, 'www-proxy.oracleoutsourcing.com');
curl_setopt($ch, CURLOPT_PROXY, 'http://www-proxy-adc.us.oracle.com');
curl_setopt($ch, CURLOPT_PROXYPORT, 80);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch,CURLOPT_PROXYAUTH,CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, true);
$data = curl_exec($ch);


$sessionid = substr($data,(strpos($data,"Set-Cookie:")+23),(strpos($data,";")-strpos($data,"Set-Cookie:")-23));
curl_close($ch);

//print_r(error_get_last());
//echo '<br><br>Printing curl info for extra details:<br>';
//print_r(curl_getinfo($ch));

//echo '<br><br>any curl errors:<br>';
//echo curl_error($ch);

echo '<b><br><br>Below is Response with web service call to '.$siebel_conn_url.' using proxy http://www-proxy-adc.us.oracle.com</b><br><br>';
//print_r($data);

//echo '<br><br>session id is<br>'. $sessionid;

//$dbg=htmlspecialchars($client->debug_str, ENT_QUOTES);
echo '<pre>';

//echo '<br><br>debug is<br> '.$dbg;
echo '<br><br></pre>';

$serverpath = $crmdomain . "/Services/Integration;jsessionid=$sessionid";
			$param ="<PageSize>5</PageSize>
			<ListOfOpportunity>
			<Opportunity>
			<OpportunityId></OpportunityId>
			<Owner></Owner>
			<CustomObject1ExternalSystemId></CustomObject1ExternalSystemId>
			<CustomText4></CustomText4>
			<SalesStage></SalesStage>
			<OpportunityName></OpportunityName>
			<LeadSource></LeadSource>
			<CloseDate></CloseDate>
			</Opportunity>
			</ListOfOpportunity>
			<StartRowNum>0</StartRowNum>";
			$client = new nusoap_client($serverpath);
                        $client->setHTTPProxy('http://www-proxy-adc.us.oracle.com' , 80 , '' , '');
			$debug='';
			$client->setHeaders("$debug");
			$response = $client->call($opportunity_query_method,$param,$opportunity_namespace,$opportunity_query_soapaction);
			$soapdata=htmlspecialchars($client->responseData, ENT_QUOTES);
		    $check=$response['LastPage'];

echo '<BR>Below is response from Opportunity<BR><BR>';
echo '<pre>';
print_r($response);
echo '</pre>';
//echo 'exiting script';
//exit;


 //Script to Logoff from Siebel Server
		                $logoff=$logoff.$sessionid."?command=logoff";
	 			        $ch1 = curl_init();
	 			        curl_setopt($ch1, CURLOPT_PROXY, 'http://www-proxy-adc.us.oracle.com');
	 			        curl_setopt($ch1, CURLOPT_PROXYPORT, 80);
	 			        curl_setopt($ch1, CURLOPT_URL,$logoff);
	 			        curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
	 			        $data1 = curl_exec($ch1);
	 			        $st=curl_getinfo($ch1,CURLINFO_HTTP_CODE);
	 		            curl_close($ch1);
                                   if ($st=200)
    { echo "Logoff Succeeded\n";
    }
    else
    echo "Logoff Failed";

echo '<br><br>';



exit;



//DOCUSIGN TEST
$serverpath = "https://demo.docusign.net/api/3.0/api.asmx?WSDL";

$param='<?xml version="1.0" encoding="utf-8"?>
      <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
         <soap:Header>
      <o:Security xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
      <o:UsernameToken xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
      <o:Username>['.$IntegratorsKey.']'.$UserID.'</o:Username>
      <o:Password  Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$docusign_pwd.'</o:Password>
      </o:UsernameToken>
      </o:Security>
      </soap:Header>
         <soap:Body>
            <RequestStatuses xmlns="http://www.docusign.net/API/3.0">
               <EnvelopeStatusFilter>
               <AccountId>'.$AccountID.'</AccountId>
                      <EnvelopeIds>';
$param=$param.'<EnvelopeId>5543cb26-6f7e-40f5-8339-83a804ab0f52</EnvelopeId>';
$param=$param.'</EnvelopeIds>
         </EnvelopeStatusFilter>
      </RequestStatuses>
   </soap:Body>
</soap:Envelope>';
$soapaction = "http://www.docusign.net/API/3.0/RequestStatuses";
$client = new nusoap_client($serverpath,"wsdl");
$client->setHTTPProxy('http://www-proxy-adc.us.oracle.com' , 80 , '' , '');
$proxy = $client->getProxy();
$err = $proxy->getError();
$debug='';
//$client->setHeaders("$debug");
$client->soap_defencoding = 'UTF-8';
$response = $client->send($param,$soapaction,'');
$soapdata=htmlspecialchars($client->responseData, ENT_QUOTES);

echo 'below is another web service test <BR><BR>';
print_r($response);



/*
//docusign test

//login
$url='https://demo.docusign.net/restapi/v2/login_information';


# headers and data (this is API dependent, some uses XML)
$headers = array(
'Accept: application/json',
'Content-Type: application/json',
'X-DocuSign-Authentication: <DocuSignCredentials><Username>docusigndemo@lennar.com</Username><Password>lennar2011</Password><IntegratorKey>LENN-531c83ef-136a-4c88-8827-8ef82f4326db</IntegratorKey></DocuSignCredentials>'
);



$handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $url);
curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($handle, CURLOPT_PROXY, 'www-proxy.oracleoutsourcing.com');
curl_setopt($handle, CURLOPT_PROXY, 'http://www-proxy-adc.us.oracle.com');
curl_setopt($handle, CURLOPT_PROXYPORT, 80);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
$json_response = curl_exec($handle);


$status = curl_getinfo($handle, CURLINFO_HTTP_CODE);


if ( $status != 200 )
{

	echo "error calling webservice, status code is :" . $status;
	print_r(curl_getinfo());
	print_r($json_response);
	curl_error($handle);
	exit(-1);
}

$response = json_decode($json_response, true);
$accountId = $response["loginAccounts"][0]["accountId"];
$baseUrl = $response["loginAccounts"][0]["baseUrl"];

//echo "accountId " . $accountId . "<br>";
//echo "baseUrl " . $baseUrl . "<br>";
echo '<br><br><b>1.Below is response from web service call to a different url using the proxy</b><br><br>';
echo 'Json Response'.$json_response;

//print_r( $response);


curl_close($handle);

*/

/*
 //function to connect to oracle database
  function db_connect($server)
    {
    
      $db = "vmohslenn001.oracleoutsourcing.com:13510/TLEN3O.ORACLEOUTSOURCING.COM";
      $result = oci_pconnect("xxphp", "con1rac1s", $db);
      
       if (!$result)
        return false;
       return $result;
  	}  


$db_conn1 = db_connect($e_serv);
	
	if(!$db_conn1)
	{
		echo '<br><h2>Unable to connect to oracle database!';
print_r(error_get_last());
		exit;
	}
	
		$qryp=" select opportunity_id from dj_esign where rownum<2";
		$stidp = oci_parse($db_conn1, $qryp);
		oci_execute($stidp);
		
		while ($row2 = oci_fetch_array($stidp, OCI_RETURN_NULLS + OCI_ASSOC))
			{
			$test= trim($row2['OPPORTUNITY_ID']);
			}

echo '<BR><BR>2.Oracle test result is '.$test;

		

    oci_close($db_conn1);
*/


/*

//ftp connection test

$ftp_server='141.146.252.230';
$ftp_user_name='infplennj';
$ftp_user_pass='33JrqtrE';


$conn_id = ftp_connect($ftp_server);

// login with username and password


  $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 


//check connection  

if ((!$conn_id) || (!$login_result))

  {   echo "FTP connection has failed!";
   echo "Attempted to connect to $ftp_server for user $ftp_user_name.";
   exit;
  } 

*/

?>
