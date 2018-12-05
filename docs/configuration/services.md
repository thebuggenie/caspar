[Back to Contents](../README.md)

# Configuring services
Caspar provides lightweight service auto configuration using the `services` configuration section of `caspar.yml`

## Setting up a service
Any services that relies on being configured through your application can be configured in the `caspar.yml` file.
This also works for libraries that needs to be bootstrapped, where Caspar can bootstrap and autoconfigure these based
on the configuration provided in the `caspar.yml` file.

There are three types of services, and a service configuration can combine any of these three:

### Service configuration
Sometimes you don't need to store the actual service, but need a central location for the service configuration, such
as smtp settings, etc.

When defining a service without the `classname` property, and without `auto_initialize` set to `true`, Caspar does not
invoke any methods when storing the service. You can retrieve the service configuration using the
`\caspar\core\Caspar::getService($service_name)` method.

Below is an example of storing Swiftmailer configuration in the service configuration section of `caspar.yml`:
```yaml
services:
  swift:
    hostname: smtp.domeneshop.no
	port: 587
	username: thebuggenie1
	password: FbuAdd3C
```
*Example swiftmailer configuration*

When you need to use this service, you can retrieve it using `\caspar\core\Caspar::getService('swift');`

### Instanced services
Instanced services, or single-instance libraries can be defined as application services and subsequently be
retrieved in your application using the `\caspar\core\Caspar::getService($service_name)` method. 

Below is an example of retrieving a Monolog logger instance using a service defined in `caspar.yml`:
```yaml
services:
  my_logger:
    classname: \Monolog\Logger
    arguments:
      - 'my_logger'
```
*Example Monolog service*

The logger object can then be retrieved anywhere in your application using 
`\caspar\core\Caspar::getService('my_logger');`

### Auto-configured services
Auto-configured services are libraries that needs to be bootstrapped before being used, but are otherwise retrieved
on their own (or combined with an instanced service).

To auto-configure a service, define it in `caspar.yml` using the below example as a template:
```yaml
services:
  b2db:
    auto_initialize: true
    callback: [\b2db\Core, 'initialize']
    arguments:
      -
        driver: mysql
        hostname: localhost
        username: root
        password: password
        database: myapp
        debug: false
        tableprefix: myapp_
      - [\caspar\core\Caspar, getCache]
```
*Example of auto-configuring the `b2db` ORM so it can be used anywhere*

The above service definition makes Caspar bootstrap the library using the callback `\b2db\Core::initialize()`, passing 
the two arguments defined in `arguments` as parameters to the `initialize()` method.

**Note:** arguments defined in the `arguments` configuration are passed expanded using the php `call_user_func_array()`
method, like this: `call_user_func_array($callback, $arguments);`
