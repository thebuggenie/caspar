[<< Setting up the default structure](tutorial-default-structure.md) | [Back to Contents](../README.md)

# Connecting your application to the database
The database connection details are stored in the configuration file, so we need to add the correct database 
configuration values for your database connection.

In your database, create a database called `blog`, and make sure the user you are connecting as has full access to 
this database.

Now look for the `b2db` section in the caspar configuration file (`application/configuration/caspar.yml`) and change 
the values to the correct values for your database. The `caspar.yml` should now contain a section that looks like this 
(We've updated the connection details here, but remember to input the correct details for your own database 
connection):
```yml
services:
  b2db:
    auto_initialize: true
    callback: [\b2db\Core, 'initialize']
    arguments:
      -
        driver: mysql
        hostname: localhost
        username: root
        password: password
        database: blog
        debug: false
        tableprefix: 
      - [\caspar\core\Caspar, getCache]
```

# Working with database objects
The b2db ORM is the data layer that takes data from the database and turns it into php objects that you can work
with. It also lets you save your data (back) to the database by transforming your objects into database rows.

To make all this magic happen, we work with two types of objects:
* data objects
* table objects

To visualize these two types, think of table objects as the gateways to the different database table, and the data
objects as individual rows in your tables. Whenever you need to get data from your database, you ask (query) your
table objects, and they return data objects that represents data in your database.

In the next couple of sections, we will look at how b2db works. First up, data objects!

[Data objects >>](tutorial-data-objects.md)
