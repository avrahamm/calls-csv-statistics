<?php

use App\Entity\IpGeolocationCache;
use App\Repository\IpGeolocationCacheRepository;
use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

$app = function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    $kernel->boot();
    return $kernel;
};

// Set environment variables
$_SERVER['APP_ENV'] = 'dev';
$_SERVER['APP_DEBUG'] = '1';
$_SERVER['DATABASE_URL'] = 'mysql://symfony:symfony@127.0.0.1:13306/symfony?serverVersion=8.0&charset=utf8mb4';
$_SERVER['IP_GEOLOCATION_API_KEY'] = 'b9c9e0c9e04642f5a66b2278c4cb1e25';

$kernel = $app($_SERVER);
$container = $kernel->getContainer();

// Get the entity manager
$entityManager = $kernel->getContainer()->get('doctrine')->getManager();

// Create the repository manually
$repository = new IpGeolocationCacheRepository($kernel->getContainer()->get('doctrine'));

// Test data
$testIp = '116.52.75.78';
$testContinent = 'AS';

// Method 1: Using the repository's insertBulk method
echo "<h2>Method 1: Using insertBulk</h2>";
try {
    $result = $repository->insertBulk([$testIp => $testContinent]);
    echo "Inserted $result record(s) using insertBulk<br>";
} catch (\Exception $e) {
    echo "Error using insertBulk: " . $e->getMessage() . "<br>";
}

// Method 2: Using Doctrine entity
echo "<h2>Method 2: Using Doctrine entity</h2>";
try {
    $entity = new IpGeolocationCache();
    $entity->setIpAddress($testIp);
    $entity->setContinentCode($testContinent);
    $entity->setLastChecked(new \DateTime());

    $entityManager->persist($entity);
    $entityManager->flush();

    echo "Inserted record using Doctrine entity<br>";
} catch (\Exception $e) {
    echo "Error using Doctrine entity: " . $e->getMessage() . "<br>";
}

// Method 3: Using direct SQL
echo "<h2>Method 3: Using direct SQL</h2>";
try {
    $connection = $entityManager->getConnection();
    $sql = "INSERT INTO ip_geolocation_cache (ip_address, continent_code, last_checked) 
            VALUES (:ip, :continent, :last_checked)
            ON DUPLICATE KEY UPDATE continent_code = VALUES(continent_code), last_checked = VALUES(last_checked)";

    $stmt = $connection->prepare($sql);
    $stmt->bindValue('ip', $testIp);
    $stmt->bindValue('continent', $testContinent);
    $stmt->bindValue('last_checked', (new \DateTime())->format('Y-m-d H:i:s'));
    $result = $stmt->executeStatement();

    echo "Inserted $result record(s) using direct SQL<br>";
} catch (\Exception $e) {
    echo "Error using direct SQL: " . $e->getMessage() . "<br>";
}

// Check if the data was inserted
echo "<h2>Verification</h2>";
try {
    $result = $repository->findContinentCodesForIps([$testIp]);
    echo "Found in cache: ";
    var_dump($result);
} catch (\Exception $e) {
    echo "Error checking cache: " . $e->getMessage() . "<br>";
}

// No need to return a response or terminate the kernel for a CLI script
