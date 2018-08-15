<?php
namespace PhpApplicationFront;

abstract class AbstractActionController extends \PhpSmallFront\AbstractActionController
{
  protected $startTime = false;
  protected $userSession;
  
  use GetSetTrait, SessionTrait; 


  protected function startTimer()
  {
    $this->startTime = microtime(true);
  }
  
  protected function reportTimer()
  {
    if ($this->issetProfiler())
    {
      header('ZEITFADEN-TIRO: '.json_encode($this->getProfiler()->getHash()));
    }

    if ($this->startTime)
    {
      $endTime = microtime(true);
      $duration = $endTime-$this->startTime;
      header('ZEITFADEN-TIMER: '.$duration);    
    }
  }



  protected function render($templateName, $data)
  {
    if ($this->getLoggedInUserId())
    {
      $loggedInUser = $this->getUserById( $this->getLoggedInUserId() );
    }
    else
    {
      $loggedInUser = false;
    }
    
    $additionalData = array(
      'currentQuery' => $this->getCurrentQueryString(),
      'params' => $this->_routeParameters,
      'loggedInUser' => $loggedInUser,
      'loggedInUserId' => $this->getLoggedInUserId(),
      'isUserLoggedIn' => !!$this->getLoggedInUserId(),
      'googleApiKey' => $this->getGoogleApiKey(),
      'auth0ClientId' => $this->getAuth0ClientId(),
      'auth0Domain' => $this->getAuth0Domain(),
      'auth0Callback' => $this->getAuth0Callback(),
      'groupId' => $this->getParam('groupId'),
      'user' => $this->getAuth0()->getUser()  
    );
    $data = array_merge($data,$additionalData);

    return parent::render($templateName, $data);
  }

  protected function needsLoggedInUser()
  {
    if (!$this->isUserLoggedIn())
    {
      header('HTTP/1.0 403 forbidden',true,403);
      echo json_encode(array(
        'status' => 'need_login'  
      ));
      die();
    }
  }



  protected function ensureRequestOwner()
  {
    if ($this->hasParam('ensureOwnerId'))
    {
      if ($this->getUserSession()->isUserLoggedIn())
      {
        if ($this->getParam('ensureOwnerId','') !== $this->getLoggedInUserId())
        {
          header('HTTP/1.0 403 forbidden',true,403);
          echo json_encode(array(
            'status' => 'wrong_user'  
          ));
          die();
        }
      }
      else
      {
        $this->needsLoggedInUser();
      }
    }
    else
    {
      // ensuring not requested
    }

  }





}
