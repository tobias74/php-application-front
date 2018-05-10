<?php
namespace PhpApplicationFront;

trait SessionTrait
{

  protected $sessionFacade;
  protected $userSession;
  
  protected function getSessionFacade()
  {
    if (!$this->sessionFacade)
    {
      $user = $this->getUserSession()->getLoggedInUser();
      $this->sessionFacade = $this->getSessionFacadeProvider()->provide( $user );
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
  
}
