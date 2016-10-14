<?php
class Sabai_Addon_Voting_Helper_RenderFlags extends Sabai_Helper
{
    public function help(Sabai $application, Sabai_Addon_Entity_IEntity $entity)
    {
        $li = array();
        $flags = $entity->data['voting_flags'];
        if (empty($flags)) {
            return;
        }
        usort($flags, array($this, '_sortByTimestamp')); // sort flags by chronological order
        $li_html = '<li id="sabai-voting-flag-%3$d" class="%6$s">
  <div class="sabai-voting-flag-avatar">%1$s</div>
  <ul class="sabai-voting-flag-meta"><li class="sabai-voting-flag-author">%2$s</li><li class="sabai-voting-flag-date">%4$s</li></ul>
  <div class="sabai-voting-flag-main">%5$s</div>
  <div class="sabai-voting-flag-score">%7$s</div>
</li>
';
        $flag_options = $application->Voting_FlagOptions();
        foreach ($flags as $flag) {
            $li[] = sprintf(
                $li_html,
                $flag->user_id ? $application->UserIdentityThumbnailSmall($flag->User) : '<i class="fa fa-lg fa-exclamation-circle"></i>',
                $flag->user_id ? $application->UserIdentityLink($flag->User) : __('System Message', 'sabai'),
                $flag->id,
                $application->getPlatform()->getHumanTimeDiff($flag->created),
                $flag->comment ? Sabai::h($flag->comment) : $flag_options[$flag->value],
                $entity->getAuthorId() === $flag->User->id ? 'sabai-voting-flag-by-owner' : '',
                $flag->value > 0 ? $flag->value : ''
            );
        }
        $link_options = array('width' => 470, 'content' => 'target.focusFirstInput();');
        $links = array(
            0 => $application->LinkToModal(__('Ignore Flags', 'sabai'), $application->Entity_Url($entity, '/voting/flags/ignore'), $link_options + array('icon' => 'check-circle'), array('class' => 'sabai-btn sabai-btn-success', 'title' => __('Ignore Flags', 'sabai'))),
        );
        $label = $application->Entity_BundleLabel($application->Entity_Bundle($entity), true);
        $links[2] = $application->LinkToModal(sprintf(__('Delete %s', 'sabai'), $label), $application->Entity_Url($entity, '/delete', array('delete_target_id' => 'sabai-entity-content-' . $entity->getId())), $link_options + array('icon' => 'trash-o'), array('class' => 'sabai-btn sabai-btn-danger', 'title' => sprintf(__('Delete %s', 'sabai'), $label)));
        ksort($links);
        return '<ul class="sabai-voting-flags">' . implode(PHP_EOL, $li) . '</ul><ul class="sabai-voting-flags-actions"><li>' . implode('</li><li>', $links) . '</li></ul>';
    }
    
    private static function _sortByTimestamp($a, $b)
    {
        return $a->created < $b->created ? -1 : 1;
    }
}