# Copyright (C) 2020 Patryk Stefanski
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <https://www.gnu.org/licenses/>.

import random
import subprocess
from typing import Dict, List, NewType

UnitKind = NewType('UnitKind', int)


class UnitAttributes:
    weapons: float
    shield: float
    armor: float
    rapid_fire: Dict[UnitKind, int]

    def __init__(self, weapons: float, shield: float, armor: float, rapid_fire: Dict[UnitKind, int]):
        if weapons <= 0.0:
            raise ValueError('weapons must be greater than 0')
        if shield <= 0.0:
            raise ValueError('shield must be greater than 0')
        if armor <= 0.0:
            raise ValueError('armor must be greater than 0')
        if any(not (0 <= rf <= 2 ** 32 - 1) for rf in rapid_fire.values()):
            raise ValueError(
                'rapid_fire elements must be between 0 and 2**32-1')
        self.weapons = weapons
        self.shield = shield
        self.armor = armor
        self.rapid_fire = rapid_fire


class Combatant:
    weapons_technology: int
    shielding_technology: int
    armor_technology: int
    unit_groups: Dict[UnitKind, int]

    def __init__(self, weapons_technology: int, shielding_technology: int, armor_technology: int,
                 unit_groups: Dict[UnitKind, int]):
        if not (0 <= weapons_technology <= 255):
            raise ValueError('weapons_technology must be between 0 and 255')
        if not (0 <= shielding_technology <= 255):
            raise ValueError('shielding_technology must be between 0 and 255')
        if not (0 <= armor_technology <= 255):
            raise ValueError('armor_technology must be between 0 and 255')
        if any(not (0 <= count <= 2 ** 64 - 1) for count in unit_groups.values()):
            raise ValueError('unit_groups elements must be between 0 and 2**64-1')
        self.weapons_technology = weapons_technology
        self.shielding_technology = shielding_technology
        self.armor_technology = armor_technology
        self.unit_groups = unit_groups


class UnitGroupStats:
    times_fired: int
    times_was_shot: int
    shield_damage_dealt: int
    hull_damage_dealt: int
    shield_damage_taken: int
    hull_damage_taken: int
    num_remaining_units: int

    def __init__(self, times_fired: int, times_was_shot: int, shield_damage_dealt: int, hull_damage_dealt: int,
                 shield_damage_taken: int, hull_damage_taken: int, num_remaining_units: int):
        self.times_fired = times_fired
        self.times_was_shot = times_was_shot
        self.shield_damage_dealt = shield_damage_dealt
        self.hull_damage_dealt = hull_damage_dealt
        self.shield_damage_taken = shield_damage_taken
        self.hull_damage_taken = hull_damage_taken
        self.num_remaining_units = num_remaining_units


class CombatantOutcome:
    rounds_stats: List[Dict[UnitKind, UnitGroupStats]]

    def __init__(self, rounds_stats):
        self.rounds_stats = rounds_stats

    def round_stats(self, round_no: int) -> Dict[UnitKind, UnitGroupStats]:
        return self.rounds_stats[round_no]


class BattleOutcome:
    num_rounds: int
    attackers_outcomes: List[CombatantOutcome]
    defenders_outcomes: List[CombatantOutcome]

    def __init__(self, num_rounds: int, attackers_outcomes: List[CombatantOutcome],
                 defenders_outcomes: List[CombatantOutcome]):
        self.num_rounds = num_rounds
        self.attackers_outcomes = attackers_outcomes
        self.defenders_outcomes = defenders_outcomes


class Error(Exception):
    pass


class BattleEngine:
    engine_path: str
    units_attributes: Dict[UnitKind, UnitAttributes]

    def __init__(self, engine_path: str, units_attributes: Dict[UnitKind, UnitAttributes]):
        self.engine_path = engine_path
        self.units_attributes = units_attributes
        self._assert_valid_units_attributes()

    def _assert_valid_units_attributes(self):
        num_kinds = len(self.units_attributes)

        if num_kinds == 0:
            raise ValueError('units attributes cannot be empty')

        for i in range(num_kinds):
            try:
                attrs = self.units_attributes[i]
            except KeyError:
                raise ValueError('no UnitKind({}) found in units_attributes'.format(i))

            for kind, count in attrs.rapid_fire.items():
                if kind >= num_kinds:
                    raise ValueError(
                        'UnitKind({}) in rapid_fire of UnitKind({}) does not exist in units_attributes'.format(kind, i))

    def _make_stdin_for_units_attributes(self):
        stdin = []
        attributes_len = len(self.units_attributes)
        stdin.append('{}'.format(attributes_len))
        stdin.append('')
        for attrs in self.units_attributes.values():
            stdin.append('{} {} {} {}'.format(
                attrs.weapons, attrs.shield, attrs.armor, len(attrs.rapid_fire)))
            for kind, count in attrs.rapid_fire.items():
                stdin.append('{} {}'.format(kind, count))
            stdin.append('\n')
        return '\n'.join(stdin)

    def _assert_valid_combatants(self, name: str, combatants: List[Combatant]):
        if len(combatants) >= 256:
            raise ValueError('too many {}'.format(name))

        for i, combatant in enumerate(combatants):
            for kind, count in combatant.unit_groups.items():
                if kind not in self.units_attributes:
                    raise ValueError('no UnitKind({}) found in units_attributes for {} at {}'.format(kind, name, i))

    @staticmethod
    def _make_stdin_for_combatant(combatant: Combatant) -> str:
        stdin = ['{} {} {} {}\n'.format(combatant.weapons_technology,
                                        combatant.shielding_technology, combatant.armor_technology,
                                        len(combatant.unit_groups))]
        for kind, count in combatant.unit_groups.items():
            stdin.append('{} {}\n'.format(kind, count))
        return ''.join(stdin)

    @staticmethod
    def _make_stdin_for_combatants(attackers: List[Combatant], defenders: List[Combatant]) -> str:
        stdin = ['{} {}\n'.format(len(attackers), len(defenders))]
        stdin.extend(BattleEngine._make_stdin_for_combatant(combatant)
                     for combatant in attackers)
        stdin.extend(BattleEngine._make_stdin_for_combatant(combatant)
                     for combatant in defenders)
        return '\n'.join(stdin)

    def parse_combatant_outcome(self, num_rounds: int, data: List[int]) -> CombatantOutcome:
        num_kinds = len(self.units_attributes)
        index = 0
        rounds_stats = []
        for round_no in range(num_rounds):
            round_stats = {}
            for kind in range(num_kinds):
                group_stats = UnitGroupStats(*data[index:index + 7])
                round_stats[UnitKind(kind)] = group_stats
                index += 7
            rounds_stats.append(round_stats)
        return CombatantOutcome(rounds_stats)

    def battle(self, attackers: List[Combatant], defenders: List[Combatant], seed: int = 0,
               timeout=None) -> BattleOutcome:
        self._assert_valid_combatants('attackers', attackers)
        self._assert_valid_combatants('defenders', defenders)

        if seed < 0:
            raise ValueError('seed must be at least 0')

        if seed == 0:
            seed = random.randint(1, 1000000000)

        attrs_stdin = self._make_stdin_for_units_attributes()
        combatants_stdin = self._make_stdin_for_combatants(attackers, defenders)
        stdin = attrs_stdin + '\n' + combatants_stdin

        args = [self.engine_path, str(seed)]
        p = subprocess.Popen(args, stdin=subprocess.PIPE, stdout=subprocess.PIPE, stderr=subprocess.PIPE)

        try:
            outs = p.communicate(input=stdin.encode(), timeout=timeout)
        except subprocess.TimeoutExpired:
            p.kill()
            raise

        if p.returncode != 0:
            stderr = outs[1].decode('ascii')
            raise Error(stderr)

        num_kinds = len(self.units_attributes)
        num_attackers = len(attackers)
        num_defenders = len(defenders)

        out = outs[0].decode('ascii')
        result = list(map(int, out.split()))
        num_rounds = result[0]
        data = result[1:]

        outcome_size = num_rounds * num_kinds * 7
        outcomes = []
        for i in range(num_attackers + num_defenders):
            d = data[i * outcome_size:(i + 1) * outcome_size]
            outcome = self.parse_combatant_outcome(num_rounds, d)
            outcomes.append(outcome)

        attackers_outcomes, defenders_outcomes = outcomes[:num_attackers], outcomes[num_attackers:]

        return BattleOutcome(num_rounds, attackers_outcomes, defenders_outcomes)
