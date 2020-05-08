from BattleEngine import UnitAttributes, UnitKind

SmallCargo = UnitKind(0)
LargeCargo = UnitKind(1)
LightFighter = UnitKind(2)
HeavyFighter = UnitKind(3)
Cruiser = UnitKind(4)
Battleship = UnitKind(5)
ColonyShip = UnitKind(6)
Recycler = UnitKind(7)
EspionageProbe = UnitKind(8)
Bomber = UnitKind(9)
SolarSatellite = UnitKind(10)
Destroyer = UnitKind(11)
DeathStar = UnitKind(12)
Battlecruiser = UnitKind(13)
RocketLauncher = UnitKind(14)
LightLaser = UnitKind(15)
HeavyLaser = UnitKind(16)
GaussCannon = UnitKind(17)
IonCannon = UnitKind(18)
PlasmaTurret = UnitKind(19)
SmallShieldDome = UnitKind(20)
LargeShieldDome = UnitKind(21)

names = {
    SmallCargo: 'Small Cargo',
    LargeCargo: 'Large Cargo',
    LightFighter: 'Light Fighter',
    HeavyFighter: 'Heavy Fighter',
    Cruiser: 'Cruiser',
    Battleship: 'Battleship',
    ColonyShip: 'ColonyShip',
    Recycler: 'Recycler',
    EspionageProbe: 'Espionage Probe',
    Bomber: 'Bomber',
    SolarSatellite: 'Solar Satellite',
    Destroyer: 'Destroyer',
    DeathStar: 'Death Star',
    Battlecruiser: 'Battlecruiser',
    RocketLauncher: 'Rocket Launcher',
    LightLaser: 'Light Laser',
    HeavyLaser: 'Heavy Laser',
    GaussCannon: 'Gauss Cannon',
    IonCannon: 'Ion Cannon',
    PlasmaTurret: 'Plasma Turret',
    SmallShieldDome: 'Small Shield Dome',
    LargeShieldDome: 'Large Shield Dome',
}

units_attributes = {
    SmallCargo: UnitAttributes(
        weapons=5.0,
        shield=10.0,
        armor=4000.0,
        rapid_fire={
            EspionageProbe: 5,
            SolarSatellite: 5,
        },
    ),
    LargeCargo: UnitAttributes(
        weapons=5.0,
        shield=25.0,
        armor=12000.0,
        rapid_fire={
            EspionageProbe: 5,
            SolarSatellite: 5,
        },
    ),
    LightFighter: UnitAttributes(
        weapons=50.0,
        shield=10.0,
        armor=4000.0,
        rapid_fire={
            EspionageProbe: 5,
            SolarSatellite: 5,
        },
    ),
    HeavyFighter: UnitAttributes(
        weapons=150.0,
        shield=25.0,
        armor=10000.0,
        rapid_fire={
            SmallCargo: 3,
            EspionageProbe: 5,
            SolarSatellite: 5,
        },
    ),
    Cruiser: UnitAttributes(
        weapons=400.0,
        shield=50.0,
        armor=27000.0,
        rapid_fire={
            LightFighter: 6,
            EspionageProbe: 5,
            SolarSatellite: 5,
            RocketLauncher: 10,
        },
    ),
    Battleship: UnitAttributes(
        weapons=1000.0,
        shield=200.0,
        armor=60000.0,
        rapid_fire={
            EspionageProbe: 5,
            SolarSatellite: 5,
        },
    ),
    ColonyShip: UnitAttributes(
        weapons=50.0,
        shield=100.0,
        armor=30000.0,
        rapid_fire={
            EspionageProbe: 5,
            SolarSatellite: 5,
        },
    ),
    Recycler: UnitAttributes(
        weapons=1.0,
        shield=10.0,
        armor=16000.0,
        rapid_fire={
            EspionageProbe: 5,
            SolarSatellite: 5,
        },
    ),
    EspionageProbe: UnitAttributes(
        weapons=0.01,
        shield=0.01,
        armor=1000.0,
        rapid_fire={},
    ),
    Bomber: UnitAttributes(
        weapons=1000.0,
        shield=500.0,
        armor=75000.0,
        rapid_fire={
            EspionageProbe: 5,
            SolarSatellite: 5,
            RocketLauncher: 20,
            LightLaser: 20,
            HeavyLaser: 10,
            GaussCannon: 5,
            IonCannon: 10,
            PlasmaTurret: 5,
        },
    ),
    SolarSatellite: UnitAttributes(
        weapons=1.0,
        shield=1.0,
        armor=2000.0,
        rapid_fire={},
    ),
    Destroyer: UnitAttributes(
        weapons=2000.0,
        shield=500.0,
        armor=110000.0,
        rapid_fire={
            EspionageProbe: 5,
            SolarSatellite: 5,
            Battlecruiser: 2,
            LightLaser: 10,
        },
    ),
    DeathStar: UnitAttributes(
        weapons=200000.0,
        shield=50000.0,
        armor=9000000.0,
        rapid_fire={
            SmallCargo: 250,
            LargeCargo: 250,
            LightFighter: 200,
            HeavyFighter: 100,
            Cruiser: 33,
            Battleship: 30,
            ColonyShip: 250,
            Recycler: 250,
            EspionageProbe: 1250,
            Bomber: 25,
            SolarSatellite: 1250,
            Destroyer: 5,
            Battlecruiser: 15,
            RocketLauncher: 200,
            LightLaser: 200,
            HeavyLaser: 100,
            GaussCannon: 50,
            IonCannon: 100,
        },
    ),
    Battlecruiser: UnitAttributes(
        weapons=700.0,
        shield=400.0,
        armor=70000.0,
        rapid_fire={
            SmallCargo: 3,
            LargeCargo: 3,
            HeavyFighter: 4,
            Cruiser: 4,
            Battleship: 7,
            EspionageProbe: 5,
            SolarSatellite: 5,
        },
    ),
    RocketLauncher: UnitAttributes(
        weapons=80.0,
        shield=10.0,
        armor=2000.0,
        rapid_fire={},
    ),
    LightLaser: UnitAttributes(
        weapons=100.0,
        shield=25.0,
        armor=2000.0,
        rapid_fire={},
    ),
    HeavyLaser: UnitAttributes(
        weapons=250.0,
        shield=100.0,
        armor=8000.0,
        rapid_fire={},
    ),
    GaussCannon: UnitAttributes(
        weapons=1100.0,
        shield=200.0,
        armor=35000.0,
        rapid_fire={},
    ),
    IonCannon: UnitAttributes(
        weapons=150.0,
        shield=500.0,
        armor=8000.0,
        rapid_fire={},
    ),
    PlasmaTurret: UnitAttributes(
        weapons=3000.0,
        shield=300.0,
        armor=100000.0,
        rapid_fire={},
    ),
    SmallShieldDome: UnitAttributes(
        weapons=1.0,
        shield=2000.0,
        armor=20000.0,
        rapid_fire={},
    ),
    LargeShieldDome: UnitAttributes(
        weapons=1.0,
        shield=10000.0,
        armor=100000.0,
        rapid_fire={},
    ),
}
