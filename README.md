Phifty Web Framework
====================

[![Build Status](https://travis-ci.org/phifty-framework/Phifty.svg?branch=master)](https://travis-ci.org/phifty-framework/Phifty)
[![Coverage Status](https://coveralls.io/repos/github/phifty-framework/Phifty/badge.svg?branch=master)](https://coveralls.io/github/phifty-framework/Phifty?branch=master)

[![Latest Stable Version](https://poser.pugx.org/phifty/phifty/v/stable)](https://packagist.org/packages/phifty/phifty)
[![Total Downloads](https://poser.pugx.org/phifty/phifty/downloads)](https://packagist.org/packages/phifty/phifty)
[![Latest Unstable Version](https://poser.pugx.org/phifty/phifty/v/unstable)](https://packagist.org/packages/phifty/phifty)
[![License](https://poser.pugx.org/phifty/phifty/license)](https://packagist.org/packages/phifty/phifty)

[![Monthly Downloads](https://poser.pugx.org/phifty/phifty/d/monthly)](https://packagist.org/packages/phifty/phifty)
[![Daily Downloads](https://poser.pugx.org/phifty/phifty/d/daily)](https://packagist.org/packages/phifty/phifty)


Documentation
--------------------

See [wiki](https://github.com/phifty-framework/Phifty/wiki)


Structure Overview
------------------

```
Phifty\App (is a Bundle)
  Phifty\Kernel(

    /* Core service providers */
    "event" => EventServiceProvider,
    "config" => ConfigServiceProvider,

    /* Extra service providers */
    [services] => Array Phifty\ServiceProvider[string]

    "bundles" => BundleServiceProvider(
      Array Phifty\Bundle[string]
    )
  )
```

Bootstrap Flow
--------------
1. Create the generated App\ConfigLoader object.
2. Create the generated App\Kernel object
    1. Load the service providers into `$kernel`
    2. Load the bundles into `$kernel`
3. Create `App\App` instance with `App($kernel)`
4. Call App::boot() method to boot the app.
    1. Call Kernel::boot to boot the service providers and the bundles.
        1. Run ::boot on all the service providers
        2. Run ::boot on all the bundles
    2. Run the App boot code.
