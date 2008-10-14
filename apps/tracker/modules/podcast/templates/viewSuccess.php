Title: <?php echo $podcast->getTitle() ?>
<ul><?php  foreach($torrents as $torrent): ?>
  <li>
    <?php echo link_to($torrent->getTitle(),$torrent->getUrl()) ?>
    <?php echo link_to('[details]','torrent/details?id='.$torrent->getId()); // fixme id numbers in urls boo ?>
    <?php echo link_to('[magnet]',$torrent->getMagnet()); ?> 
  </li>
<?php endforeach; ?></ul>
<?php if($sf_user->isAuthenticated()): ?>
  <?php if(!$podcast->getFeedUrl()) echo link_to('add torrent','torrent/upload?podcast_id='.$podcast->getId()); ?>
<?php endif; ?>
<?php slot('feed');
echo auto_discovery_link_tag ('rss','feed/feed?id='.$podcast->getId());
end_slot();
?>
