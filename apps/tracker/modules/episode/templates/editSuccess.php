<?php echo link_to($podcast->getTitle(),'podcast/edit?id='.$podcast->getId()) ?>
<?php page_title($episode->getTitle()); ?>

<div class="delete-form-wrapper">
  <?php echo delete_form_for_object($episode); ?>
</div>
<div class="form-wrapper <?php if($form->hasErrors()) { echo "open-form"; } ?>">
  <form action="<?php echo url_for('episode/edit') ?>" method="POST" enctype="multipart/form-data">
    <table>
      <?php include_partial('episode/episodeform', Array('form'=>$form)); ?>
      <tr class="form-field">
        <td>&nbsp;</td>
        <td>
          <input type="submit" value="Save" class="save-button" />
          <input type="submit" class="close-form" value="Cancel" />
        </td>
      </tr>
    </table>

    <?php echo $form['podcast_id'] ?>
    <?php echo $form['id'] ?>
    <?php echo $form['_csrf_token'] ?>

  </form>
</div>

<h3>Formats</h3>
  <?php foreach($feeds as $feed): ?>
    <?php if(isset($files[$feed->getId()])):
        $torrent=$files[$feed->getId()];
        ?>
      <?php include_partial('torrent', Array('feed'=>$feed,'torrent'=>$torrent,'delete'=>true)); ?>
    <?php else: ?>
        <?php 
            $form=new TorrentForm();
            $form->setDefaults(Array(
            'episode_id'=>$episode->getId(),
            'feed_id'=>$feed->getId(),
            ),Array());
        ?>
        <div class="torrent_file">
          <?php if(isset($feed)): ?>
            <?php echo $feed ?>
          <?php endif; ?>

          <a href="#" class="add-file-open">Choose file</a>
          <div class="add-file" style="display: none;">
            <?php include_partial('torrent/add',Array('feed'=>$feed,'form'=>$form)); ?>
          </div>
        </div>
            
    <?php endif; ?>
  <?php endforeach; ?>

<a href="#" id="add-format-open">Add new format</a>

<div id="add-format" style="display: none;">
<h3>Add new format</h3>
  <div class="form-wrapper open">
  <form action="<?php echo url_for('podcast_feed/add') ?>" method="POST" enctype="multipart/form-data" class=".form_feed">
  <table>
    <?php
        // todo move to action
        $fform =new FeedForm();
        $fform->setDefault('podcast_id',$episode->getPodcastId());
    ?>
    <?php include_partial('podcast_feed/feedform', Array('form'=>$fform)); ?>
    <tr>
    <td>&nbsp;</td>
    <td>
        <input type="submit" value="Save"/>
    </td>
    <td>

    </td>
    </tr>
 
 </table>
  </form>
</div>
</div>
