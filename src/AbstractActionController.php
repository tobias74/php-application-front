<?php
namespace PhpApplicationFront;

abstract class AbstractActionController extends \PhpSmallFront\AbstractActionController
{
  use GetSetTrait; 
  
  protected $userSession;
  protected $sessionFacade;


  protected function getSessionFacade()
  {
    if (!$this->sessionFacade)
    {
      $this->sessionFacade = $this->getSessionFacadeProvider()->provide( $this->getLoggedInUser() );
    }
    return $this->sessionFacade;
  }

  protected function getUserSession() 
  {
    if (!$this->userSession)
    {
      $this->userSession = $this->getUserSessionRecognizer()->recognizeAuthenticatedUser();
    }
    return $this->userSession;
  }

  protected function getAuth0()
  {
    return $this->getUserSession()->getAuth0();
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

  public function getLoggedInUserId()
  {
    $userId = $this->getUserSession()->getLoggedInUserId();
    if ($userId === '')
    {
      $userId = false;
    }

    return $userId;
  }


  protected function getLoggedInUser()
  {
    $userId = $this->getUserSession()->getLoggedInUserId();
    return $this->getUserById($userId);
  }

  public function getUserById($userId)
  {
    return $this->getUserSessionRecognizer()->getUserById($userId);
  }

  public function getUsersByIds($userIds) 
  {
    $allUsers = array();
    foreach ($userIds as $userId) {
      $allUsers[] = $this->getUserById($userId);
    }
    return $allUsers;
  }

  public function getDisplayNameByUserId($userId)
  {
    try
    {
      $user = $this->getUserById($userId);
      return $user->displayName;
    }
    catch (\PhpCrudMongo\NoMatchException $e)
    {
      return "unknown user";
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


  protected function isUserLoggedIn()
  {
    return $this->getUserSession()->isUserLoggedIn();
  }



}
