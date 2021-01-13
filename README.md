# LowCalPHP

LowCalPHP is a microframework built for PHP 7.3+.

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

**Setup a Mysql database connection (connects on demand):**
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

# Under the Hood
## bootstrap.php
This file is where your application starts and finishes. Your .htaccess file (or equivalent) will direct all php requests to bootstrap.php, and your code starts from there.

The main task of this file is to load the PSR4 autoloader, setup LowCal's required namespaces, set the most important environment variables, initiate an instance of Base, and finally, load the init.php file.

You should not modify this file in any way if you want to keep your app easy to update with future LowCal versions.

## init.php
This file is loaded by bootstrap.php and is the true beginning of your application. This is where you define all of your routing rules, languages, database connections, view overrides, and class overrides. 

You should not put application logic here. Instead, you should use common patterns like MVC to put relevant logic in controllers, views, and models.

## Config Files
LowCal comes with a config.php file and a config_local.php file. LowCal PHP uses a cascading configuration system where a base config file is first processed (config.php), and then an environment specific config file (config_local.php) is processed after, overwriting any config values found within it.  

This allows you to load different configuration values depending on your environment (local, staging, prod, etc...). You only need to define a subset of the configuration values in your environment specific configuration files. In other words, only supply the variables that differ from the base file (though you could define them all if you want).

## Config Variables

**BASE_DIR**: Root directory of your application.

**LOWCAL_ENV**: Environment loaded from the htaccess file, using getenv().

***_DIR**: These variables define paths to different directories in LowCalPHP.

**VIEW_ACTIVE_ENGINE**: Set the view engine that should be used.

**VIEW_ENGINE_PHP**: Set this as the value for *VIEW_ACTIVE_ENGINE* as it uses the built-in php file based viewing engine.

**OUTPUT_COMPRESSION**: If set to true, LowCaLPHP will attempt to compress your HTML output into one long line, saving bytes in data transfer.

**OUTPUT_BUFFERING**: If set to true, LowCalPHP will buffer all output until the end of your applications execution (using ob_start()).

**DOMAIN_PROTECTION**: If set to true, LowCalPHP will check the domain used to make a request to your application, and will ensure only authorized domains are used (prevents rare forms of attacks or intrusion).

**DOMAIN_ALLOWED_DOMAINS**: Specify which domains are expected by your application.

**DOMAIN_SOLUTION**: If an invalid domain is detected, define what will be done. LowCalPHP can either redirect to a URL, or display a specific view. *Type* can be "redirect" or "view". Provided a routing ID or a view name, respectively.

**SECURITY_HASH_TABLE**: You can override the default hash table found in the Security module that is used for generating two-way encrypted strings. You can generate a randomly sorted hashing array by calling the regenerateHash() method in the Security module.

**SECURITY_SALT**: The default salt used in LowCal Security methods. You should really change this when going to production.

**SECURITY_POISON_CONSTRAINTS**: LowCalPHP can poison hashe strings to make them more difficult to crack. You should override the array that is found in the Security module using this variable. Call the regeneratePoisonConstraints() method in the Security module to get a new, randomized constraint array.

**SECURITY_CHECKSUM**: Used to ensure your SECURITY_* variables match those of another installation. Checksum ensures your variables are the same as another installation to prevent security related functions from breaking. You can generate your current installations checksum using getChecksum() method in the Security module.

**SECURITY_ARGONID_OPTIONS**: LowCalPHP uses PHP7's ARGON hashing methods. This variable allows you to tweak the algorithm's options.

**CACHE_SELECTED_TYPE**: LowCal supports local caching (to help simulate actual caching when not available), Memcache, and Couchbase. This variable tells LowCal which Classes to use when cache methods are used.

**CACHE_TYPE_***: Set one of these variables as the value for *CACHE_SELECTED_TYPE*.

**SETTING_CACHE_***: These aree various varibles defining how LowCal should interact with the chosen caching system.



This work is copyright (c) 2017, Consultation Kevork Aghazarian, and is licensed under a [Creative Commons Attribution 4.0 International License](http://creativecommons.org/licenses/by/4.0/ "License")