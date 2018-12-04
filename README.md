Caspar lightweight MVC framework for PHP applications
=====================================================

# Introduction
Caspar is a lightweight MVC-based framework for PHP 7.1 (or newer) applications. 

# Getting started

## Install composer
Caspar uses [composer](https://getcomposer.org) for dependency management, and 
should be installed and used via composer.
Download and install composer from the website before continuing.

## Create your project and add caspar as a dependency
To use caspar as a framework for your application, create an empty directory that 
will contain your application, then add caspar as a composer dependency by 
running `composer require thebuggenie/caspar`. This will download and install caspar, 
and add it to your project's `composer.json` (if you haven't created a `composer.json` yet, 
running the command will create one for you.

### Running commands from the command line
Caspar provides a binary through composer, available as `vendor/bin/caspar`. 

**Important**: Make sure the `php` executable is running the same version as your web
server, and that they are both at least version 7.1. You can check which version your
php command line executable is running as, by executing `php --version`. 

## Create the application skeleton for your app
Caspar requires a specific directory structure to function, as it expects your files to 
follow a certain convention. To get you started, create the default application skeleton
by running the caspar `create_app` command from your project folder (substitute `MyApp` with the 
name of your application without spaces):
```
vendor/bin/caspar create_app MyApp
```

This will create the default directory structure for your caspar application.

### Adding the b2db ORM
The caspar framework can work with any ORM, but comes with built-in support for the b2db ORM.

After running the above command, add the b2db dependency to your `composer.json` by running
`composer require thebuggenie/b2db`

## Update composer autoloader
After running this command, update the composer autoloader to load the files from your 
application directory, by adding the following section to your `composer.json`:
```json
    "autoload": {
        "psr-4" : {
            "application\\" : "application"
        }
    }
```

If you haven't changed anything else from the beginning of this guide, your `composer.json`
should look like this:
```json
{
    "require": {
        "thebuggenie/caspar": "dev-master",
        "thebuggenie/b2db": "^2.0"
    },
    "autoload": {
        "psr-4" : {
            "application\\" : "application"
        }
    }
}
```

Remember to run `composer update` or `composer dump-autoload` after this change, so 
composer actually updates its autoloader.

## Configuring your application
At this point, your skeleton application is ready to use, but requires a small bit of 
configuration.

Open up the configuration file (`application/configuration/caspar.yml`) and add the 
correct database configuration values for your database connection.

## Creating tables
Caspar can automatically create any tables defined in your `application/entities/tables` 
folder, using the `create_tables` task. From the project folder, run:
```
vendor/bin/caspar create_tables
```  

This will create tables based on the definitions of all your table files in 
`application/entities/tables` and their corresponding entities in `application/entities`.

For more information, see the b2db documentation.

## Setting up the web server
If you're using Apache, you can use the following simple config to set up a virtual host
with your application (can be put in `/etc/sites-available/myapp.conf`):
```apacheconfig
<VirtualHost *:80>
        ServerName myapp.l

        DocumentRoot /var/www/myapp/public
        <Directory /var/www/myapp/public>
                Options FollowSymLinks
                AllowOverride All
        </Directory>

        ErrorLog /var/log/apache2/myapp.error.log

</VirtualHost>
```
If your application is running in a local development environment, remember to add the 
hostname to your `hostnames` file (`/etc/hostnames` on unix).
 
If this is hosted publicly (or on a different machine), set the `ServerName` config 
directive to a valid hostname.

Run `sudo a2ensite myapp.conf` and `sudo service apache2 restart` to enable the virtual host.
