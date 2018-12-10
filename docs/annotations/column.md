# @Column

## Defining a column
Data object properties that you wish to store and retrieve from the database must be annotated with a `@Column` 
annotation. This tells b2db that the property is mapped to a database column, and how to transform the data stored
in the database to and from data in your object.  

Example object with `@Column` annotated properties
```php
<?php

    namespace application\entities;

    /**
     * Article class
     */
    class Article extends \b2db\Saveable
    {

        /**
         * @Id
         * @Column(type="integer", auto_increment=true, length=10)
         */
        protected $id;

        /**
         * @Column(type="string", length=200)
         */
        protected $created_at;

        /**
         * @Column(type="integer", length=10)
         */
        protected $author;

        /**
         * @Column(type="string", length=200)
         */
        protected $title;

        /**
         * @Column(type="string")
         */
        protected $content;

        /**
         * @Column(type="serializable")
         */
        protected $data;
        
    }
```


The [`@Column` annotation](../annotations/column.md) is pretty self-explanatory, but here's a short overview:
* **`type`:** one of the following: 
  * `integer`
  * `string` (or `varchar`)
  * `float`
  * `serializable` - this is for array values that are serialized before stored to the database, and unserialized 
  when the object is populated / created. You can treat the value as an array.
  * `blob`
* **`length`:** for column types that need a length, specify the `length` property of the `@Column` annotation
* **`default`:** b2db will read the default value of the property if it is set. Sometimes you may want a different 
default than the object default, in which case you can specify it here.
* **`auto_increment`:** for id columns or other fields that the database should auto increment, specify it here. For 
postgres and other database types that doesn't really have an `auto_increment` flag on columns, b2db will make the 
index that is used by the database and assign it to the column
* **`name`:** if you want to specify a column name, use the `name` property to tell b2db what the name of the column is

### Column names
You might notice that we haven't told b2db what the names of the columns are in the database in the example above. 
That is because, by default, we only need to do that if the names of the columns differ from the names of the object 
properties. This means in our database, the `$id` property will be stored in the `id` column, the `$title` property 
will be stored in the `title` column, etc. 

*Note:* b2db automatically ignores `_` in front of property names when column names are calculated. This means that 
for a property named `$_title`, the column name will automatically be `title`, without a need to specify it manually.

