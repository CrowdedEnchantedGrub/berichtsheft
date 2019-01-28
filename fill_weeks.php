<?php

$startDate = new DateTime('2017-02-27');
$yearBreak = new DateTime('2018-03-01');
$endDate = new DateTime('2019-03-01');

$dbConnection = new PDO('mysql:host=127.0.0.1;dbname=mycommits', 'root', 'root');

$week = new DateInterval('P7D');
$fivedays = new DateInterval('P4D');

$query = $dbConnection->prepare('SELECT * FROM commits_master
WHERE `date` >= :fromDate AND `date` <= :toDate
ORDER BY `date` ASC');

/** @var DateTime $monday */
for ($monday = $startDate; $monday < $endDate; $monday->add($week)) {
    $sunday = clone $monday;
    $sunday->add($week);
    $friday = clone $monday;
    $friday->add($fivedays);

    $query->execute([
        'fromDate' => $monday->format('Y-m-d H:i:s'),
        'toDate' => $sunday->format('Y-m-d H:i:s'),
    ]);

    $commits = $query->fetchAll();

    if ($monday < $yearBreak) {
        $year = 1;
    } else {
        $year = 2;
    }

    $content = '';

    foreach ($commits as $commit) {
        $message = explode(PHP_EOL, trim($commit['commitMessage']))[0];

        $content .= $message . PHP_EOL . PHP_EOL;
    }

    file_put_contents(__DIR__ . '/latex/weeks/' . $monday->format('Y-m-d') . '.tex',
        sprintf(
            '\begin{weeklyjournal}{%s}{%s}{%d}
%s
\end{weeklyjournal}
        ',
            $monday->format('d.m.Y'),
            $friday->format('d.m.Y'),
            $year,
            escapeForLatex(trim($content))
        ));
}

function escapeForLatex(string $input)
{
    $escaped = str_replace(
        [
            '&',
            '%',
            '$',
            '#',
            '_',
            '{',
            '}',
            '~',
            '^',
            '\\',
        ],
        [
            '\&',
            '\%',
            '\$',
            '\#',
            '\_',
            '\{',
            '\}',
            '\textasciitilde{}',
            '\textasciicircum{}',
            '\textbackslash{}',
        ],
        $input
    );

    if (strpos($input, '\\') !== false) {
        echo $input . PHP_EOL;
        echo '***********************' . PHP_EOL;
        echo $escaped . PHP_EOL;
    }

    return $escaped;
}