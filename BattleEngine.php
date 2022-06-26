<?php

/*
 * Copyright (C) 2020 Patryk Stefanski
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace BattleEngine;

use Exception;
use InvalidArgumentException;

class UnitAttributes
{
    private float $weapons;
    private float $shield;
    private float $armor;
    private array $rapidFire;

    public function __construct(
        float $weapons,
        float $shield,
        float $armor,
        array $rapidFire
    )
    {
        if ($weapons <= 0.0) {
            throw new InvalidArgumentException('weapons must be greater than 0');
        }
        if ($shield <= 0.0) {
            throw new InvalidArgumentException('shield must be greater than 0');
        }
        if ($armor <= 0.0) {
            throw new InvalidArgumentException('armor must be greater than 0');
        }
        foreach ($rapidFire as $count) {
            if ($count < 0 || $count > (1 << 32) - 1) {
                throw new InvalidArgumentException('rapidFire elements must be between 0 and 2**32-1');
            }
        }
        $this->weapons = $weapons;
        $this->shield = $shield;
        $this->armor = $armor;
        $this->rapidFire = $rapidFire;
    }

    public function getWeapons(): float
    {
        return $this->weapons;
    }

    public function getShield(): float
    {
        return $this->shield;
    }

    public function getArmor(): float
    {
        return $this->armor;
    }

    public function getRapidFire(): array
    {
        return $this->rapidFire;
    }
}

class Combatant
{
    private int $weaponsTechnology;
    private int $shieldingTechnology;
    private int $armorTechnology;
    private array $unitGroups;

    public function __construct(
        int   $weaponsTechnology,
        int   $shieldingTechnology,
        int   $armorTechnology,
        array $unitGroups
    )
    {
        if ($weaponsTechnology < 0 || $weaponsTechnology > 255) {
            throw new InvalidArgumentException('weaponsTechnology must be between 0 and 255');
        }
        if ($shieldingTechnology < 0 || $shieldingTechnology > 255) {
            throw new InvalidArgumentException('shieldingTechnology must be between 0 and 255');
        }
        if ($armorTechnology < 0 || $armorTechnology > 255) {
            throw new InvalidArgumentException('armorTechnology must be between 0 and 255');
        }
        foreach ($unitGroups as $count) {
            if ($count < 0) {
                throw new InvalidArgumentException('unitGroups elements must be at least 0');
            }
        }
        $this->weaponsTechnology = $weaponsTechnology;
        $this->shieldingTechnology = $shieldingTechnology;
        $this->armorTechnology = $armorTechnology;
        $this->unitGroups = $unitGroups;
    }

    public function getWeaponsTechnology(): int
    {
        return $this->weaponsTechnology;
    }

    public function getShieldingTechnology(): int
    {
        return $this->shieldingTechnology;
    }

    public function getArmorTechnology(): int
    {
        return $this->armorTechnology;
    }

    public function getUnitGroups(): array
    {
        return $this->unitGroups;
    }
}

class UnitGroupStats
{
    private int $timesFired;
    private int $timesWasShot;
    private int $shieldDamageDealt;
    private int $hullDamageDealt;
    private int $shieldDamageTaken;
    private int $hullDamageTaken;
    private int $numRemainingUnits;

    public function __construct(
        int $timesFired,
        int $timesWasShot,
        int $shieldDamageDealt,
        int $hullDamageDealt,
        int $shieldDamageTaken,
        int $hullDamageTaken,
        int $numRemainingUnits
    )
    {
        $this->timesFired = $timesFired;
        $this->timesWasShot = $timesWasShot;
        $this->shieldDamageDealt = $shieldDamageDealt;
        $this->hullDamageDealt = $hullDamageDealt;
        $this->shieldDamageTaken = $shieldDamageTaken;
        $this->hullDamageTaken = $hullDamageTaken;
        $this->numRemainingUnits = $numRemainingUnits;
    }

    public function getTimesFired(): int
    {
        return $this->timesFired;
    }

    public function getTimesWasShot(): int
    {
        return $this->timesWasShot;
    }

    public function getShieldDamageDealt(): int
    {
        return $this->shieldDamageDealt;
    }

    public function getHullDamageDealt(): int
    {
        return $this->hullDamageDealt;
    }

    public function getShieldDamageTaken(): int
    {
        return $this->shieldDamageTaken;
    }

    public function getHullDamageTaken(): int
    {
        return $this->hullDamageTaken;
    }

    public function getNumRemainingUnits(): int
    {
        return $this->numRemainingUnits;
    }
}

class CombatantOutcome
{
    private array $roundsStats;

    public function __construct(array $roundsStats)
    {
        $this->roundsStats = $roundsStats;
    }

    public function getRoundStats(int $roundNo): array
    {
        return $this->roundsStats[$roundNo];
    }

    public function getRoundsStats(): array
    {
        return $this->roundsStats;
    }
}

class BattleOutcome
{
    private int $numRounds;
    private array $attackersOutcomes;
    private array $defendersOutcomes;

    public function __construct(
        int   $numRounds,
        array $attackersOutcomes,
        array $defendersOutcomes
    )
    {
        $this->numRounds = $numRounds;
        $this->attackersOutcomes = $attackersOutcomes;
        $this->defendersOutcomes = $defendersOutcomes;
    }

    public function getNumRounds(): int
    {
        return $this->numRounds;
    }

    public function getAttackersOutcomes(): array
    {
        return $this->attackersOutcomes;
    }

    public function getDefendersOutcomes(): array
    {
        return $this->defendersOutcomes;
    }
}

class Error extends Exception
{
}

class BattleEngine
{
    private string $enginePath;
    private array $unitsAttributes;

    public function __construct(string $enginePath, array $unitsAttributes)
    {
        $this->enginePath = $enginePath;
        $this->unitsAttributes = $unitsAttributes;
        $this->assertValidUnitsAttributes();
    }

    private function assertValidUnitsAttributes()
    {
        $numKinds = count($this->unitsAttributes);

        if ($numKinds === 0) {
            throw new InvalidArgumentException('unitsAttributes cannot be empty');
        }

        for ($i = 0; $i < $numKinds; $i++) {
            if (!array_key_exists($i, $this->unitsAttributes)) {
                $message = sprintf('no unit with key %d found in unitsAttributes', $i);
                throw new InvalidArgumentException($message);
            }
            $rapidFire = $this->unitsAttributes[$i]->getRapidFire();
            foreach ($rapidFire as $kind => $count) {
                if ($kind >= $numKinds) {
                    $message = sprintf(
                        'unit with key %d in rapidFire of unit with key %d does not exist in unitsAttributes',
                        $kind,
                        $i
                    );
                    throw new InvalidArgumentException($message);
                }
            }
        }
    }

    private function makeStdinForUnitsAttributes(): string
    {
        $stdin = [];
        $stdin[] = count($this->unitsAttributes);
        $stdin[] = '';
        foreach ($this->unitsAttributes as $attrs) {
            $rapidFire = $attrs->getRapidFire();
            $stdin[] = sprintf(
                '%f %f %f %d',
                $attrs->getWeapons(),
                $attrs->getShield(),
                $attrs->getArmor(),
                count($rapidFire),
            );
            foreach ($rapidFire as $kind => $count) {
                $stdin[] = sprintf('%d %d', $kind, $count);
            }
            $stdin[] = '';
        }
        return implode("\n", $stdin);
    }

    private function assertValidCombatants(string $name, array $combatants)
    {
        if (count($combatants) >= 256) {
            throw new InvalidArgumentException('too many ' . $name);
        }

        foreach ($combatants as $i => $combatant) {
            foreach ($combatant->getUnitGroups() as $kind => $count) {
                if (!array_key_exists($kind, $this->unitsAttributes)) {
                    $message = sprintf(
                        'no unit with key %d found in unitsAttributes for %s at index %d',
                        $kind,
                        $name,
                        $i
                    );
                    throw new InvalidArgumentException($message);
                }
            }
        }
    }

    private function makeStdinForCombatant(Combatant $combatant): string
    {
        $unitGroups = $combatant->getUnitGroups();
        $stdin = [];
        $stdin[] = sprintf(
            '%d %d %d %d',
            $combatant->getWeaponsTechnology(),
            $combatant->getShieldingTechnology(),
            $combatant->getArmorTechnology(),
            count($unitGroups)
        );
        foreach ($unitGroups as $kind => $count) {
            $stdin[] = sprintf('%d %d', $kind, $count);
        }
        return implode("\n", $stdin);
    }

    private function makeStdinForCombatants(
        array $attackers,
        array $defenders
    ): string
    {
        $stdin = [];
        $stdin[] = sprintf('%d %d', count($attackers), count($defenders));
        $stdin[] = '';
        foreach ($attackers as $attacker) {
            $stdin[] = $this->makeStdinForCombatant($attacker);
            $stdin[] = '';
        }
        foreach ($defenders as $defender) {
            $stdin[] = $this->makeStdinForCombatant($defender);
            $stdin[] = '';
        }
        return implode("\n", $stdin);
    }

    private function parseCombatantOutcome(
        int   $numRounds,
        array $data
    ): CombatantOutcome
    {
        $numKinds = count($this->unitsAttributes);
        $index = 0;
        $roundsStats = [];
        for ($roundNo = 0; $roundNo < $numRounds; $roundNo++) {
            $roundStats = [];
            for ($kind = 0; $kind < $numKinds; $kind++) {
                list(
                    $timesFired,
                    $timesWasShot,
                    $shieldDamageDealt,
                    $hullDamageDealt,
                    $shieldDamageTaken,
                    $hullDamageTaken,
                    $numRemainingUnits
                    ) = array_slice($data, $index, 7);
                $roundStats[] = new UnitGroupStats(
                    $timesFired,
                    $timesWasShot,
                    $shieldDamageDealt,
                    $hullDamageDealt,
                    $shieldDamageTaken,
                    $hullDamageTaken,
                    $numRemainingUnits
                );
                $index += 7;
            }
            $roundsStats[] = $roundStats;
        }
        return new CombatantOutcome($roundsStats);
    }

    public function simulate(
        array $attackers,
        array $defenders,
        int   $seed = 0,
        int   $numSimulations = 1
    ): array
    {
        $this->assertValidCombatants('attackers', $attackers);
        $this->assertValidCombatants('defenders', $defenders);

        if ($seed < 0) {
            throw new InvalidArgumentException('seed must be at least 0');
        }

        if ($seed === 0) {
            $seed = rand(1, 1000000000);
        }

        $cmd = $this->enginePath . ' ' . $seed . ' ' . $numSimulations;
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $cwd = null;
        $env = [];
        $p = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);
        if (!is_resource($p)) {
            throw new Error('opening process failed');
        }

        $attrsStdin = $this->makeStdinForUnitsAttributes();
        $combatantsStdin = $this->makeStdinForCombatants(
            $attackers,
            $defenders
        );
        $stdin = $attrsStdin . "\n" . $combatantsStdin;

        fwrite($pipes[0], $stdin);
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($p);

        if ($returnCode !== 0) {
            throw new Error($stderr);
        }

        $numKinds = count($this->unitsAttributes);
        $numAttackers = count($attackers);
        $numDefenders = count($defenders);

        $result = array_map('intval', preg_split('/\s+/', $stdout));

        $simulations = [];

        $idx = 0;
        for ($i = 0; $i < $numSimulations; $i++) {
            $numRounds = $result[$idx];
            $idx++;

            $outcomeSize = $numRounds * $numKinds * 7;
            $outcomes = [];
            for ($j = 0; $j < $numAttackers + $numDefenders; $j++) {
                $d = array_slice($result, $idx, $outcomeSize);
                $idx += $outcomeSize;
                $outcomes[] = $this->parseCombatantOutcome($numRounds, $d);
            }

            $attackersOutcomes = array_slice($outcomes, 0, $numAttackers);
            $defendersOutcomes = array_slice($outcomes, $numAttackers);

            $simulations[] = new BattleOutcome(
                $numRounds,
                $attackersOutcomes,
                $defendersOutcomes
            );
        }

        return $simulations;
    }
}
