<?php
namespace PhpApplicationFront;

class MediaHelper
{
  use GetSetTrait;
  
  public function getImageSpec($sizeName)
  {
    $flySpec = new \PhpMediaCache\FlyImageSpecification();
    
    switch ($sizeName)
    {
      case "small": 
        $flySpec->width=100;
        $flySpec->height=100;
        break;
        
      case "medium": 
        $flySpec->width=300;
        $flySpec->height=300;
        break;
        
      case "big": 
        $flySpec->width=800;
        $flySpec->height=800;
        break;

      case "very_big": 
        $flySpec->width=1400;
        $flySpec->height=1400;
        break;
        
      case "original":
        $flySpec->width=false;
        $flySpec->height=false;
        break;
    }
    
    return $flySpec;
  }

  public function getVideoSpec($format, $quality)
  {
    $flySpec = new \PhpMediaCache\FlyVideoSpecification();
    $flySpec->format = $format;
    $flySpec->quality = $quality;
    return $flySpec;
  }
    
    
  public function requestTranscoding($id)
  {
    $videoUrl = $this->getFileService()->getExternalUri($id);

    $pairs = [
      [
        'quality' => 'medium',
        'format' => 'webm'
      ],
      [
        'quality' => 'medium',
        'format' => 'ogg'
      ],
      [
        'quality' => 'medium',
        'format' => 'mp4'
      ],
      [
        'quality' => 'medium',
        'format' => 'jpg'
      ]
    ];
    
    foreach ($pairs as $pair)
    {
      $spec = $this->getVideoSpec( $pair['format'], $pair['quality'] );
      $values = $this->getMediaCacheService()->getCachedVideo($videoUrl, $id, $spec);
    }
  }
    
    
}

