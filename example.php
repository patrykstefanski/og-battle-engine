<?php

error_reporting(E_ALL);

require_once 'BattleEngine.php';
require_once 'OG.php';

use BattleEngine\{BattleEngine, Combatant, CombatantOutcome};
use OG\OG;

$attackers = [
    new Combatant(
        10,
        10,
        10,
        [
            OG::BATTLESHIP => 250000,
        ],
    ),
    new Combatant(
        10,
        10,
        10,
        [
            OG::LIGHT_FIGHTER => 250000,
            OG::HEAVY_FIGHTER => 250000,
            OG::CRUISER => 250000,
        ],
    ),
];

$defenders = [
    new Combatant(
        10,
        10,
        10,
        [
            OG::DEATH_STAR => 2500,
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
        printf("\t%d %s\n", $count, OG::$names[$kind]);
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

function printCombatantOutcome(CombatantOutcome $outcome, int $roundNo)
{
    $roundStats = $outcome->getRoundStats($roundNo);
    $some = false;
    foreach ($roundStats as $kind => $stats) {
        $numRemaining = $stats->getNumRemainingUnits();
        if ($numRemaining > 0) {
            $some = true;
            printf("\t%d %s\n", $numRemaining, OG::$names[$kind]);
        }
    }
    if (!$some) {
        printf("\tDestroyed!\n");
    }
}

function printCombatantOutcomes(string $who, array $outcomes, int $roundNo)
{
    foreach ($outcomes as $i => $outcome) {
        printf("%s #%d\n", $who, $i);
        printCombatantOutcome($outcome, $roundNo);
        printf("\n");
    }
}

printCombatants('Attacker', $attackers);
printCombatants('Defender', $defenders);

$battleEngine = new BattleEngine('./build/BattleEngine', OG::$unitsAttributes);
$outcome = $battleEngine->battle($attackers, $defenders);

$numRounds = $outcome->getNumRounds();
$attackersOutcomes = $outcome->getAttackersOutcomes();
$defendersOutcomes = $outcome->getDefendersOutcomes();

for ($roundNo = 0; $roundNo < $numRounds; $roundNo++) {
    printf("After %d. round:\n\n", $roundNo + 1);
    printCombatantOutcomes('Attacker', $attackersOutcomes, $roundNo);
    printCombatantOutcomes('Defender', $defendersOutcomes, $roundNo);
}
