<?php
namespace PhpApplicationFront;


class SugarloafApplication
{
    public function __construct($config)
    {
        $this->config = $config;
    }


    public function getEnvironment()
    {
        $config = $_ENV;
        
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {
          $config['browserLanguage']='en';
        }
        else {
          $config['browserLanguage'] = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        }

        return $config;
    }

    protected function getDependencyManager()
    {
      $dependencyManager = new \SugarLoaf\DependencyManager();
    
      $dir_iterator = new \RecursiveDirectoryIterator( $this->config['dependencyConfigurationFolder'] );
      $iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);
    
      foreach ($iterator as $file) 
      {
        if (!is_dir($file))
        {
          $configurator = include($file);
          $configurator($dependencyManager, $this->getEnvironment());
        }
      }
    
      return $dependencyManager;
    }
    

    protected function getRouteConfiguration()
    {
      $dir_iterator = new \RecursiveDirectoryIterator( $this->config['routeConfigurationFolder'] );
      $iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);
      
      $routeConfiguration = array();
      
      foreach ($iterator as $file) 
      {
        if (!is_dir($file))
        {
          $jsonData = json_decode(file_get_contents($file), true);
          $routeConfiguration = array_merge($routeConfiguration, $jsonData);
        }
      }
      
      return $routeConfiguration;
    }
    
    
    public function getService($serviceName, $parameters = array())
    {
      return $this->getDependencyManager()->get($serviceName, $parameters);
    }
    
    public function setSessionProvider($val)
    {
      $this->sessionProvider = $val;
    }

    protected function getSession()
    {
        $dependencyManager = $this->getDependencyManager();
        $session = $dependencyManager->get( $this->config['sessionInstanceName'] );
        $session->start();
        return $session;
    }
    
    public function run()
    {
        // at this piont I want to create a session, start it, and pass it into the controller to use it,
        // the controllers should not create their own session, I want to make their context from here
        if (isset($this->config['sessionInstanceName']))
        {
          $session = $this->getSession();
        }
        else
        {
          $session = false;
        }

        $renderer = new \PhpSmallFront\TwigRenderer( $this->config['templateFolder'] );

        $dependencyManager = $this->getDependencyManager();

        $controllerProvider = function($controllerName, $routeParameters) use ($dependencyManager, $renderer, $session) {
            return $dependencyManager->get($controllerName, [$routeParameters, $renderer, $session]);
        };
        
        $application = new \PhpSmallFront\Application(array(
          'controllerProvider' => $controllerProvider,
          'routeConfiguration' => $this->getRouteConfiguration()
        ));
        
        $application->run();
        
    }
    
}