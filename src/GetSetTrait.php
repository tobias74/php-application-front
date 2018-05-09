<?php
namespace PhpApplicationFront;

trait GetSetTrait
{

  public function __call($name, $parameters)
  {
    if (substr($name,0,3) == "set")
    {
      $memberName = lcfirst(substr($name, 3));
      $memberValue = $parameters[0];
      $this->$memberName = $memberValue;
    }
    elseif (substr($name,0,3) == "get")
    {
      $memberName = lcfirst(substr($name, 3));
      if (isset($this->$memberName))
      {
        return $this->$memberName;
      }
      else 
      {
        throw new \ErrorException("bad coding, this member does not exist: $name in getsetrait.");
      }
    }
    else
    {
      throw new \ErrorException("bad coding, this funciton does not exist: $name in getsetrait.");
    }
  }

  
}
