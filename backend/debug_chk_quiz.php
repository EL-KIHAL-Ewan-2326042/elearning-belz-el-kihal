<?php

use App\Kernel;
use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/vendor/autoload.php';

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine')->getManager();
$serializer = $container->get('serializer');

$quiz = $em->getRepository(Quiz::class)->find(3);

if (!$quiz) {
    echo "Quiz 3 not found\n";
    exit;
}

echo "Quiz found: " . $quiz->getTitle() . "\n";

// Serialize using the group 'quiz:read'
$json = $serializer->serialize($quiz, 'json', ['groups' => ['quiz:read']]);
$data = json_decode($json, true);

if (!empty($data['questions'])) {
    $q = $data['questions'][0];
    echo "First Question: " . $q['content'] . "\n";
    echo "Answers Dump:\n";
    foreach ($q['answers'] as $a) {
        // Print keys and values
        echo " - [id: ".$a['id']."] ";
        foreach ($a as $k => $v) {
            echo "$k=" . (is_bool($v) ? ($v ? 'true' : 'false') : $v) . " | ";
        }
        echo "\n";
    }
} else {
    echo "No questions found in serialization.\n";
}
