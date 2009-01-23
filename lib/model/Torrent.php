<?php

/**
 * Subclass for representing a row from the 'torrent' table.
 *
 * 
 *
 * @package lib.model
 */

sfLoader::loadHelpers(Array('Asset','Url'));

class Torrent extends BaseTorrent
{

    function __construct($file=null) // todo -- abstract the validatedfile stuff out
    {
        if($file==null){return;}

        if(! $file instanceof sfValidatedFile) throw new sfException('constructor takes instance of sfValidatedFile');

        $filename = $file->getOriginalName();
        $extension = $file->getOriginalExtension();
        $this->setFileName($filename);
        $file->save(sfConfig::get('sf_upload_dir').'/'.$filename,0644); // todo abstract filemode out
                    
        $torrent_file=$this->getTorrentPath();

        if(file_exists($torrent_file))
        {
            $this->cleanupFiles();
            throw new sfException("$torrent_file already exists");
        }

        $this->setSize(filesize($file->getSavedName()));

        /*$fi = new finfo(FILEINFO_MIME);
        $mime_type = $fi->file($file->getSavedName());
        $fi->close();*/
        //$mime_type=mime_content_type($file->getSavedName());
        $mime_content_type='text/plain'; // just to have something fixme
        $this->setMimeType($file->getType());
        

        $MakeTorrent = new File_Bittorrent2_MakeTorrent($file->getSavedName());
        $MakeTorrent->setAnnounce(url_for('client/announce',true));
        $MakeTorrent->setComment('TODO');
        $MakeTorrent->setPieceLength(256); // KiB
        $meta = $MakeTorrent->buildTorrent();
        $this->setFileSha1(sha1_file($file->getSavedName())); // todo: perhaps we should add profiling code?
        if($meta && file_put_contents($torrent_file,$meta))
        {
            $File_Bittorrent2_Decode = new File_Bittorrent2_Decode();
            $info=$File_Bittorrent2_Decode->decodeFile($torrent_file);

            $this->setInfoHash($info['info_hash']);
            if(!file_exists($torrent_file))
            {
                $this->cleanupFiles();
                throw new sfException("$torrent_file not written");
            }
        }
        else
        {
            $this->cleanupFiles();
            throw new sfException('Unable to generate torrent');
        }
    }

    public function getUrl($torrent=true) // for convenience for now, fixme
    {
        return $this->getUri($torrent);
    }
    public function getUri($torrent=true)
    {
        if(isset($this->enclosure))
            return $this->enclosure->getUrl();
        return _compute_public_path($this->getFileName().($torrent?'.torrent':''),'uploads','',true);
    }

    public function getMagnet()
    {
      // TODO: add web sources to magnets
      // TODO: blocked on http://trac.symfony-project.org/ticket/4624
        return 'magnet:?xt=urn:sha1:'.$this->getFileSha1().
        '&dn='.urlencode($this->getFileName());
    }


    public function getFeedEnclosure()
    {
      if(!isset($this->enclosure))
        return $this->setFeedEnclosure();
      else
        return $this->enclosure;
    }



    public function setFeedEnclosure($enclosure=null)
    {
        if($enclosure instanceof FeedEnclosure)
        {
            $this->enclosure=$enclosure;
            return $enclosure;
        }

        $type=($enclosure==null?'web':$enclosure);

        $params=Array();

        switch($type)
        {
            case 'web':
                $params['url']=$this->getUri(false);
                $params['length']=$this->getFileSize();
                $params['mimeType']=$this->getMimeType();
                break;
            case 'magnet':
                $params['url']=$this->getMagnet();
                $params['length']=$this->getFileSize();
                $params['mimeType']=$this->getMimeType();
                break;
            case 'torrent':
                $params['url']=$this->getUri(true);
                $params['length']=filesize($this->getTorrentPath());
                $params['mimeType']='application/x-bittorrent';
                break;
            default:
                throw new sfException("Unsupported enclosure type $type");
        }

        $this->enclosure =new sfFeedEnclosure();
        return $this->enclosure->initialize($params);

    }

    public function getGuid() //fixme this isn't migration to real feed guid
    {
        $components = Array();
        if($podcast=$this->getPodcast())
        {
            $components[]=$podcast->getGuid();
            // insert guid from feed here
        }
        else
        {
            $components[]=$this->getInfoHash();
        }
        return implode('#',$components);
    }

    public function getTorrentPath()
    {
      return $this->getOriginalFilePath().".torrent";
    }

    public function getOriginalFilePath()
    {
      return sfConfig::get('sf_upload_dir').'/'.$this->getFileName();
    }

    public function __destruct()
    {
      try {
        if($this->isNew())
        {
          $this->cleanupFiles();
        }
      }
      catch(Exception $e){}

    }
    protected function cleanupFiles()
    {
      if($this->getFileName())
      {
        @unlink($this->getTorrentPath());
        @unlink($this->getOriginalFilePath());
      }
    }
    public function delete($con = null)
    {
      try {
        $ret=parent::delete($con);
        $this->cleanupFiles();
        if($this->getFileName())
        {
          @unlink($this->getOriginalFilePath());
        }
      }
      catch(Exception $e)
      {
        $this->cleanupFiles(); // make this always happen
        throw $e;
      }
      return $ret;
    }

    public function getClients($criteria= null, $con = null)
    {
      if(! $criteria instanceof Criteria)
      {
        $criteria = new Criteria();
        $criteria->addAscendingOrderByColumn(ClientPeer::BYTES_LEFT);
        $criteria->addAscendingOrderByColumn(ClientPeer::BYTES_UPLOADED);
        $criteria->addDescendingOrderByColumn(ClientPeer::UPDATED_AT);
        // naive
      }
      return parent::getClients($criteria,$con);
    }
    public function reap($try_to_do_global=true)
    {
      return ClientPeer::reap($this->getClients(),$try_to_do_global);
    }

    public function getFileSize()
    {
        // todo remote files need to have this information cached
        // NB: this will look negative if >= 2**31 use sprintf 
        return filesize($this->getOriginalFilePath());
    }

    public function getTitle() // convenience method for sfFeed2Plugin
    {
        return $this->getEpisode()->getTitle();
    }
}
