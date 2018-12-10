[<< Setting up the default structure](your-first-application.md) | [Back to Contents](../README.md)

# Setting up the default structure
In this section, we will:
* create our application folder
* add Caspar and b2db as project dependencies and create our default database
* create two modules: `main` and `admin`

## Create your project and add Caspar as a dependency
Start by creating an empty directory that will contain our application. For the purposes of this tutorial, we will 
assume you are placing the project in your `/var/www` folder. Call the application `blog`:
```bash
cd /var/www
mkdir blog
cd blog
```

Now, add Caspar as a composer dependency: 
```bash
composer require thebuggenie/caspar
```

This will create a `composer.json` file in your project root, download and install Caspar and add it as a project 
dependency in your `composer.json` file.

### Installing libraries through composer
Some libraries that are installed via composer requires certain php extensions to be installed. If you get errors about
missing extensions (`ext-something is missing`), install the extension using your system package manager and/or make 
sure it's enabled in your php config.  

## Running commands from the command line
Caspar provides a binary through composer, available as `vendor/bin/caspar`. This command lets you run caspar
tasks from the command line. There are several tasks available that can help you during development. To list the
available tasks, run `vendor/bin/caspar help`. 

**Important**: Make sure the `php` executable is running the same version as your web
server, and that they are both at least version 7.1. You can check which version your
php command line executable is running as, by executing `php --version`. 

## Create the application skeleton for your app
Caspar requires a specific directory structure to function, as it expects certain files in specific locations. To get 
you started, create the default application skeleton by running the Caspar `create_app` command from your project 
folder:
```
vendor/bin/caspar create_app BlogApp
```

This will create the default directory structure for your blog application.

## Adding the b2db ORM
The Caspar framework can work with any ORM, but comes with built-in support for the b2db ORM. We will use this ORM
in all the examples in this guide. If you're familiar with a different ORM, you can set it up and use that, but that
is outside the scope of this guide.

After running the above command, add the b2db dependency to your `composer.json` by running
```bash
composer require thebuggenie/b2db:dev-dev
```

After running this command, we need to make sure composer generates autoload files for our application classes. 
Update the composer autoloader to load the files from your application directory, by adding the following section to 
your `composer.json`:
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
        "thebuggenie/b2db": "dev-dev"
    },
    "autoload": {
        "psr-4" : {
            "application\\" : "application"
        }
    }
}
```
*Example `composer.json`*

To make sure composer updates its autoloading index, run the following command:
```bash 
composer dump-autoload
```

## Configuring your application
At this point, your skeleton application is ready to use, but requires a small bit of 
configuration, so we can connect to a database.

All settings are stored in the caspar configuration file (`application/configuration/caspar.yml`), and this is where
we will put any settings we need to store as well. 

In the next section, we will connect your application to the database, and add some tables to it.
[Adding data to your database >>](tutorial-adding-data.md)