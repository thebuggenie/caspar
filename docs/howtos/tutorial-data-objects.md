[<< Adding data to your database](tutorial-adding-data.md) | [Back to Contents](../README.md)

# Data objects
Data objects are regular php objects that you want to store in the table. Examples are blog posts and users, which
are all stored in the database. We represent these with data objects in our application. Your application already has
objects for the user, which comes with the default application skeleton. You don't actually have any users in your 
database just yet, but will add these later.

We want to also add data objects for our blog posts, which we'll call `Article`. In addition, we want to store tags
on our blog posts, which we'll creatively call `Tag`. Remember that data objects represent *individual* pieces of data. 
Because of this, data object names are *always singular*, never plural. 

To get started, create a new `Article` class that extends `b2db\Saveable` in the `application\entities` 
directory. 

It needs a few basic properties, such as `id`, `created_at`, `author`, `title` and `content`. We also want
to add a property telling us the `state` of the blog post, to let us distinguish between drafts, published and deleted
posts. We also want getters and setters for the different properties so we can change the values. 

```php
<?php

    namespace application\entities;

    class Article extends \b2db\Saveable
    {

        protected $id;

        protected $created_at;

        protected $author;

        protected $title;

        protected $content;

        protected $state;
        
        public function getId()
        {
            return $this->id;
        }
        
        public function getCreatedAt()
        {
            return $this->created_at;
        }
        
        public function setCreatedAt($created_at)
        {
            $this->created_at = $created_at;
        }
        
        public function getAuthor()
        {
            return $this->author;
        }
        
        public function setAuthor($author)
        {
            $this->author = $author;
        }
        
        public function getTitle()
        {
            return $this->title;
        }
        
        public function setTitle($title)
        {
            $this->title = $title;
        }
        
        public function getContent()
        {
            return $this->content;
        }
        
        public function setContent($content)
        {
            $this->content = $content;
        }
        
        public function getState()
        {
            return $this->state;
        }
        
        public function setState($state)
        {
            $this->state = $state;
        }
        
    }
```

The file needs to follow the [PSR-4 naming convention](https://www.php-fig.org/psr/psr-4) so composer can autoload it, 
so save the file as `application/entities/Article.php`. 

With this class definition, we can now create new `Article` objects in our application:
```php
<?php
$article = new \application\entities\Article();
$article->setTitle('My first blog post!');
$article->setContent('This is the content of my first blog post!');
```

However - in this state, the object cannot be saved to the database. To make this happen, we need to tell b2db how the 
data in the object is stored in the database.

# Annotating your objects
To avoid having to write lots of custom code that interferes with your application logic, b2db uses docblock 
annotations to define b2db data. To begin with, we will define the database details for each of our object properties.

## Defining a column
Data object properties that you wish to store and retrieve from the database must be annotated with a `@Column` 
annotation. This tells b2db that the property is mapped to a database column, and how to transform the data stored
in the database to and from data in your object.  

Let's tell b2db how to handle the properties in our data object:
```php
<?php

    namespace application\entities;

    /**
     * Article class
     * @Table(name="\application\entities\tables\Articles")
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
         * @Column(type="integer", length=3)
         */
        protected $state;
        
    }
```

The [`@Column` annotation](../annotations/column.md) is pretty self-explanatory, but here's a short overview:
* **`type`:** one of the following: 
  * `integer`
  * `string` (or `varchar`)
  * `float`
  * `serializable`
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

### Id column
In addition to the different column annotations, we need to tell b2db which field contains the unique identifier for 
the row, so it knows how to identify the different items, also known as the id column. We do this by annotating the 
property that contains the id with an [`@Id` annotation](../annotations/id.md).

When storing new items b2db will automatically assign the id value to th`@Id` annotated property when the object is 
stored. 

**Note:** b2db does not support composite keys.

## Table name
We've told b2db how to *define* our data objects, but we haven't told it anything about where or how it's stored or 
retrieved yet. To do this, we need to connect it to a table class by using a [`@Table` annotation](../annotations/table.md).

The `@Table` annotation contains a `name` property with the full class name of the class we want to use a table class.
We specify `\application\entities\tables\Articles` because that is the name of the class we will create.   

Now we need to create the table class that handles querying the database when we need to look up our objects. 
In the next section we'll look at table objects:
[Table objects >>](tutorial-table-objects.md)
