<?php

declare(strict_types=1);

namespace OG;

require_once 'BattleEngine.php';

use BattleEngine\UnitAttributes;

class OG
{
    public const SMALL_CARGO = 0;
    public const LARGE_CARGO = 1;
    public const LIGHT_FIGHTER = 2;
    public const HEAVY_FIGHTER = 3;
    public const CRUISER = 4;
    public const BATTLESHIP = 5;
    public const COLONY_SHIP = 6;
    public const RECYCLER = 7;
    public const ESPIONAGE_PROBE = 8;
    public const BOMBER = 9;
    public const SOLAR_SATELLITE = 10;
    public const DESTROYER = 11;
    public const DEATH_STAR = 12;
    public const BATTLECRUISER = 13;
    public const ROCKET_LAUNCHER = 14;
    public const LIGHT_LASER = 15;
    public const HEAVY_LASER = 16;
    public const GAUSS_CANNON = 17;
    public const ION_CANNON = 18;
    public const PLASMA_TURRET = 19;
    public const SMALL_SHIELD_DOME = 20;
    public const LARGE_SHIELD_DOME = 21;

    public static $unitsAttributes;

    public static $names = [
        self::SMALL_CARGO => 'Small Cargo',
        self::LARGE_CARGO => 'Large Cargo',
        self::LIGHT_FIGHTER => 'Light Fighter',
        self::HEAVY_FIGHTER => 'Heavy Fighter',
        self::CRUISER => 'Cruiser',
        self::BATTLESHIP => 'Battleship',
        self::COLONY_SHIP => 'Colony Ship',
        self::RECYCLER => 'Recycler',
        self::ESPIONAGE_PROBE => 'Espionage Probe',
        self::BOMBER => 'Bomber',
        self::SOLAR_SATELLITE => 'Solar Satellite',
        self::DESTROYER => 'Destroyer',
        self::DEATH_STAR => 'Death Star',
        self::BATTLECRUISER => 'Battlecruiser',
        self::ROCKET_LAUNCHER => 'Rocket Launcher',
        self::LIGHT_LASER => 'Light Laser',
        self::HEAVY_LASER => 'Heavy Laser',
        self::GAUSS_CANNON => 'Gauss Cannon',
        self::ION_CANNON => 'Ion Cannon',
        self::PLASMA_TURRET => 'Plasma Turret',
        self::SMALL_SHIELD_DOME => 'Small Shield Dome',
        self::LARGE_SHIELD_DOME => 'Large Shield Dome',
    ];
}

OG::$unitsAttributes = [
    OG::SMALL_CARGO => new UnitAttributes(
        5.0,
        10.0,
        4000.0,
        [
            OG::ESPIONAGE_PROBE => 5,
            OG::SOLAR_SATELLITE => 5,
        ],
    ),
    OG::LARGE_CARGO => new UnitAttributes(
        5.0,
        25.0,
        12000.0,
        [
            OG::ESPIONAGE_PROBE => 5,
            OG::SOLAR_SATELLITE => 5,
        ],
    ),
    OG::LIGHT_FIGHTER => new UnitAttributes(
        50.0,
        10.0,
        4000.0,
        [
            OG::ESPIONAGE_PROBE => 5,
            OG::SOLAR_SATELLITE => 5,
        ],
    ),
    OG::HEAVY_FIGHTER => new UnitAttributes(
        150.0,
        25.0,
        10000.0,
        [
            OG::SMALL_CARGO => 3,
            OG::ESPIONAGE_PROBE => 5,
            OG::SOLAR_SATELLITE => 5,
        ],
    ),
    OG::CRUISER => new UnitAttributes(
        400.0,
        50.0,
        27000.0,
        [
            OG::LIGHT_FIGHTER => 6,
            OG::ESPIONAGE_PROBE => 5,
            OG::SOLAR_SATELLITE => 5,
            OG::ROCKET_LAUNCHER => 10,
        ],
    ),
    OG::BATTLESHIP => new UnitAttributes(
        1000.0,
        200.0,
        60000.0,
        [
            OG::ESPIONAGE_PROBE => 5,
            OG::SOLAR_SATELLITE => 5,
        ],
    ),
    OG::COLONY_SHIP => new UnitAttributes(
        50.0,
        100.0,
        30000.0,
        [
            OG::ESPIONAGE_PROBE => 5,
            OG::SOLAR_SATELLITE => 5,
        ],
    ),
    OG::RECYCLER => new UnitAttributes(
        1.0,
        10.0,
        16000.0,
        [
            OG::ESPIONAGE_PROBE => 5,
            OG::SOLAR_SATELLITE => 5,
        ],
    ),
    OG::ESPIONAGE_PROBE => new UnitAttributes(
        0.01,
        0.01,
        1000.0,
        [],
    ),
    OG::BOMBER => new UnitAttributes(
        1000.0,
        500.0,
        75000.0,
        [
            OG::ESPIONAGE_PROBE => 5,
            OG::SOLAR_SATELLITE => 5,
            OG::ROCKET_LAUNCHER => 20,
            OG::LIGHT_LASER => 20,
            OG::HEAVY_LASER => 10,
            OG::GAUSS_CANNON => 5,
            OG::ION_CANNON => 10,
            OG::PLASMA_TURRET => 5,
        ],
    ),
    OG::SOLAR_SATELLITE => new UnitAttributes(
        1.0,
        1.0,
        2000.0,
        [],
    ),
    OG::DESTROYER => new UnitAttributes(
        2000.0,
        500.0,
        110000.0,
        [
            OG::ESPIONAGE_PROBE => 5,
            OG::SOLAR_SATELLITE => 5,
            OG::BATTLECRUISER => 2,
            OG::LIGHT_LASER => 10,
        ],
    ),
    OG::DEATH_STAR => new UnitAttributes(
        200000.0,
        50000.0,
        9000000.0,
        [
            OG::SMALL_CARGO => 250,
            OG::LARGE_CARGO => 250,
            OG::LIGHT_FIGHTER => 200,
            OG::HEAVY_FIGHTER => 100,
            OG::CRUISER => 33,
            OG::BATTLESHIP => 30,
            OG::COLONY_SHIP => 250,
            OG::RECYCLER => 250,
            OG::ESPIONAGE_PROBE => 1250,
            OG::BOMBER => 25,
            OG::SOLAR_SATELLITE => 1250,
            OG::DESTROYER => 5,
            OG::BATTLECRUISER => 15,
            OG::ROCKET_LAUNCHER => 200,
            OG::LIGHT_LASER => 200,
            OG::HEAVY_LASER => 100,
            OG::GAUSS_CANNON => 50,
            OG::ION_CANNON => 100,
        ],
    ),
    OG::BATTLECRUISER => new UnitAttributes(
        700.0,
        400.0,
        70000.0,
        [
            OG::SMALL_CARGO => 3,
            OG::LARGE_CARGO => 3,
            OG::HEAVY_FIGHTER => 4,
            OG::CRUISER => 4,
            OG::BATTLESHIP => 7,
            OG::ESPIONAGE_PROBE => 5,
            OG::SOLAR_SATELLITE => 5,
        ],
    ),
    OG::ROCKET_LAUNCHER => new UnitAttributes(
        80.0,
        20.0,
        2000.0,
        [],
    ),
    OG::LIGHT_LASER => new UnitAttributes(
        100.0,
        25.0,
        2000.0,
        [],
    ),
    OG::HEAVY_LASER => new UnitAttributes(
        250.0,
        100.0,
        8000.0,
        [],
    ),
    OG::GAUSS_CANNON => new UnitAttributes(
        1100.0,
        200.0,
        35000.0,
        [],
    ),
    OG::ION_CANNON => new UnitAttributes(
        150.0,
        500.0,
        8000.0,
        [],
    ),
    OG::PLASMA_TURRET => new UnitAttributes(
        3000.0,
        300.0,
        100000.0,
        [],
    ),
    OG::SMALL_SHIELD_DOME => new UnitAttributes(
        1.0,
        2000.0,
        20000.0,
        [],
    ),
    OG::LARGE_SHIELD_DOME => new UnitAttributes(
        1.0,
        10000.0,
        100000.0,
        [],
    ),
];
