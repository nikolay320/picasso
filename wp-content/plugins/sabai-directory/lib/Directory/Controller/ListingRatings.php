<?php
class Sabai_Addon_Directory_Controller_ListingRatings extends Sabai_Controller
{
    protected function _doExecute(Sabai_Context $context)
    {        
        $rating_summary = $this->Voting_RatingSummary($context->entity);
        $rating_count = array_sum($rating_summary);
        $rating_count_max = max($rating_summary);
        foreach (range(0, 50, 5) as $rating) {
            if (isset($rating_summary[$rating])) {
                $rating_summary[$rating] = $rating_summary[$rating];
            } else {
                $rating_summary[$rating] = 0;
            }
        }
        ksort($rating_summary, SORT_NUMERIC);
        $field = $this->Entity_Field($this->Entity_Addon($context->entity)->getReviewBundleName(), 'directory_rating');
        if (!$field) {
            $context->setError();
            return;
        }
        
        $widget_settings = $field->getFieldWidgetSettings();
        $context->addTemplate('directory_listing_ratings')->setAttributes(array(
            'rating_summary' => $rating_summary,
            'rating_count' => $rating_count,
            'rating_count_max' => $rating_count_max,
        ));
        
        if (!empty($widget_settings['criterion']['options'])) {
            $rating_values = array();
            foreach (array_keys($widget_settings['criterion']['options']) as $criteria) {
                if (!in_array($criteria, $widget_settings['criterion']['default'])) {
                    unset($widget_settings['criterion']['options'][$criteria]);
                    continue;
                }
                if (isset($context->entity->voting_rating[$criteria]['average'])) {
                    $rating_values[$criteria] = (float)$context->entity->voting_rating[$criteria]['average'];
                }
            }
            if (!empty($rating_values)) {
                $context->rating_values = $rating_values;
                $context->rating_criterion = $widget_settings['criterion']['options'];
                
                $this->LoadJs('Chart.min.js', 'chartjs');
            }
        }
    }
}
