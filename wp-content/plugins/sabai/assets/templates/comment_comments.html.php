<?php $token = $this->Token('comment_vote_comment', 1800, true);?>
<ul id="sabai-comment-comments-<?php echo $entity->getId();?>" class="sabai-comment-comments">
<?php foreach ($comments as $comment):?>
  <?php echo $this->Comment_Render($comment->toArray(), $entity, $parent_entity, in_array($comment->id, $comments_voted) ? false : $token, $modal);?>
<?php endforeach;?>
</ul>