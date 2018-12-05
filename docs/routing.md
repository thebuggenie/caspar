[Back to Contents](README.md)

# Routing
The routing component of Caspar connects an incoming request (https://mysite.local/about) to application logic and a 
template in your application.

## Defining routes
Routes are defined by annotating methods in your controllers. Controllers must be located in the
`modules/<modulename>/controllers` directory, and must extend the `caspar/core/Controllers` class.

Any method that begins with `run` can be defined as a controller action by annotating it with the
`@Route` annotation. Below is an example of a simple route:
```php
<?php

class Main extends \caspar\core\Controller
{
  /**
   * @Route(url="/")
   */
  public function runIndex(Request $request)
  {
  }
}
```
*Example controller with a single route*

The above method has the minimum annotation required for Caspar to pick it up as a route. As long as the
`url` property is defined in the `@Route` annotation, Caspar will default the rest.

When any urls are accessed, Caspar will automatically match it to the correct action, and use the template 
defined for the action. In the example above, Caspar will run the method `runIndex()` and present the 
`index.html.php` template. The template for the action must match the name of the method, without `run`.

### Named routes
Every route has a name. You can specify the name manually using a `name` property on the `@Route` annotation, but 
if no `name` property is defined, the route name will be `modulename_actionname`, without the `run` part. 

You can specify a name for the route using the `name` property of the `@Route` annotation like this:
```php
<?php

class Main extends \caspar\core\Controller
{
  /**
   * @Route(name="home", url="/")
   */
  public function runIndex(Request $request)
  {
  }
}
```
*Example route with a `name` property*

In the example above, the route name is `main_home`, not `main_index`, as the `@Route` annotation defines the name.

**Note:** the name of the route does not affect the name of the template. The name of the template is always the
same name as the action, without `run`, regardless of the route name.

### Route parameters
Sometimes you want to pass parameters from the urls to your controller actions. To do this, use placeholders
in your route urls, formatted like `:placeholder`.

Named placeholders will be available from the `$request` object passed to all actions:
```php
<?php

  /**
   * @Route(name="user_info", url="/users/:user_id")
   */
  public function runIndex(Request $request)
  {
    $user_id = $request->getParameter('user_id');
  }
```
*Example action retrieving the `user_id` from the `$request` object*

You can define as many named parameters as you want, and they will automatically be handled by Caspar.

## Generating urls
As routes sometimes change structure, it's not useful to have to type them out in your templates or other
places where you want to generate links or urls. Caspar has convenience methods to generate urls, which can 
be used in templates, or other places in your code.

### In templates
The method `make_url()` generates urls based on the defined routes. Given a route named `home`, you can generate
the url to that route by calling `make_url('home')`.

### Outside templates
The `make_url()` method is a shortcut to `\caspar\core\Caspar::getRouting()->generate()`. Outside templates,
you can call `\caspar\core\Caspar::getRouting()->generate()` directly. It takes the same parameters as `make_url()`.

### Named parameters / placeholders
When generating urls, you sometimes need to pass parameters. The `make_url()` method and 
`\caspar\core\Caspar::getRouting()->generate()` takes a second parameter which is an array of `key => value` pairs
where the array keys match the named parameters in the route definitions, and the values are the values
that goes in the urls.

Given the `user_info` route example above, you can generate a link to user 1 by calling:
```php
<?php

echo make_url('user_info', ['user_id' => 1]);
// Outputs: "/users/1"
```

## Organizing routes
You can split your routes across different modules, and different controllers inside the modules.
If all the routes inside a controller has the same url patterns or the same name patterns, you can group the 
routes by adding a `@Routes` annotation on the class. This lets you avoid repeating parts of the url:
```php
<?php

/**
 * @Routes(name_prefix="admin_", url_prefix="/admin")
 */
class Main extends \caspar\core\Controller
{
  /**
   * @Route(name="landing", url="/")
   */
  public function runIndex(Request $request)
  {
  }

  /**
   * @Route(name="users", url="/users")
   */
  public function runConfigureUsers(Request $request)
  {
  }
}
```
*Example class with a `@Routes` annotation*

Given the example above, the `runIndex` route will be named `admin_landing`, instead of `main_landing`. This is the 
name you would use to generate the url using `make_url()`. The second route will be named `admin_users`.

Both urls will be prefixed with `/admin`, meaning the urls will be:
* https://myapp.local/admin
* https://myall.local/admin/users

**Note:** Caspar does not generate a trailing slash on urls, and matches routes with a trailing slash
if the trailing slash is missing.

## Advanved routing features

### Automatic CSRF protection
Some urls should be protected against CSRF attack by using a CSRF token. Caspar has built-in support for CSRF protected
routes, which can be enabled by adding a `@CsrfProtected` annotation:
```php
<?php

  /**
   * @Route(name="users", url="/users")
   * @CsrfProtected
   */
  public function runConfigureUsers(Request $request)
  {
  }
```
*Example of an action with CSRF protection enabled*

You don't have to do anything special to generate or validate the CSRF token. Given the annotation above, the url will
get a `csrf_token` parameter appended automatically, and Caspar will validate it and throw a `CSRFFailureException`
if it doesn't match.

### Restricting routes to http methods
If you want to restrict an action to only be valid for certain http methods, you can specify that with the `methods`
property on the `@Route` annotation:
```php
<?php

  /**
   * @Route(name="users", url="/users", methods="GET,POST")
   */
  public function runConfigureUsers(Request $request)
  {
  }
```
*Example of a route that only matches `GET` and `POST` requests*

If no `methods` property is provided, the route will be valid for any http method. If the `methods` property exists,
the http method must match what is inside the `methods` property, or the route will not be found.
 