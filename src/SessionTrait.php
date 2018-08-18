<?php
namespace PhpApplicationFront;

trait SessionTrait
{

  protected $userSession;
  protected $sessionFacade;

  protected function getSessionFacade()
  {
    if (!$this->sessionFacade)
    {
      $user = $this->getUserSession()->getLoggedInUser();
      $this->sessionFacade = $this->getSessionFacadeProvider()->provide( $user );
    }
    return $this->sessionFacade;
  }
  

  protected function getAuth0()
  {
    return $this->getUserSession()->getAuth0();
  }

  protected function getUserSession() 
  {
    if (!$this->userSession)
    {
      $this->userSession = $this->getUserSessionRecognizer()->recognizeAuthenticatedUser( $this->getSession() );
    }
    return $this->userSession;
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

  protected function isUserLoggedIn()
  {
    return $this->getUserSession()->isUserLoggedIn();
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
  
}
