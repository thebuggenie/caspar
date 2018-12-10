[<< Adding data to your database](tutorial-adding-data.md) | [Back to Contents](../README.md)

# Table objects
Whenever we need to retrieve data from the database, we use table objects which takes data from our database objects,
turn them into data objects and returns them back to us.

Let's create a table object for our `Article` table. Because our table objects represent the table in our database, and
database tables are usually plural, we use the plural form of our database object as the name of the table. Also,
for organization sake, we create a separate namespace for the table objects. You can choose whether to have the 
namespace *under* `entities`, next to it, or somewhere else, as long as you're consistent. In this tutorial,
we'll create a `tables` namespace under our `entities` namespace, where all our tables will exist.

In the default application skeleton, the `tables` namespace already contains your user tables, so the folder
`application\entities\tables` already exists. Create a new `Articles` class that extends `\b2db\Table` in the 
`application\entities\tables` directory:
```php
<?php

    namespace application\entities\tables;

    class Articles extends \b2db\Table
    {
    }
```

As you can see, there's not much to this table yet. To function properly, b2db needs to know two things:
* which database table this class gets data from
* which data object to turn data into

To achieve this, we annotate *the class* with the necessary information:
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

* The [`@Table` annotation](../annotations/table.md) with its `name` property tells b2db which database table to 
connect to. When objects are retrieved from the database, b2db will `SELECT FROM` this table, and when tables are 
joined on eachother, b2db uses this name for `JOIN` statements. Also, b2db uses this name when you run `->create()` 
to create the table. Note that the `@Table` annotation on a table class differs slightly in use-case from a `@Table` 
annotation on a data object.
* The [`@Entity` annotation](../annotations/entity.md) tells b2db which data object to instantiate when returning 
data from the database. When using `select*()` methods from the table, b2db instantiates objects of this type, and b2db
uses the data definitions in this class to determine which columns are available. 

Now that we've told b2db about how the data is defined and stored, we can create the database table for the article 
class, and all the user tables used by the user class. We use the `create_tables` task for that. 

Run the following command from the root of your project:
```bash
vendor/bin/caspar create_tables
```

Caspar will look for all table definitions in our `application/entities/tables` folder and create the tables for them
using our defined data objects.

Next up: Storing and retrieving data

[Storing and retrieving data >>](tutorial-storing-retrieving-data.md) 
