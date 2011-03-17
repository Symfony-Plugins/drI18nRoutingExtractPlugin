<?php 

/**
 * Extractor class for extracting translatable parts of routing patterns (URLs)
 * 
 */
class drI18nRoutingExtractor extends sfI18nExtract
{
  protected $_routes = null;
  
  protected $_strings = array();
  
  /**
   * @param array $routes A collection of sfRoute objects
   */
  public function __construct(array $routes)
  {
    $this->_routes = $routes;
  }
  
  /**
   * Extract translatable parts from the given routes (i.e. tokens with type "text") 
   * 
   * @see sfI18nExtract::extract()
   */
  public function extract()
  {
    $this->_strings = array();
    
    $skip_tokens = array('*');
    
    foreach ($this->_routes as $route)
    {
      /* @var $route sfRoute */
      $pattern = $route->getPattern();
      foreach ($route->getTokens() as $token)
      {
        if ($token[0] == 'text' && !in_array($token[2], $skip_tokens))
        {
          $this->_strings[] = $token[2];
        }
      }
    }
    
    return $this->_strings;
  }
}
