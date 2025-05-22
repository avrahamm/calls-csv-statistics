<?php
$apiKey = "b9c9e0c9e04642f5a66b2278c4cb1e25";
$ip = "116.52.75.78";

/**
 * I tried a ip-test.php. I got a message
 * ```bash
 * IP to geolocation lookup for domain or service name is not supported on your current subscription. This feature is available to all paid subscriptions only.
 * ```
 * So will leave it for now.
 * I will keep the test files for the future.
 * //$ip = "116.52.75.78,3.3.3.3";
 */

$ips = array("3.3.3.3", "4.4.4.4", "5.5.5.5", "6.6.6.6", "7.7.7.7");
$fields = "continent_code";
$lang = "en";

echo "<h2>Single IP Lookup</h2>";
$location = get_geolocation($apiKey,
    ip: $ip,
    lang: $lang,
    fields: $fields,
);
$decodedLocation = json_decode($location, true);
echo "<pre>;";
print_r($decodedLocation);
echo "</pre>;";

echo "<h2>Batch IP Lookup</h2>";
echo "<p>Using the batch endpoint to lookup multiple IPs in a single request:</p>";
$batchLocations = get_batch_geolocation($apiKey, $ips, $lang, $fields);
$decodedBatchLocations = json_decode($batchLocations, true);
echo "<pre>;";
print_r($decodedBatchLocations);
echo "</pre>;";

function get_geolocation($apiKey, $ip, $lang = "en", $fields = "*", $excludes = "") {
$url = "https://api.ipgeolocation.io/ipgeo?apiKey=".$apiKey."&ip=".$ip."&lang=".$lang."&fields=".$fields."&excludes=".$excludes;
$cURL = curl_init();

curl_setopt($cURL, CURLOPT_URL, $url);
curl_setopt($cURL, CURLOPT_HTTPGET, true);
curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
'Content-Type: application/json',
'Accept: application/json',
//'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
));

return curl_exec($cURL);
}

/**
 * Get geolocation data for multiple IP addresses in a single batch request
 * 
 * @param string $apiKey API key for ipgeolocation.io
 * @param array $ips Array of IP addresses
 * @param string $lang Language for the response
 * @param string $fields Fields to include in the response
 * @param string $excludes Fields to exclude from the response
 * @return string JSON response from the API
 */
function get_batch_geolocation($apiKey, $ips, $lang = "en", $fields = "*", $excludes = "") {
    $url = "https://api.ipgeolocation.io";
    $cURL = curl_init();

    // Prepare the query parameters
    $queryParams = http_build_query([
        'apiKey' => $apiKey,
        'lang' => $lang,
        'fields' => $fields,
        'excludes' => $excludes
    ]);

    $fullUrl = $url . '?' . $queryParams;

    // Convert the IP array to JSON
    $jsonPayload = json_encode($ips);

    curl_setopt($cURL, CURLOPT_URL, $fullUrl);
    curl_setopt($cURL, CURLOPT_POST, true);
    curl_setopt($cURL, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Content-Length: ' . strlen($jsonPayload)
        //'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
    ));

    $response = curl_exec($cURL);

    if (curl_errno($cURL)) {
        echo 'Curl error: ' . curl_error($cURL);
    }

    curl_close($cURL);

    return $response;
}
