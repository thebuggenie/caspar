# Components
Components are reusable templates, as simple as that. A component consists of:
* *the template* - the template contains the markup / text that is included
* *component logic [Optional]* - component logic that can do things like looking up related information

## The template
Component templates are placed in the `components` directory in the module directory. Below is an example of a `user`
component in the `main` module:

`application/modules/main/components/user.php`
```php
<li class="user">
  <h1><?= $user->getName(); ?></h1>
</li>
``` 

This component can be included in any template using the `include_component()` method:

`application/modules/main/templates/users.php`
```php
<ul>
  <?php foreach ($users as $user): ?>
    <?php include_component('main/user', ['user' => $user]); ?>
  <?php endforeach; ?>
</ul>
```

## Component logic
Some components will have logic of their own - maybe you want to look up information before rendering the component.
To add application logic to your `user` component, create a method called `componentUser()` in the module's 
`Components` class (see the default app skeleton for example).

All parameters passed to the component using `include_component()` or `get_component_html()` will be passed to the 
component method as properties on the component object:

`application/modules/main/Components.php`
```php
<?php

namespace application\modules\main;

/**
 * Components for the main module
 */
class Components extends \caspar\core\Components
{
  public function componentUser()
  {
    $this->user_id = $this->user->getId();
  }
}
``` 

The component can pass additional information to the component template by assigning them as properties on the object,
just as with regular templates. These are available in the component template with the same variable names as the 
properties:

`application/modules/main/templates/users.php`
```php
<li class="user" id="<?= $user_id; ?>">
  <h1><?= $user->getName(); ?></h1>
</li>
```
