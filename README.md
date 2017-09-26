Commands

[![Build Status](https://travis-ci.org/vasily-kartashov/log4php.svg?branch=master)](https://travis-ci.org/vasily-kartashov/log4php)

To do
===
* Add phing build test to run all tasks automatically
* Move tests into namespaces
* Extend test to cover all levels from PSR
* ~~Use latest PHP Unit~~
* ~~Fix last 4 test cases~~
* ~~Add PSR-7 Compatibility per default~~
* Profile
* Remove exclusive locking as much as possible
* Builder pattern for log configuration
* ~~Add packagist integration~~
* ~~Add test for message interpolation and exception tracing~~
* ~~Add proper recognition for tracing~~
* Add extended context resolver to event generation and pattern layout
* Add new relic appender
* Make sure that during substitutions there's no default casting issues (like array to string conversion)
* Add proper test cases for LocationInfo for different situations, including global handlers defined as function or callable