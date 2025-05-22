<style>;
    table, th, tr, td {
        border: 1px solid black;
        border-collapse: collapse;
    }

    th, td {
        padding: 5px 30px;
    }
</style>;

<?php
$apiKey = "b9c9e0c9e04642f5a66b2278c4cb1e25";
$fields = "continent_code";
$ips = array("3.3.3.3", "4.4.4.4", "5.5.5.5", "6.6.6.6", "7.7.7.7");

echo "<h2>Using Batch IP Lookup</h2>";

// Get geolocation data for all IPs in a single batch request
$batchLocations = get_batch_geolocation($apiKey, $ips);
$decodedBatchLocations = json_decode($batchLocations, true);

echo "<table>";
echo "<tr>";
echo "<th>IP</th>";
echo "<th>Continent</th>";
echo "<th>Country</th>";
echo "<th>Organization</th>";
echo "<th>ISP</th>";
echo "<th>Languages</th>";
echo "<th>Is EU Member?</th>";
echo "<th>Currency</th>";
echo "<th>Timezone</th>";
echo "</tr>";

foreach ($decodedBatchLocations as $location) {
    echo "<tr>";

    if (isset($location['message']) && $location['message'] != '') {
        echo "<td>;".$location['ip']."</td>;";
        echo "<td>;".$location['message']."</td>;";
        echo "<td colspan='7'></td>;";
    } else {
        echo "<td>;".$location['ip']."</td>;";
        echo "<td>;".$location['continent_name']." (".$location['continent_code'].")</td>;";
        echo "<td>;".$location['country_name']." (".$location['country_code2'].")</td>;";
        echo "<td>;".$location['organization']."</td>;";
        echo "<td>;".$location['isp']."</td>;";
        echo "<td>;".$location['languages']."</td>;";

        if ($location['is_eu'] == true) {
            echo "<td>;Yes</td>;";
        } else {
            echo "<td>;No</td>;";
        }

        echo "<td>;".$location['currency']['name']."</td>;";
        echo "<td>;".$location['time_zone']['name']."</td>;";
    }

    echo "</tr>;";
}

echo "</table>;";

echo "<h2>Using Individual IP Lookups (for comparison)</h2>";

echo "<table>";
echo "<tr>";
echo "<th>IP</th>";
echo "<th>Continent</th>";
echo "<th>Country</th>";
echo "<th>Organization</th>";
echo "<th>ISP</th>";
echo "<th>Languages</th>";
echo "<th>Is EU Member?</th>";
echo "<th>Currency</th>";
echo "<th>Timezone</th>";
echo "</tr>";

foreach ($ips as $ip) {
    $location = get_geolocation($apiKey, $ip);
    $decodedLocation = json_decode($location, true);

    echo "<tr>";

    if (isset($decodedLocation['message']) && $decodedLocation['message'] != '') {
        echo "<td>;".$ip."</td>;";
        echo "<td>;".$decodedLocation['message']."</td>;";
        echo "<td colspan='7'></td>;";
    } else {
        echo "<td>;".$decodedLocation['ip']."</td>;";
        echo "<td>;".$decodedLocation['continent_name']." (".$decodedLocation['continent_code'].")</td>;";
        echo "<td>;".$decodedLocation['country_name']." (".$decodedLocation['country_code2'].")</td>;";
        echo "<td>;".$decodedLocation['organization']."</td>;";
        echo "<td>;".$decodedLocation['isp']."</td>;";
        echo "<td>;".$decodedLocation['languages']."</td>;";

        if ($decodedLocation['is_eu'] == true) {
            echo "<td>;Yes</td>;";
        } else {
            echo "<td>;No</td>;";
        }

        echo "<td>;".$decodedLocation['currency']['name']."</td>;";
        echo "<td>;".$decodedLocation['time_zone']['name']."</td>;";
    }

    echo "</tr>;";
}

echo "</table>;";

function get_geolocation($apiKey, $ip, $lang = "en", $fields = "*", $excludes = "") {
    $url = "https://api.ipgeolocation.io/ipgeo?apiKey=".$apiKey."&ip=".$ip."&lang=".$lang."&fields=".$fields."&excludes=".$excludes;
    $cURL = curl_init();

    curl_setopt($cURL, CURLOPT_URL, $url);
    curl_setopt($cURL, CURLOPT_HTTPGET, true);
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
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
    $url = "https://api.ipgeolocation.io/batch";
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
        'Content-Length: ' . strlen($jsonPayload),
        'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
    ));

    $response = curl_exec($cURL);

    if (curl_errno($cURL)) {
        echo 'Curl error: ' . curl_error($cURL);
    }

    curl_close($cURL);

    return $response;
}
?>;
