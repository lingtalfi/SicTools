SicTools
========
2019-02-06 -> 2021-08-05

Tools for implementing
the [sic notation](https://github.com/karayabin/universe-snapshot/blob/master/universe/Ling/NotationFan/sic.md) in a php
application.

This is part of the [universe framework](https://github.com/karayabin/universe-snapshot).


Install
==========
Using the [planet installer](https://github.com/lingtalfi/Light_PlanetInstaller) via [light-cli](https://github.com/lingtalfi/Light_Cli)
```bash
lt install Ling.SicTools
```

Using the [uni](https://github.com/lingtalfi/universe-naive-importer) command.

```bash
uni import Ling/SicTools
```

Or just download it and place it where you want otherwise.





Summary
=======

- [SicTools api](https://github.com/lingtalfi/SicTools/blob/master/doc/api/Ling/SicTools.md) (generated
  with [DocTools](https://github.com/lingtalfi/DocTools))
- [HotServiceResolver](https://github.com/lingtalfi/SicTools/blob/master/doc/HotServiceResolver.md)
- [ColdServiceResolver](https://github.com/lingtalfi/SicTools/blob/master/doc/ColdServiceResolver.md)
- [SicTool](https://github.com/lingtalfi/SicTools/blob/master/doc/SicTool.md)
- [Conception notes](https://github.com/lingtalfi/SicTools/blob/master/doc/pages/conception-notes.md)

History Log
------------------

- 1.6.8 -- 2021-08-05

    - add HotServiceResolver->setCustomResolveNotationCallback method 
  
- 1.6.7 -- 2021-06-25

    - fix HotServiceResolver->getService not handling the service name when customNotation returns null
  
- 1.6.6 -- 2021-05-31

    - Removing trailing plus in lpi-deps file (to work with Light_PlanetInstaller:2.0.0 api

- 1.6.5 -- 2021-03-05

    - update README.md, add install alternative

- 1.6.4 -- 2021-02-09

    - update SicFileCombinerUtil::combine, recursive feature is now an option
  
- 1.6.3 -- 2021-02-09

    - update SicFileCombinerUtil::combine, add options argument

- 1.6.2 -- 2020-12-08

    - Fix lpi-deps not using natsort

- 1.6.1 -- 2020-12-04

    - Add lpi-deps.byml file

- 1.6.0 -- 2020-08-17

    - update SicFileCombinerUtil, now handle internal variable references

- 1.5.2 -- 2019-12-09

    - check commit

- 1.5.1 -- 2019-12-09

    - update HotServiceResolver->getService, now throws an exception if a non callable is passed for the methods or
      methods_collection property

- 1.5.0 -- 2019-11-06

    - the instance property now accepts custom notation

- 1.4.5 -- 2019-07-18

    - update docTools documentation, add links to source code for classes and methods

- 1.4.4 -- 2019-07-17

    - fix SicFileCombinerUtil.combine method not merging lazy variables correctly

- 1.4.3 -- 2019-05-15

    - fix SicFileCombinerUtil typo

- 1.4.2 -- 2019-04-25

    - fix SicFileCombinerUtil->combine method handling environment variables incorrectly

- 1.4.1 -- 2019-04-25

    - add docTools doc

- 1.4.0 -- 2019-04-25

    - add SicFileCombinerUtil->setEnvironmentVariables method

- 1.3.0 -- 2019-04-09

    - update SicFileCombinerUtil class, now uses the concepts of lazy override and variable references

- 1.2.1 -- 2019-04-05

    - fix ColdServiceResolver not turning associative arrays into numerical arrays when calling them as
      constructor/method arguments

- 1.2.0 -- 2019-04-05

    - add SicFileCombinerUtil class

- 1.1.0 -- 2019-02-07

    - add callable notation interpretation for SicTools\HotServiceResolver and SicTools\ColdServiceResolver

- 1.0.1 -- 2019-02-07

    - fix SicTools\ColdServiceResolver->addServiceCode not resetting stack

- 1.0.0 -- 2019-02-06

    - initial commit