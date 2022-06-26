from typing import List

import OG
from BattleEngine import BattleEngine, Combatant, CombatantOutcome

attackers = [
    Combatant(
        weapons_technology=10,
        shielding_technology=10,
        armor_technology=10,
        unit_groups={
            OG.Battleship: 250000,
        },
    ),
    Combatant(
        weapons_technology=10,
        shielding_technology=10,
        armor_technology=10,
        unit_groups={
            OG.LightFighter: 250000,
            OG.HeavyFighter: 250000,
            OG.Cruiser: 250000,
        },
    ),
]

defenders = [
    Combatant(
        weapons_technology=10,
        shielding_technology=10,
        armor_technology=10,
        unit_groups={
            OG.DeathStar: 2500,
        },
    ),
]


def print_combatant(combatant: Combatant):
    weapons = combatant.weapons_technology * 10
    shield = combatant.shielding_technology * 10
    armor = combatant.armor_technology * 10
    print('\tWeapons {}% Shield {}% Armor {}%'.format(weapons, shield, armor))
    for kind, count in combatant.unit_groups.items():
        print('\t{} {}'.format(count, OG.names[kind]))


def print_combatants(who: str, combatants: List[Combatant]):
    for i, combatant in enumerate(combatants):
        print('{} #{}'.format(who, i))
        print_combatant(combatant)
        print()


def print_combatant_outcome(outcome: CombatantOutcome, round_no: int):
    round_stats = outcome.round_stats(round_no)
    some = False
    for kind, stats in round_stats.items():
        num_remaining = stats.num_remaining_units
        if num_remaining > 0:
            some = True
            print('\t{} {}'.format(num_remaining, OG.names[kind]))
    if not some:
        print('\tDestroyed!')


def print_combatant_outcomes(who, outcomes: List[CombatantOutcome], round_no: int):
    for i, outcome in enumerate(outcomes):
        print('{} #{}'.format(who, i))
        print_combatant_outcome(outcome, round_no)
        print('')


print_combatants('Attacker', attackers)
print_combatants('Defender', defenders)

engine = BattleEngine('./build/BattleEngine', OG.units_attributes)
outcome = engine.simulate(attackers, defenders)[0]

num_rounds = outcome.num_rounds
attackers_outcomes = outcome.attackers_outcomes
defenders_outcomes = outcome.defenders_outcomes

for round_no in range(num_rounds):
    print('After {}. round:\n'.format(round_no + 1))
    print_combatant_outcomes('Attacker', attackers_outcomes, round_no)
    print_combatant_outcomes('Defender', defenders_outcomes, round_no)
