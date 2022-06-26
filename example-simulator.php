<?php

error_reporting(E_ALL);

require_once 'BattleEngine.php';
require_once 'OG.php';

use BattleEngine\BattleEngine;
use BattleEngine\Combatant;
use OG\OG;

$numSimulations = 100;

$attackers = [
    new Combatant(
        10,
        10,
        10,
        [
            OG::BATTLESHIP => 25000,
        ],
    ),
    new Combatant(
        10,
        10,
        10,
        [
            OG::LIGHT_FIGHTER => 25000,
            OG::HEAVY_FIGHTER => 25000,
            OG::CRUISER => 25000,
        ],
    ),
];

$defenders = [
    new Combatant(
        10,
        10,
        10,
        [
            OG::DEATH_STAR => 250,
        ],
    ),
];

function printCombatant(Combatant $combatant)
{
    $weapons = $combatant->getWeaponsTechnology() * 10;
    $shield = $combatant->getShieldingTechnology() * 10;
    $armor = $combatant->getArmorTechnology() * 10;
    printf(
        "\tWeapons %d%% Shield %d%% Armor %d%%\n",
        $weapons,
        $shield,
        $armor
    );
    $unitGroups = $combatant->getUnitGroups();
    foreach ($unitGroups as $kind => $count) {
        printf("\t%s %d\n", OG::$names[$kind], $count);
    }
}

function printCombatants(string $who, array $combatants)
{
    foreach ($combatants as $i => $combatant) {
        printf("%s #%d\n", $who, $i);
        printCombatant($combatant);
        printf("\n");
    }
}

function mean(array $values): float
{
    return array_sum($values) / count($values);
}

function stdev(array $values): float
{
    $mean = array_sum($values) / count($values);
    $var = 0.0;
    foreach ($values as $val) {
        $var += pow($val - $mean, 2);
    }
    return sqrt($var / (count($values) - 1));
}

function printCombatantResult(Combatant $combatant, array $stats)
{
    $unitGroups = $combatant->getUnitGroups();
    foreach ($unitGroups as $kind => $count) {
        printf("\t%s:\n", OG::$names[$kind]);
        $numRemainingUnits = array_map(fn($s) => $s[$kind]->getNumRemainingUnits(), $stats);
        printf("\t\tMean:  %f\n", mean($numRemainingUnits));
        printf("\t\tStdev: %f\n", stdev($numRemainingUnits));
        printf("\t\tMin:   %d\n", min($numRemainingUnits));
        printf("\t\tMax:   %d\n", max($numRemainingUnits));
    }
}

function printCombatantResults(string $who, array $combatants, array $simulations, callable $getOutcomes)
{
    foreach ($combatants as $i => $combatant) {
        printf("%s #%d\n", $who, $i);
        $stats = array_map(fn($s) => $getOutcomes($s)[$i]->getRoundStats($s->getNumRounds() - 1), $simulations);
        printCombatantResult($combatant, $stats);
        printf("\n");
    }
}

printCombatants('Attacker', $attackers);
printCombatants('Defender', $defenders);

$battleEngine = new BattleEngine('./build/BattleEngine', OG::$unitsAttributes);
$simulations = $battleEngine->simulate($attackers, $defenders, 0, $numSimulations);

$numRounds = mean(array_map(fn($s): int => $s->getNumRounds(), $simulations));
printf("Num rounds: %f\n\n", $numRounds);
printCombatantResults('Attacker', $attackers, $simulations, fn($s) => $s->getAttackersOutcomes());
printCombatantResults('Defender', $defenders, $simulations, fn($s) => $s->getDefendersOutcomes());
