[Back to Contents](README.md)

# Controllers
MVC frameworks structure your application in [models](models.md), [views](templates.md) and controllers. Controllers
handles passing information from the request to your applications and models, and passes information back to the user
through templates (read more about the MVC pattern in [MVC explained](howtos/mvc-explained.md)).

Controllers are classes in your application extending the `caspar\core\Controller` class. They are grouped in 
[modules](modules.md) and are found in the `controller` directory under each module.

You can have as many controllers as you like in your application, and each module can have several controllers.

Controller objects and methods have a lot of magic happening behind the scenes. Don't expect them to behave like 
normal objects, they don't always behave like "regular" PHP objects.

## Actions
Each controller has one or more actions, which represents an entry-point into your application. The action is linked to
an url via the [routing](routing.md), using `@Route` annotations.

When Caspar triggers your action, it passes the incoming [request](requests.md) to the action in the `$request` 
parameter. Your action method signature is always the same:
```php
namespace application\modules\main\controllers;

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
*Example controller*

### Action output / return values
The action should never return anything when used together with templates. If the action returns values, this 
will replace the use of the template. This can, however, be useful when working with ajax requests, where you would
want to return either text or simple JSON responses.

Caspar controllers have a helper method to return JSON-formatted output:  
```php
namespace application\modules\main\controllers;

class Main extends \caspar\core\Controller
{
  /**
   * @Route(url="/")
   */
  public function runIndex(Request $request)
  {
    return $this->renderJSON(['message' => 'Hello world']);
  }
}
```
*Example controller returning a json response*

The same holds for returning plain text:  
```php
namespace application\modules\main\controllers;

class Main extends \caspar\core\Controller
{
  /**
   * @Route(url="/")
   */
  public function runIndex(Request $request)
  {
    return $this->renderText('Hello world');
  }
}
```
*Example controller returning a simple text response*

When using these methods, you also don't need a corresponding template. Apart from these examples, your actions should 
never output (`echo` or `print()`) or return values.

### Passing information to templates
One of the main purposes of a controller is to pass information from the action to the template. To pass information
to the template, simply assign it to a property on the object (it must not be an existing property, it must not be 
defined in the object). This will make it available in the template with the same variable name as the property.

```php
namespace application\modules\main\controllers;

class Main extends \caspar\core\Controller
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
*Example controller making a `$user` variable available in its template*
 
Any information can be passed like this from the controller to the template.

### Passing information between requests
Sometimes you want to pass information between requests. As requests are individual, you should usually always pass 
information via the request using parameters or forms. However, sometimes you will want to pass information without
it coming via request parameters or form submissions.

To achieve this, use the `caspar\core\Caspar::setMessage($key, $value)` and `caspar\core\Caspar::getMessage($key)` 
methods. This passes information using `$_SESSION` variables, instead, but without you having to worry about how.

Information stored using `caspar\core\Caspar::setMessage()` is available until you either remove it explicitly using
`caspar\core\Caspar::clearMessage($key)` or `caspar\core\Caspar::getMessageAndClear($key)`.  
```php
namespace application\modules\main\controllers;

class Main extends \caspar\core\Controller
{
  /**
   * @Route(name="postuser", url="/users/:user_id", methods="POST")
   */
  public function runIndex(Request $request)
  {
    $user_id = $request['user_id'];
    $user = Users::getTable()->selectById($user_id);
    $user->setName($request['name']);
    $user->save();
    
    \caspar\core\Caspar::setMessage('user_saved', true);
    
    $this->redirect($this->getRouting()->generate('main_getuser', ['user_id' => $user_id]));
  }

  /**
   * @Route(name="getuser", url="/users/:user_id", methods="GET")
   */
  public function runIndex(Request $request)
  {
    $user_id = $request['user_id'];
    $this->user = Users::getTable()->selectById($user_id);
    
    if (caspar\core\Caspar::getMessageAndClear('user_saved') === true) {
      $this->user_saved = true;
    }
  }
}
```
*Example controller passing information between requests using `setMessage()` and `getMessage()`*

In the example above, the `POST` action is storing a `user_saved` message and immediately forwarding to the `getinfo` 
action. This action checks to see if a `user_saved` message is stored, and then makes a `$user_saved` variable 
available in its template when the user has just been saved. The template can then decide to show an information block
telling the user that the information was just saved, if this variable is set.

## Retrieving information from the request
You can learn more about retrieving information from the request, such as url parameters, form information and more
in [requests](requests.md).
