import statistics
from typing import Dict, List

import OG
from BattleEngine import BattleEngine, BattleOutcome, Combatant, UnitGroupStats, UnitKind

num_simulations = 100

attackers = [
    Combatant(
        weapons_technology=10,
        shielding_technology=10,
        armor_technology=10,
        unit_groups={
            OG.Battleship: 25000,
        },
    ),
    Combatant(
        weapons_technology=10,
        shielding_technology=10,
        armor_technology=10,
        unit_groups={
            OG.LightFighter: 25000,
            OG.HeavyFighter: 25000,
            OG.Cruiser: 25000,
        },
    ),
]

defenders = [
    Combatant(
        weapons_technology=10,
        shielding_technology=10,
        armor_technology=10,
        unit_groups={
            OG.DeathStar: 250,
        },
    ),
]


def print_combatant(combatant: Combatant):
    weapons = combatant.weapons_technology * 10
    shield = combatant.shielding_technology * 10
    armor = combatant.armor_technology * 10
    print('\tWeapons {}% Shield {}% Armor {}%'.format(weapons, shield, armor))
    for kind, count in combatant.unit_groups.items():
        print('\t{}\t{}'.format(OG.names[kind], count))


def print_combatants(who: str, combatants: List[Combatant]):
    for i, combatant in enumerate(combatants):
        print('{} #{}'.format(who, i))
        print_combatant(combatant)
        print()


def print_combatant_result(combatant: Combatant, stats: List[Dict[UnitKind, UnitGroupStats]]):
    for kind in combatant.unit_groups.keys():
        print('\t{}:'.format(OG.names[kind]))
        num_remaining_units = [s[kind].num_remaining_units for s in stats]
        print('\t\tMean:  {}'.format(statistics.mean(num_remaining_units)))
        print('\t\tStdev: {}'.format(statistics.pstdev(num_remaining_units)))
        print('\t\tMin:   {}'.format(min(num_remaining_units)))
        print('\t\tMax:   {}'.format(max(num_remaining_units)))


def print_combatant_results(who: str, combatants: List[Combatant], simulations: List[BattleOutcome],
                            get_outcomes):
    for i, combatant in enumerate(combatants):
        print('{} #{}'.format(who, i))
        stats = [get_outcomes(s)[i].round_stats(s.num_rounds - 1)
                 for s in simulations]
        print_combatant_result(combatant, stats)
        print()


print_combatants('Attacker', attackers)
print_combatants('Defender', defenders)

engine = BattleEngine('./build/BattleEngine', OG.units_attributes)
simulations = engine.simulate(attackers, defenders, 0, num_simulations)

num_rounds = statistics.mean(s.num_rounds for s in simulations)
print('Num rounds: {}\n'.format(num_rounds))
print_combatant_results('Attacker', attackers, simulations,
                        lambda s: s.attackers_outcomes)
print_combatant_results('Defender', defenders, simulations,
                        lambda s: s.defenders_outcomes)
