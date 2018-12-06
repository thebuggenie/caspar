[Back to Contents](../README.md)

# Getting started

This how-to describes how to get started using the Caspar framework. It assumes some basic knowledge about
php, composer and systems set-up, but should be easy to follow.

If you are unfamiliar with MVC frameworks in general, you may want to have a look at [MVC explained](mvc-explained.md)
before continuing.

## Install composer
Caspar uses [composer](https://getcomposer.org) for dependency management, and 
should be installed and used via composer.
Download and install composer from the website before continuing.

## Create your project and add Caspar as a dependency
To use Caspar as a framework for your application, create an empty directory that 
will contain your application, then add Caspar as a composer dependency by 
running `composer require thebuggenie/caspar`. This will download and install Caspar, 
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
by running the Caspar `create_app` command from your project folder (substitute `MyApp` with the 
name of your application without spaces):
```
vendor/bin/caspar create_app MyApp
```

This will create the default directory structure for your Caspar application.

### Adding the b2db ORM
The Caspar framework can work with any ORM, but comes with built-in support for the b2db ORM.

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
*Example `composer.json`*

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
Explaining web server setups is beyond the scope of this little tutorial. However, to get
started, you will have to enable some configuration for the folder you're accessing your
application through, as Caspar require the rewrite configuration to be working before the
urls will work properly.

If you followed the guide above, a default .htaccess is included in the `/public` folder of 
your project. This will enable the rewrite functionality if you are using Apache, and have set
`AllowOverride All` in your apache config. If you don't know what that means, you probably haven't,
so now would be a good time to make sure that is configured.

Regardless, there are two ways to set this up using Apache. Substitute the configuration examples
for your favourite web server if you are using a different one.

### Virtual host
When running as a virtual host, a dedicated hostname will point to the root of your application.

If you're using Apache, you can use the following simple config to set up a virtual host
with your application (put the following content inside a file called (example) `/etc/apache2/sites-available/myapp.conf`):
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
*Example apache virtual host configuration*

If your application is running in a local development environment, remember to add the 
hostname to your `hostnames` file (`/etc/hostnames` on unix, `<Windows>\System32\drivers\etc` on Windows)
 
If this is hosted publicly (or on a different machine), set the `ServerName` config 
directive to a valid hostname.

On linux, run `sudo a2ensite myapp.conf` and `sudo service apache2 restart` to enable the virtual host.

### Subdirectory
If you're hosting the application in a folder on your existing host, the `AllowOverride All` directive
must be set, to allow the included `/public/.htaccess` to be read.

Open the `/public/.htaccess` file and set the `RewriteBase` directive to match the path (including 
`public`). If you are accessing your application on (http://localhost/dev/myapp/public), set it to 
`RewriteBase /dev/myapp/public`
