<?php
namespace Album;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

/*
This is the ALBUM MODULE
In order to load and configure a module, Zend Framework provides a ModuleManager. This will look for a Module class in the specified module namespace (i.e., Album); in the case of our new module, that means the class Album\Module, which will be found in module/Album/src/Module.php. *this file
ModuleManager will call ALL METHODS in this class automatically for us
*/
class Module implements ConfigProviderInterface
{
  public function getConfig()
  {
      return include __DIR__ . '/../config/module.config.php';
  }

  /*
  This method returns an array of factories that are all merged together by the ModuleManager before passing them to the ServiceManager. The factory for Album\Model\AlbumTable uses the ServiceManager to create an Album\Model\AlbumTableGateway service representing a TableGateway to pass to its constructor. We also tell the ServiceManager that the AlbumTableGateway service is created by fetching a Zend\Db\Adapter\AdapterInterface implementation (also from the ServiceManager) and using it to create a TableGateway object. The TableGateway is told to use an Album object whenever it creates a new result row. The TableGateway classes use the prototype pattern for creation of result sets and entities. This means that instead of instantiating when required, the system clones a previously instantiated object.

  Factories

  The code below demonstrates building factories as closures within your module class. Another option is to build the factory as a class, and then map the class in your module configuration. This approach has a number of benefits:
      The code is not parsed or executed unless the factory is invoked.
      You can easily unit test the factory to ensure it does what it should.
      You can extend the factory if desired.
      You can re-use the factory across multiple instances that have related construction.
  See: https://zendframework.github.io/zend-servicemanager/configuring-the-service-manager/#factories
  */
  public function getServiceConfig()
  {
      return [
          'factories' => [
              Model\AlbumTable::class => function($container) {
                  $tableGateway = $container->get(Model\AlbumTableGateway::class);
                  return new Model\AlbumTable($tableGateway);
              },
              Model\AlbumTableGateway::class => function ($container) {
                  $dbAdapter = $container->get(AdapterInterface::class);
                  $resultSetPrototype = new ResultSet();
                  $resultSetPrototype->setArrayObjectPrototype(new Model\Album());
                  return new TableGateway('album', $dbAdapter, null, $resultSetPrototype);
              },
          ],
      ];
  }

  /*
  Because we're now defining our own factory, we can modify our module.config.php to
  remove the definition. Open module/Album/config/module.config.php and remove the following lines:
  'controllers' => [
        'factories' => [
            Controller\AlbumController::class => InvokableFactory::class,
        ],
    ],
  */
  public function getControllerConfig()
  {
      return [
          'factories' => [
              Controller\AlbumController::class => function($container) {
                  return new Controller\AlbumController(
                      $container->get(Model\AlbumTable::class)
                  );
              },
          ],
      ];
  }
}
