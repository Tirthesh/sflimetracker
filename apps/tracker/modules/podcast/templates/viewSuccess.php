<h2><?php echo $podcast->getTitle() ?></h2>

<?php if($sf_user->isAuthenticated()): ?>
<form action="<?php echo url_for('podcast/update') ?>" method="POST" enctype="multipart/form-data">
  <table>
    <?php echo $form ?>
    <tr>
      <td>
        <input type="submit" value="Save"/>
      </td>
      <td>
        <input type="submit" value="Remove"/><?php /* todo fixme */ ?>
      </td>
    </tr>
  </table>
</form>
<?php endif; ?>

<h3>Feeds</h3>
<?php if($feeds): ?>
<ul>
  <li>[html]
  <?php
    $url = url_for($sf_context->getRouting()->getCurrentInternalUri(), true);
    echo link_to($url,$url);
  ?>
    
  </li>
  <?php foreach($feeds as $feed): ?>
    <li>
      [rss]
      <?php 
        $url = url_for('feed/feed?id='.$feed->getId(),true); // make this use a slug todo
        echo $url;
      ?>
    </li>
  <?php endforeach; ?>
</ul>
<?php else: ?>
  <p><i>No feeds yet.</i></p>
<?php endif; ?>

<h3>Episodes</h3>
<?php
  if($sf_user->isAuthenticated())
  {  
    if(true) // TODO add !Podcast->isFeedBased() here
    {
      echo link_to('Add episode','episode/add?podcast_id='.$podcast->getId());
    }
  }
?>
<?php if($episodes): ?>
  <ul>
    <?php foreach($episodes as $episode): ?>
      <li>
        <?php echo link_to($episode->getTitle(),'episode/view?id='.$episode->getId()) ?>
      </li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p><i>No episodes yet.</i></p>
<?php endif; ?>

<?php slot('feed');
foreach($feeds as $feed) 
{
  echo auto_discovery_link_tag ('rss','feed/feed?id='.$feed->getId()); // make this use a slug todo
}
end_slot();
?>
