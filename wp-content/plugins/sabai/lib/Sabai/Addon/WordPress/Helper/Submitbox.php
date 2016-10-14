<?php
class Sabai_Addon_WordPress_Helper_Submitbox extends Sabai_Helper
{    
    public function help(Sabai $application, $arg, $box)
    {
        list($hiddens, $delete_link, $publish_label, $entity, $actions) = $box['args'];
        $action_html = array();
        foreach ((array)$actions as $action) {
            $action_html[] = sprintf('<div class="misc-pub-section"><strong class="label">%s</strong>%s</div>', isset($action['label_markup']) ? $action['label_markup'] : Sabai::h($action['label']), $action['markup']);
        }
        printf(
            '<div class="submitbox" id="submitpost">
  <div id="minor-publishing">%s</div>
  <div id="major-publishing-actions">
  <div id="delete-action">%s</div>
  <div id="publishing-action">
    <input id="publish" class="button button-primary button-large" type="submit" accesskey="p" tabindex="5" value="' . Sabai::h($publish_label) . '" name="'. Sabai_Addon_Form::FORM_SUBMIT_BUTTON_NAME .'[0]">
  </div>
  <div class="clear"></div>
  </div>
</div>',
            empty($action_html) ? '' : '<div id="misc-publishing-actions">' . implode(PHP_EOL, $action_html) . '</div>',
            $delete_link
        );
        foreach ($hiddens as $hidden_name => $hidden_value) {
            printf('<input type="hidden" name="%s" value="%s" />', Sabai::h($hidden_name), Sabai::h($hidden_value));
        }
    }
}

