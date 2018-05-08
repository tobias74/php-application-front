<?php
namespace PhpApplicationFront\FileSendingStrategies;

class PhpNativeServing
{
  public function sendFile($uri)
  {
    $this->serveFileResumable($uri);  
  }
    
  protected function serveFileResumable ($uri) {

    // Make sure the files exists, otherwise we are wasting our time
    if (!file_exists($file)) {
      header("HTTP/1.1 404 Not Found");
      exit;
    }

    $fileTime = filemtime($file);

    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
    {
      error_log('we did get the http if modiefed...');
      if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $fileTime)
      {
        error_log('and we answered, not modified');
        header('HTTP/1.0 304 Not Modified');
        exit;
      }
      else
      {
        error_log('and we answered, yes modified, continue loading.');
      }
    }  







    // Get the 'Range' header if one was sent
    if (isset($_SERVER['HTTP_RANGE'])) 
    {
      $range = $_SERVER['HTTP_RANGE']; // IIS/Some Apache versions
    }
    else 
    {
      $range = FALSE; // We can't get the header/there isn't one set
    }

    // Get the data range requested (if any)
    $filesize = filesize($file);
    if ($range) {
      $partial = true;
      list($param,$range) = explode('=',$range);
      if (strtolower(trim($param)) != 'bytes') { // Bad request - range unit is not 'bytes'
        header("HTTP/1.1 400 Invalid Request");
        exit;
      }
      $range = explode(',',$range);
      $range = explode('-',$range[0]); // We only deal with the first requested range
      if (count($range) != 2) { // Bad request - 'bytes' parameter is not valid
        header("HTTP/1.1 400 Invalid Request");
        exit;
      }
      if ($range[0] === '') { // First number missing, return last $range[1] bytes
        $end = $filesize - 1;
        $start = $end - intval($range[0]);
      } else if ($range[1] === '') { // Second number missing, return from byte $range[0] to end
        $start = intval($range[0]);
        $end = $filesize - 1;
      } else { // Both numbers present, return specific range
        $start = intval($range[0]);
        $end = intval($range[1]);
        if ($end >= $filesize || (!$start && (!$end || $end == ($filesize - 1)))) $partial = false; // Invalid range/whole file specified, return whole file
      }
      $length = $end - $start + 1;
    } else $partial = false; // No range requested

    header('Accept-Ranges: bytes');

    // if requested, send extra headers and part of file...
    if ($partial) {
      header('Content-Length: '.$length);
      header('HTTP/1.1 206 Partial Content');
      header("Content-Range: bytes $start-$end/$filesize");
      if (!$fp = fopen($file, 'r')) { // Error out if we can't read the file
        header("HTTP/1.1 500 Internal Server Error");
        exit;
      }
      if ($start) fseek($fp,$start);
      while ($length) { // Read in blocks of 8KB so we don't chew up memory on the server
        $read = ($length > 8192) ? 8192 : $length;
        $length -= $read;
        print(fread($fp,$read));
      }
      fclose($fp);
    } else 
    {
      header('Content-Length: '.$filesize);
      readfile($file); // ...otherwise just send the whole file
    }

    // Exit here to avoid accidentally sending extra content on the end of the file
    exit;

  }

}
