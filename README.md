# LowCal PHP

LowCal PHP is a microframework built for PHP 7.1+.

  - Lightweight (small memory footprint)
  - Easy to use (get a web app up and running in under 2 minutes)
  - Non-obtrusive (will not get in the way of your own architecture) 
  - Strictly typed (as much as PHP allows for it)

# Getting Started

The supplied bootstrap.php contains all the ncessary start code for your application. 

**Require the PSR4 Autoloader:**
```php
$loader = new Psr4Autoloader();
$loader->register();
$loader->addNamespace('LowCal\\', 'LowCal/');
```

**Load necessary configuration files:**
```php
Config::loadFile('config.php');
Config::loadConfigForEnv('config.php');
```

**Init LowCal Base class:**
```php
$LowCal = new Base($loader);
```
## That's it!
You now have the basis for your web app. Below are the following steps you'll probably take depending on what you are building.

The supplied init.php allows you to start putting all of your application specific logic/setup.

**Configure your apps main url:**
```php
$LowCal->routing()->setSiteUrl(Config::get('APP_ROOT_URL'));
```

**Add your first routing rule:**
```php
$LowCal->routing()->add('home', '/<lang>/', '\LowCal\Controller\Home', 'indexAction');
```

**Do you need to support multiple languages?**
```php
$LowCal->locale()->addLanguage('en', 'en-us');
$LowCal->locale()->addLanguage('fr', 'fr-ca');
$LowCal->locale()->setCurrentLocale('en');
```

**Register your log files:**
```php
$LowCal->log()->registerFile('mysqli', Config::get('LOGS_DIR'))
	          ->registerFile('memcached', Config::get('LOGS_DIR'));
```

**Choose your tempalting engine (LowCal comes with a basic file-based one):**
```php
$LowCal->view()->setViewEngineType(Config::get('VIEW_ENGINE_PHP'))
		       ->setViewEngineObject(new \LowCal\Module\View\PHP($LowCal))
		       ->getViewEngineObject()
		       ->setViewDir(Config::get('VIEWS_DIR'));
```

**Setup a Mysql database connection (only connects when needed):**
```php
$LowCal->db()->addServer(
    'firstmysqliserver', 
    Config::get('DATABASE_SELECTED_TYPE'), 
    Config::get('APP_DB_USER'), 
    Config::get('APP_DB_PASSWORD'), 
    Config::get('APP_DB_NAME'), 
    Config::get('APP_DB_HOST'), 
    Config::get('APP_DB_PORT')
);
```

**Finally, listen for requested route and echo the action's response:**
```php
echo $LowCal->routing()->listen();
```

# There Is a Lot More
LowCal PHP has a lot under the hood, that tries to keep your application as streamlined as possible, without getting in the way of your own architecture and creativity. LowCal's code should be very easy to read, and comes commented as well. So go ahead, and start your next web app with LowCal PHP!


This work is copyright (c) 2017, Consultation Kevork Aghazarian, and is licensed under a [Creative Commons Attribution 4.0 International License](http://creativecommons.org/licenses/by/4.0/ "License")