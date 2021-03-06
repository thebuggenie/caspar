[Back to Contents](README.md)

# Structuring your application in modules
A Caspar application is divided into modules, and you decide how to organise this. If you prefer to not
split your application into different modules, you can manage just fine with only one module.

Structuring your application in modules has the benefit of allowing you to logically separate the different parts of 
your application, avoids code duplication and improves the structure and flow of your application.

## Module naming
The main module is by default called `main`. This is just a convention, and you can choose your own name. Other modules
can also be called anything you want, as long as it's a valid php class name.

**Note:** All examples on this page assumes the name of the module is `main`, but this is just an example.

## Module class (optional)
Modules can have a module class where you can put module-specific functionality that is useful for your module, which 
doesn't belong in the controllers / actions themselves. Examples of this can be sending out emails, generating 
documents to save on the server, etc.

**Note:** A module can function just fine without a module class, and it's not required to create one.

You can create a module class by extending the `caspar\core\Module` class, and putting it in the module folder (not 
the `modules/` folder, but the `modules/modulename` folder). The name of the module class must be the same as the 
module, with the first letter being uppercase.

`application/modules/main/Main.php`
```php
<?php

namespace application\modules\main;

class Main extends \caspar\core\Module
{
}
```
*Example module class*

An instance of this class can then be retrieved using `\caspar\core\Caspar::getModule('main')`. This class is *not*
the same as the controller classes, which are not directly retrievable, but are automatically instantiated and 
triggered by Caspar's routing system.

## Controllers / actions
Each module has one or more [controllers](controllers.md). These are placed in `application/modules/main/controllers`

`application/modules/main/controllers/Users.php`
```php
<?php

namespace application\modules\main\controllers;

class Users extends \caspar\core\Controller
{
  /**
   * @Route(url="/users/:user_id")
   */
  public function runIndex(Request $request)
  {
    $user_id = $request['user_id'];
    $this->user = Users::getTable()->selectById($user_id);
  }
}
```
*Example controller*

## Templates
All module [templates](templates.md) are placed in the `templates` directory under the module, with the template name matching their 
corresponding action.

Given a controller with a `runIndex()` action and a `runUser()` action, the `templates` folder for the `main` module
would look like this:
```
- application
  - main
    - templates
        index.html.php
        user.html.php
```
*Example directory structure*
