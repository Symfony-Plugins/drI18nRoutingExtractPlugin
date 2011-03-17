<?php 

/**
 * Extract class for extracting translatable parts from a given set of routes
 *
 */
class drI18nRoutingExtract extends sfI18nExtract
{
  protected $_routes = array();
  protected $_catalogue = null;
  
  public function __construct(sfI18N $i18n, $culture, $parameters = array())
  {
    if (isset($parameters['catalogue']))
    {
      $this->setCatalogue($parameters['catalogue']);
    }
    
    if (isset($parameters['routes']))
    {
      $this->setRoutes($parameters['routes']);
    }
    
    parent::__construct($i18n, $culture, $parameters);
  }
  
  /**
   * Get the current collection of sfRoute objects
   * 
   * @return array A collection of sfRoute objects
   */
  public function getRoutes()
  {
    return $this->_routes;
  }
  
  /**
   * Set an array of sfRoute objects that should be used by the extract method
   * 
   * @param array $routes A collection of sfRoute objects
   */
  public function setRoutes(array $routes)
  {
    $this->_routes = $routes;
  }
  
  /**
   * Set the name of the messages catalogue that should be used
   * 
   * @param string $catalogue
   */
  public function setCatalogue($catalogue)
  {
    $this->_catalogue = $catalogue;
  }
  
  /**
   * Get the name of the messages catalogue that should be used
   */
  public function getCatalogue()
  {
    return $this->_catalogue;
  }
  
  /**
   * Extract translatable strings from the routing
   * 
   * @see sfI18nExtract::extract()
   */
  public function extract()
  {
    $extractor = new drI18nRoutingExtractor($this->getRoutes());
    
    $this->allSeenMessages =  array_unique($extractor->extract());
  }
  
  
  /**
   * Copied from sfI18nExtract::loadCurrentMessages() but allows for a specific catalogue to be used
   * 
   * @see parent::getMessageSource()
   */
  public function getMessageSource()
  {
    $messageSource = $this->i18n->getMessageSource();
    $messageSource->load($this->getCatalogue());
    
    return $messageSource;
  }
  
  /**
   * Copied from sfI18nExtract::loadCurrentMessages() and extended to allow for a specific catalogue to be used
   * 
   * @see sfI18nExtract::loadMessageSources()
   */
  protected function loadMessageSources()
  {
    $this->i18n->getMessageSource()->setCulture($this->culture);
    $this->i18n->getMessageSource()->load($this->getCatalogue());
  }

  /**
   * Copied from sfI18nExtract::loadCurrentMessages() and extended to allow for a specific catalogue to be used
   * 
   * @see sfI18nExtract::loadCurrentMessages()
   */
  protected function loadCurrentMessages()
  {
    $this->currentMessages = array();
    
    $messageSource = $this->getMessageSource();
    
    foreach ($messageSource->read() as $catalogue => $translations)
    {
      foreach ($translations as $key => $values)
      {
        $this->currentMessages[] = $key;
      }
    }
  }
  
  /**
   * Copied from sfI18nExtract::loadCurrentMessages() and extended to allow for a specific catalogue to be used
   * 
   * @see sfI18nExtract::saveNewMessages()
   */
  public function saveNewMessages()
  {
    $messageSource = $this->getMessageSource();
    
    foreach ($this->getNewMessages() as $message)
    {
      $messageSource->append($message);
    }

    $messageSource->save($this->getCatalogue());
  }

  /**
   * Copied from sfI18nExtract::loadCurrentMessages() and extended to allow for a specific catalogue to be used
   * 
   * @see sfI18nExtract::deleteOldMessages()
   */
  public function deleteOldMessages()
  {
    $messageSource = $this->getMessageSource();
    
    foreach ($this->getOldMessages() as $message)
    {
      $messageSource->delete($message, $this->getCatalogue());
    }
  }
}
