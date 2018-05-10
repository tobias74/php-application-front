<?php
namespace PhpApplicationFront;

class MediaActionController extends AbstractActionController
{

  protected $idParameterName = 'stationId';

    
  public function getImageAction()
  {
    $id = $this->getParam($this->idParameterName, 0);
    $this->needsToBeAllowedToView($id);
    $imageUri = $this->getFileService()->getExternalUri($id);
    $spec = $this->getMediaHelper()->getImageSpec( $this->getParam('imageSize','medium') );
    $cachedImage = $this->getMediaCacheService()->getCachedImage($imageUri, $id, $spec);
    $imageUrl = $this->getMediaCacheService()->getExternalUriForMedia($cachedImage);

    $this->sendFile($imageUrl);    
  }

  public function getVideoAction()
  {
    $id = $this->getParam($this->idParameterName, 0);
    $this->needsToBeAllowedToView($id);
    $videoUrl = $this->getFileService()->getExternalUri($id);

    $flySpec = $this->getMediaHelper()->getVideoSpec( $this->getParam('format','webm'), $this->getParam('quality','medium'));
    $cachedVideo = $this->getMediaCacheService()->getCachedVideo($videoUrl, $id, $flySpec);
    if ($cachedVideo->isScheduled())
    {
      throw new \Exception('Video is scheduled, but not ready yet.');
    }
    else
    {
      $videoUrl = $this->getMediaCacheService()->getExternalUriForMedia($cachedVideo);
      $this->sendFile($videoUrl);    
    }
  }
  
  public function serveAttachmentAction()
  {
    $id = $this->getParam($this->idParameterName, 0);
    $this->needsToBeAllowedToView($id);

    //header('Content-Disposition: inline; filename= '.$entity->getFileName());

    $this->sendFile( $this->getFileService()->getFileUri($id) );
  }
  
  
  protected function sendFile($uri)
  {
      $this->getFileSendingStrategy()->sendFile($uri);
  }

  protected function needsToBeAllowedToView($id)
  {
    if (!$this->getAccessResolver()->isAllowedToView($id))
    {
      throw new \Exception("not allowed to view media");
    }

  }


}

