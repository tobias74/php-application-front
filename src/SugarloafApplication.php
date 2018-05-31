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
    
    
    public function run()
    {
        session_start();

        $renderer = new \PhpSmallFront\TwigRenderer( $this->config['templateFolder'] );
        
        $dependencyManager = $this->getDependencyManager();
        
        $controllerProvider = function($controllerName, $routeParameters) use ($dependencyManager, $renderer) {
            return $dependencyManager->get($controllerName, [$routeParameters, $renderer]);
        };
        
        $application = new \PhpSmallFront\Application(array(
          'controllerProvider' => $controllerProvider,
          'routeConfiguration' => $this->config['routeConfiguration']
        ));
        
        $application->run();
        
    }
    
}