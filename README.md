# OG Battle Engine
A battle engine for OGame and its clones with an API for PHP and Python.

[![CI](https://github.com/patrykstefanski/og-battle-engine/workflows/CI/badge.svg)](https://github.com/patrykstefanski/og-battle-engine/actions)

## Requirements
* C99 compiler
* CMake >= 3.1
* PHP >= 7.3
* Python >= 3.6

## Usage

### Get battle engine executable
There are 2 ways how to obtain a battle engine executable:

#### 1. Use prebuilt executables
You can download prebuilt executables for your operating system in [releases](https://github.com/patrykstefanski/og-battle-engine/releases) page.

#### 2. Build battle engine
Or, you can build an executable yourself:

```
$ cmake -B build -DCMAKE_BUILD_TYPE=Release
$ cmake --build build --config Release
```

### Run examples
Make sure you specify the correct path to the obtained battle engine binary in the examples.
For instance, in _example-battle.php_ you need to replace the path in:

```php
$battleEngine = new BattleEngine('./build/BattleEngine', OG::$unitsAttributes);
```

After that, you can run the examples:

```
$ php example-battle.php
$ python example-battle.py
```
