# Templates
Templates are what users accessing your application sees. They are usually html documents showing information coming
via the controller actions.

There are two different templates, module templates and global templates. All templates are usually `.php` files.

## Global templates
The `templates` directory of your `application` directory contains templates that are used across your entire 
application. 

The default `layout.php` template wraps your content, and the `$content` placeholder in this template
is where your module action templates are shown. This lets you use one template for styling the entire application
so you don't have to repeat the base layout.

## Module templates
Each module places its own templates in the `templates` directory under the module, with the template name matching 
their corresponding action.

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

## Module template variables
Templates can access information passed on from the controller action. In an action, you set the variable on the 
controller object, like this:
```php
<?php

  /**
   * @Route(url="/users/:user_id")
   */
  public function runIndex(Request $request)
  {
    $user_id = $request['user_id'];
    $this->user = Users::getTable()->selectById($user_id);
  }
```

This makes the `$user` variable set like `$this->user` available in the template:

```php
<h1><?= $user->getName(); ?></h1>
```

## Common template variables
In addition to the variables passed from the action, there are some common template variables available to all 
templates:

* `$csp_user` - this is the user object of the current user accessing the application, set via `Caspar::setUser()`
* `$csp_request` - this is the same as the `$request` variable passed to the action
* `$csp_response` - this is the [response](responses.md) object
* `$csp_routing` - this is the routing object

## Template helper functions
Caspar templates can use a range of helper functions to help with constructing templates.

### Translations
All text can be translated using the `__()` translation shortcut function. Read more about how translations work in
[translations](translations.md).

```php
<h1><?= __('List of users'); ?></h1>
```
*Example of a template with a translated header*

### `make_url()`
The `make_url()` method lets you construct urls from your defined routes, easily:

```php
<?= link_tag(make_url('show_user', ['user_id' => $user->getId()]), 'Show user info'); ?>
```
*Example of printing an `<a>` tag using `link_tag()`*

### `link_tag()`
The `link_tag()` method lets you create `<a>` tags and easily pass in information like custom classes:

```php
<ul>
  <li><?= link_tag(make_url('home'), 'Frontpage', ['class' => 'home-link']); ?></li>
  <li><?= link_tag('http://github.com', 'GitHub.com'); ?></li>
</ul>
```
*Example of printing an `<a>` tag using `link_tag()`*

### `image_tag()`
The `image_tag()` method lets you create `<img>` tags and easily pass in information like custom classes:

```php
<?= image_tag('/images/user.png', ['class' => 'user-avatar user-image']); ?>
```
*Example of printing an `<img>` tag using `image_tag()`*

## Including other templates
One of the benefits of using templates is to be able to structure your templates and split them into reusable parts.
For instance, a user template can be used to display user information, and included on any page that needs to display
that information.

Reusable templates are called *components*. You can read more about how to use them in [components](components.md).
