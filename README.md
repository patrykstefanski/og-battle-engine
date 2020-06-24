# OG Battle Engine
A battle engine for OGame and its clones with an API for PHP and Python.

[![CI](https://github.com/patrykstefanski/og-battle-engine/workflows/CI/badge.svg)](https://github.com/patrykstefanski/og-battle-engine/actions)

## Requirements
* C99 compiler
* CMake >= 3.1
* PHP >= 7.3
* Python >= 3.6

## Usage

### Build battle engine
```
$ cmake -B build -DCMAKE_BUILD_TYPE=Release .
$ make -C build
```

### Run examples
```
$ php example.php
$ python example.py
```
