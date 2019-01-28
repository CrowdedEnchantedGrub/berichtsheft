<?php

use GuzzleHttp\Client;

require_once __DIR__ . '/vendor/autoload.php';

$client = new Client();

// URL für Bitbucket API Repositories Endpunkt.
// Beispiel: https://api.bitbucket.org/2.0/repositories/organization
const BASE_URL = '';

// Oauth-Token für Bitbucket API
$token = 'Bearer abcd...';

// Commit-Author Name, für den Commits geholt werden sollen
$nickname = '';

$repositorySlugs = [];
$nextUrl = BASE_URL . '?pagelen=100';

$i = 0;
while ($nextUrl !== null) {
    echo 'Fetching repositories page ' . ++$i . PHP_EOL;

    $response = $client->get(
        $nextUrl,
        [
            'headers' => [
                'Authorization' => $token,
            ],
        ]
    );

    $responseText = $response->getBody()->getContents();
    $responseBody = json_decode($responseText, true);

    if (!empty($responseBody['next'])) {
        $nextUrl = $responseBody['next'];
    } else {
        $nextUrl = null;
    }

    foreach ($responseBody['values'] as $repository) {
        $repositorySlugs[$repository['slug']] = $repository['name'];
    }
}

$dbConnection = new PDO('mysql:host=127.0.0.1;dbname=mycommits', 'root', 'root');

$r = 0;
foreach ($repositorySlugs as $slug => $repositoryName) {
    $nextUrl = BASE_URL . '/' . $slug . '/commits/master?pagelen=100';

    echo 'Repository ' . ++$r . ' of ' . count($repositorySlugs) . PHP_EOL;

    $i = 0;
    while ($nextUrl !== null) {
        echo 'Fetching commits of ' . $repositoryName . ', page ' . ++$i . PHP_EOL;

        try {
            $response = $client->get(
                $nextUrl,
                [
                    'headers' => [
                        'Authorization' => $token,
                    ],
                ]
            );
        } catch (\Exception $e) {
            continue 2;
        }

        $responseText = $response->getBody()->getContents();
        $responseBody = json_decode($responseText, true);

        if (!empty($responseBody['next'])) {
            $nextUrl = $responseBody['next'];
        } else {
            $nextUrl = null;
        }

        foreach ($responseBody['values'] as $commit) {
            if (!array_key_exists('user', $commit['author'])
                || $commit['author']['user']['nickname']
                !== $nickname) {
                continue;
            }

            $statement = $dbConnection->prepare(
                'INSERT INTO commits_master (`repositoryName`, `repositorySlug`, `date`, `commitMessage`, `commitHash`)
                    VALUES (:repoName, :repoSlug, :date, :commitMessage, :commitHash)'
            );

            $statement->execute([
                'repoName' => $repositoryName,
                'repoSlug' => $slug,
                'date' => (new DateTime($commit['date']))->format('Y-m-d H:i:s'),
                'commitMessage' => $commit['message'],
                'commitHash' => $commit['hash'],
            ]);
        }
    }
}
