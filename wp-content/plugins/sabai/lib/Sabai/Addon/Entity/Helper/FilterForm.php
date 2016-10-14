<?php
class Sabai_Addon_Entity_Helper_FilterForm extends Sabai_Helper
{    
    public function help(Sabai $application, $bundleName, $container, $target, SabaiFramework_Application_Url $url, array $filters, array $values = null, $submitOnChanage = true, $largeScreenSingleRow = true, $token = false, $buildId = false)
    {
        if (!$bundle = $application->Entity_Bundle($bundleName)) {
            return false;
        }
        
        $column_count = 1;
        $filter_positions = array();
        foreach ($filters as $filter_name => $filter) {
            if (!empty($filter->data['disabled'])
                || (!$ifilter = $application->Field_FilterImpl($filter->type, true))
            ) {
                unset($filters[$filter_name]);
                continue;
            }
            
            $settings = (array)@$filter->data['settings'] + (array)$ifilter->fieldFilterGetInfo('default_settings');
            $value = isset($values[$filter_name]) ? $values[$filter_name] : null;
            if (!$filter_form = $ifilter->fieldFilterGetForm($filter->Field, $filter_name, $settings, $bundle, $value, $values, $submitOnChanage, array($filter_name))) {
                unset($filters[$filter_name]);
                continue;
            }
            
            $row = empty($filter->data['row']) ? 1 : $filter->data['row'];
            $column = empty($filter->data['column']) ? 1 : $filter->data['column'];
            $filters[$filter_name] = array('#tree' => true) + $filter_form + array(
                '#title' => empty($filter->data['hide_label']) ? $filter->getLabel() : null,
                '#description' => (string)@$filter->data['description'],
                '#weight' => (int)@$filter->data['weight'],
                '#filter' => $filter,
            );
            $filter_positions[$row][$column][$filter_name] = $filter_name;
            
            if ($column > $column_count) {
                $column_count = $column;
            }
        }
        $filters = $application->Filter('entity_filter_form_filters', $filters, array($bundle));
        ksort($filter_positions);
        $form = array('#class' => 'sabai-entity-filter-form');
        $row_numbers = array_keys($filter_positions);
        $row_count = array_pop($row_numbers);
        if ($column_count > 6) {
            $column_count = 6;
        } else {
            while (!in_array($column_count, array(1, 2, 3, 4, 6))) {
                ++$column_count;
            }
        }
        $span = 12 / $column_count;
        $class = $largeScreenSingleRow ? 'sabai-col-md-12 ' : '';
        for ($i = 1; $i <= $row_count; $i++) {
            if (empty($filter_positions[$i])) continue;
            
            ksort($filter_positions[$i]);
            $form['row' . $i] = array(
                '#prefix' => '<div class="sabai-row">',
                '#suffix' => '</div>',
                '#tree' => false,
            );
            for ($j = 1; $j <= $column_count; $j++) {
                $column_name = 'column' . $i . '-' . $j;
                $form['row' . $i][$column_name] = array(
                    '#prefix' => '<div class="' . $class . ' sabai-col-sm-'. $span .'">',
                    '#suffix' => '</div>',
                    '#tree' => false,
                );
                if ((!$filter_name = @$filter_positions[$i][$j])
                    || (!$column_filters = array_intersect_key($filters, $filter_positions[$i][$j]))
                ) continue;
                
                $form['row' . $i][$column_name] += $column_filters;
            }
        }
        if (!$token) {
            $form['#token'] = false;
        }
        if (!$buildId) {
            $form['#build_id'] = false;
        }
        $form['#action'] = '#';
        $url->params['filter'] = 1;
        $form['#js'] = sprintf(
            'jQuery(document).ready(function($) {
    if (!$("%1$s").length) return;
    
    $("%1$s").find(".sabai-entity-filter-form").submit(function (e) {        
        SABAI.ajax({
            type: "post",
            container: "%1$s",
            target: "%2$s",
            url: "%3$s&" + $(this).serialize(),
            pushState: true
        });
        e.preventDefault();
    })%4$s;
    $(SABAI).bind("toggled.sabai", function (e, data) {
        if (data.trigger.hasClass("sabai-entity-btn-filter")
            && data.target.parents("%1$s").length
        ) {
            data.container = "%1$s";
            $(SABAI).trigger("entity_filter_form_toggled.sabai", data);
        }
    });
});',
            $container,
            $target,
            $url,
            $submitOnChanage ? '.change(function(e){if ($(e.target).parents(".sabai-field-filter-ignore").length > 0) return; $(this).submit();})' : ''
        );
        
        return $form;
    }
}