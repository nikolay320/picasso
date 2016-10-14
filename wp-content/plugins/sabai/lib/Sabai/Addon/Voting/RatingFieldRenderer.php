<?php
class Sabai_Addon_Voting_RatingFieldRenderer extends Sabai_Addon_Field_Renderer_AbstractRenderer
{
    protected static $_jsLoaded;
    
    protected function _fieldRendererGetInfo()
    {
        return array(
            'field_types' => array($this->_name),
            'default_settings' => array('hide_average' => false),
        );
    }

    public function fieldRendererRenderField(Sabai_Addon_Field_IField $field, array $settings, array $values, Sabai_Addon_Entity_IEntity $entity)
    {
        $ret = array();
        $count_formats = isset($settings['count_formats'])
            ? (is_array($settings['count_formats']) ? $settings['count_formats'] : array($settings['count_formats'], $settings['count_formats']))
            : array('%d', '%d');
        $count = sprintf(_n($count_formats[0], $count_formats[1], $values['']['count'], 'sabai'), $values['']['count']);
        $ret[] = $this->_addon->getApplication()->Voting_RenderRating($values['']['average']);
        if (!$settings['hide_average']) {
            $ret[] = sprintf('<span class="sabai-voting-rating-average" itemprop="ratingValue">%s</span>', number_format($values['']['average'], 2));
        }
        if (isset($settings['summary_url'])) {
            // Load Js files
            if (!self::$_jsLoaded) {
                $this->_addon->getApplication()->LoadJs('Chart.min.js', 'chartjs');
                self::$_jsLoaded = true;
            }
            
            $ret[] = $this->_addon->getApplication()->LinkToModal(
                '<i class="fa fa-bar-chart"></i>',
                $settings['summary_url'],
                array('no_escape' => true),
                array('class' => 'sabai-voting-rating-details', 'title' => __('Rating Details', 'sabai'), 'data-modal-title' => __('Rating Details', 'sabai'))
            );
        }
        $ret[] = sprintf(' (<span class="sabai-voting-rating-count">%s</span>)', isset($settings['link']) ? '<a href="'. $settings['link'] .'">' . $count . '</a>' : $count);
        
        return implode('', $ret);
    }
}