[Back to Contents](README.md)

# Requests
Incoming requests are handled by Caspar, and passed to your controller actions automatically in a `$request` parameter. 
Caspar provides a `Request` class which contains all the information from the incoming request.

## Url parameters
Named url parameters and query parameters are available from the request object using either `getParameter()`, or the
simpler array access:
```php
<?php

class Main extends \caspar\core\Controller
{
  /**
   * @Route(url="/users/:user_id")
   */
  public function runIndex(Request $request)
  {
    // Retrieving the user_id via array access
    $user_id = $request['user_id'];
    
    // Retrieving a named request parameter with a fallback value
    $name = $request->getParameter('name', 'Unknown');
  }
}
```
*Example action retrieving parameters from the request*

### Form submissions
When you submit a form, the form fields are handled in the same way as the named parameters above. The request 
parameters match the form field `name`s. If you have `<input type="text" name="age">`, you can retrieve the submitted
value using `$request['age']` or `$request->getParameter('age')`.

## Checking if a request parameter exists
You can check if a request parameter exists by either calling `isset()` or using the `$request->hasParameter($name)` 
method:
```php
<?php

class Main extends \caspar\core\Controller
{
  /**
   * @Route(url="/users/:user_id", methods="POST")
   */
  public function runIndex(Request $request)
  {
    $user_id = $request['user_id'];
    $user = Users::getTable()->selectById($user_id);

    if ($request->hasParameter('name')) {
      $user->setName($request->getParameter('name');
    }
  }
}
```
*Example action only setting the name if one is provided*

## Checking the request method
You can check which type of request is coming in using `$request->getMethod()` or the shortcut methods for checking 
individual method types, `$request->isPost()` and `$request->isAjaxCall()`.

## Cookies
Cookies can be retrieved from the request using `$request->getCookie($name)`. This method wraps the PHP `$_COOKIE` 
variable, and contains all cookies from the incoming request.

### Setting cookies
Cookies are not set via the `$request`, but via the [response](responses.md). A cookie is not available until the first request 
*after* it has been sent to the user.  