<?php

class drI18nExtractRoutingTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('culture', sfCommandArgument::REQUIRED, 'The target culture'),
    ));  
    
    $this->addOptions(array(
      new sfCommandOption('catalogue', null, sfCommandOption::PARAMETER_OPTIONAL, 'The name of the I18n messages catalogue', null),
      new sfCommandOption('display-new', null, sfCommandOption::PARAMETER_NONE, 'Output all new found strings'),
      new sfCommandOption('display-old', null, sfCommandOption::PARAMETER_NONE, 'Output all old strings'),
      new sfCommandOption('auto-save', null, sfCommandOption::PARAMETER_NONE, 'Save the new strings'),
      new sfCommandOption('auto-delete', null, sfCommandOption::PARAMETER_NONE, 'Delete old strings'),
    ));

    $this->namespace = 'i18n';
    $this->name = 'extract-routing';
    $this->briefDescription = 'Extracts strings from the routing ';
    $this->detailedDescription = 'The [i18n:extract|INFO] task extracts i18n strings from your routing
for the given application and target culture:

  [./symfony i18n:extract frontend fr|INFO]

By default, the task only displays the number of new and old strings
it found in the current project.

If you want to display the new strings, use the [--display-new|COMMENT] option:

  [./symfony i18n:extract --display-new frontend fr|INFO]

To save them in the i18n message catalogue, use the [--auto-save|COMMENT] option:

  [./symfony i18n:extract --auto-save frontend fr|INFO]

If you want to display strings that are present in the i18n messages
catalogue but are not found in the application, use the 
[--display-old|COMMENT] option:

  [./symfony i18n:extract --display-old frontend fr|INFO]

To automatically delete old strings, use the [--auto-delete|COMMENT] but
be careful, especially if you have translations for plugins as they will
appear as old strings but they are not:

  [./symfony i18n:extract --auto-delete frontend fr|INFO]
  
By default, the extractor uses the ``routing`` catalogue, which is used by the zxI18nRoutingPlugin plugin.
In case you wish to save the messages to another catalogue, use the [--catalogue|COMMENT] option:

  [./symfony i18n:extract --auto-save frontend fr --catalogue=messages|INFO]
    ';
  }
  
  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->logSection('i18n', sprintf('extracting i18n strings from the "%s" routing', $arguments['application']));

    // get i18n configuration from factories.yml
    $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));

    $class = $config['i18n']['class'];
    $params = $config['i18n']['param'];
    unset($params['cache']);
    
    if (!$options['catalogue'])
    {
      $options['catalogue'] = 'routing';
    }
    
    $params = array_merge($config['routing']['param'], array('load_configuration' => false, 'logging' => false));

    $config = new sfRoutingConfigHandler();
    $routes = $config->evaluate($this->configuration->getConfigPaths('config/routing.yml'));

    $routing = new sfPatternRouting($this->dispatcher, null, $params);
    $routing->setRoutes($routes);

    $this->dispatcher->notify(new sfEvent($routing, 'routing.load_configuration'));

    $routes = $routing->getRoutes();

    $extract = new drI18nRoutingExtract(new $class($this->configuration, new sfNoCache(), $params), $arguments['culture'], array(
      'catalogue' => $options['catalogue'],
      'routes' => $routes));
      
    $extract->extract();

    $this->logSection('i18n', sprintf('found "%d" new i18n strings', count($extract->getNewMessages())));
    
    $this->logSection('i18n', sprintf('found "%d" old i18n strings', count($extract->getOldMessages())));

    if ($options['display-new'])
    {
      $this->logSection('i18n', sprintf('display new i18n strings', count($extract->getOldMessages())));
      foreach ($extract->getNewMessages() as $message)
      {
        $this->log('               '.$message."\n");
      }
    }

    if ($options['auto-save'])
    {
      $this->logSection('i18n', 'saving new i18n strings');

      $extract->saveNewMessages();
    }

    if ($options['display-old'])
    {
      $this->logSection('i18n', sprintf('display old i18n strings', count($extract->getOldMessages())));
      foreach ($extract->getOldMessages() as $message)
      {
        $this->log('               '.$message."\n");
      }
    }

    if ($options['auto-delete'])
    {
      $this->logSection('i18n', 'deleting old i18n strings');

      $extract->deleteOldMessages();
    }
  }
}
