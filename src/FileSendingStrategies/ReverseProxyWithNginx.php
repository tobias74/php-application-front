<?php
namespace PhpApplicationFront\FileSendingStrategies;

class ReverseProxyWithNginx
{
  public function sendFile($uri)
  {
    $this->reverseProxy($uri);  
  }
    
    
  protected function reverseProxy($uri)
  {
    $urlParts = parse_url($uri);
    if (isset($urlParts['port']))
    {
      $host = $urlParts['host'].':'.$urlParts['port'];
    }
    else
    {
      $host = $urlParts['host'];
    }
    
    if (isset($urlParts['query']))
    {
      $query = '?'.$urlParts['query'];
    }
    else
    {
      $query = '';
    }

    $headerString = 'X-Accel-Redirect: /stream_from_s3/'.$urlParts['scheme'].'/'.$host.$urlParts['path'].$query;
    
    header($headerString);
  }
    
}
