[<< Adding data to your database](tutorial-adding-data.md) | [Back to Contents](../README.md)

# Table objects
Whenever we need to retrieve data from the database, we use table objects which takes data from our database objects,
turn them into data objects and returns them back to us.

Let's create a table object for the `Article` class we just created. Because our table objects represent the table in 
our database (and database table names are usually plural), we use the plural form of our database object as the name 
of the table class. 

Also, for organization sake, we create a separate namespace for the table objects. In the default application skeleton, 
the `tables` namespace already contains your user tables in the folder `application\entities\tables`. Let's create a 
new `Articles` class that extends `\b2db\Table` in the `application\entities\tables` directory.

Create the file `application/entities/tables/Articles.php` with the following content: 
```php
<?php

    namespace application\entities\tables;

    class Articles extends \b2db\Table
    {
    }
```

As you can see, there's not much to this table class yet. To function properly, b2db needs to know two things:
* which database table this class gets data from
* which data object to turn data into

To achieve this, we annotate *the class* with the necessary information. Update the class we just created with the 
following annotations:
```php
<?php

    namespace application\entities\tables;

    /**
     * @Table(name="articles")
     * @Entity(class="\application\entities\Article")
     */
    class Articles extends \b2db\Table
    {
    }
```

Let's go through what's going on here:
* The [`@Table` annotation](../annotations/table.md) with its `name` property tells b2db which database table to 
connect to:
  * when objects are retrieved from the database, b2db will `SELECT FROM` this table
  * when tables are joined on eachother, b2db uses this name for `JOIN` statements
  * when you run `->create()` to create the table, b2db knows to use this as the table name. 
* The [`@Entity` annotation](../annotations/entity.md) tells b2db which data object to instantiate when returning 
data from the database:
  * when using `select*()` methods from the table (you'll learn about these later), b2db instantiates objects of this type
  * b2db uses the data definitions in the class specified in this annotation to determine which columns are available 

# Creating tables in our database
Now that we've told b2db about how the data is defined and stored, we can proceed to create the database table for the 
article class, and all the user tables used by the user class. We use the `create_tables` Caspar task for that. 

Run the following command from the root of your project:
```bash
vendor/bin/caspar create_tables
```

Caspar will look for all table definitions in our `application/entities/tables` folder and create the tables for them
using our defined data objects. After running this command, you will have the following tables in your database:
* articles
* users
* user_sessions
* user_tokens

Next up: Storing and retrieving data

[Storing and retrieving data >>](tutorial-storing-retrieving-data.md) 
