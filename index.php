<?php
/**
 * Date: 11/8/18
 * Time: 3:11 PM
 * Author: lest4t
 */

require_once(__DIR__ . '/data.php');

/**
 * Факториал числа
 * @param $number int
 * @return int
 */
function factorial($number) {
	$factorial = 1;
	for ($i = 1; $i < $number; $i++) {
		$factorial *= $i;
	}

	return $factorial;
}

/**
 * Формула расчета забитых голов по формуле Пуассона
 *
 * @param $goals
 * @param $attack
 * @return float|int
 */
function poisson($goals, $attack) {
	return (exp(-$attack) * pow($attack, $goals)) / factorial($goals);
}

function match($teamOneId, $teamTwoId) {
	global $data;

	$teamOne = $data[$teamOneId];
	$teamTwo = $data[$teamTwoId];

	$allScored  = 0;
	$allSkipped = 0;
	$allGames   = 0;
	foreach ($data as $team) {
		$allScored  += $team['goals']['scored'];
		$allSkipped += $team['goals']['skipped'];
		$allGames   += $team['games'];
	}

	$averageScored  = $allScored / $allGames;
	$averageSkipped = $allSkipped / $allGames;
	$averageGoals   = ($averageScored + $averageSkipped) / 2;


	$teamOneAttack  = $teamOne['goals']['scored'] / $teamOne['games'] / $averageScored;
	$teamOneDefense = $teamOne['goals']['skipped'] / $teamOne['games'] / $averageSkipped;
	$teamTwoAttack  = $teamTwo['goals']['scored'] / $teamTwo['games'] / $averageScored;
	$teamTwoDefense = $teamTwo['goals']['skipped'] / $teamTwo['games'] / $averageSkipped;

	$scoredTeamOne = $teamOneAttack * $teamTwoDefense * $averageGoals;
	$scoredTeamTwo = $teamTwoAttack * $teamOneDefense * $averageGoals;

	$teamOneScoredTable = array();
	$teamTwoScoredTable = array();

	for ($i = 0; $i < 10; $i++) {
		$poisonTeamOne = round(poisson($i, $scoredTeamOne) * 100);
		$poisonTeamTwo = round(poisson($i, $scoredTeamTwo) * 100);
		if ($poisonTeamOne) {
			for ($j = 0; $j < $poisonTeamOne; $j++) {
				array_push($teamOneScoredTable, $i);
			}
		}
		if ($poisonTeamTwo) {
			for ($j = 0; $j < $poisonTeamTwo; $j++) {
				array_push($teamTwoScoredTable, $i);
			}
		}
	}

	return array(
		$teamOneScoredTable[rand(0, count($teamOneScoredTable) - 1)],
		$teamTwoScoredTable[rand(0, count($teamTwoScoredTable) - 1)],
	);
}

// Тестирование формулы из командной строки
foreach ($data as $id => $team) {
	echo "[" . $id . "]" . $team['name'] . "\r\n";
}

$teamOneId = readline("Enter team one ID: ");
$teamTwoId = readline("Enter team two ID: ");

$result = match($teamOneId, $teamTwoId);

echo "RESULT:\r\n";
echo $data[$teamOneId]['name'] . ": " . $result[0] . "\r\n";
echo $data[$teamTwoId]['name'] . ": " . $result[1] . "\r\n";
