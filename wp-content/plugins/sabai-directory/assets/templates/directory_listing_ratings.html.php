<?php if (!empty($rating_values)):?>
<script type="text/javascript">
jQuery(document).ready(function ($) {
    var radarChartData = {
        labels: <?php echo json_encode(array_values($rating_criterion));?>,
        datasets: [
            {
                fillColor: 'rgba(0,0,0,0.1)',
                strokeColor: 'rgba(0,0,0,0.1)',
                data: <?php echo json_encode(array_values($rating_values));?>,
            },
        ]
    };
    new Chart($('.sabai-directory-radar-chart > canvas').get(0).getContext('2d')).Radar(radarChartData, {
        scaleShowLabels: true,
        pointDot : false,
        datasetStrokeWidth : 1,
        scaleFontSize: 10,
        pointLabelFontSize : 10,
        scaleOverride: true,
        scaleSteps: 5,
        scaleStepWidth: 1,
        scaleStartValue: 0,
        tooltipFontSize: 12,
    });
});
</script>
<?php endif;?>
<div class="sabai-row">
    <div class="<?php if (empty($rating_values)):?>sabai-col-sm-12<?php else:?>sabai-col-sm-5<?php endif;?> sabai-directory-chart sabai-directory-bar-chart">
<?php if (!empty($rating_values)):?>
        <h2><?php echo __('Rating distribution', 'sabai-directory');?></h2>
<?php endif;?>
        <table>
            <tbody>
<?php foreach (range(50, 0, 5) as $rating): $_rating_count = $rating_summary[$rating];?>
                <tr>
                    <th scope="row"><?php echo $this->Voting_RenderRating($rating / 10);?></th>
                    <td><div class="sabai-directory-chart-bar" style="width:<?php echo $percent = round($_rating_count / $rating_count_max, 2) * 100;?>%;"><span class="<?php if ($percent > 30):?>sabai-directory-chart-bar-text-inside<?php else:?>sabai-directory-chart-bar-text-outside<?php endif;?>"><?php echo $_rating_count;?></span></div></td>
                </tr>
<?php endforeach;?>
            </tbody>
        </table>
    </div>
<?php if (!empty($rating_values)):?>
    <div class="sabai-col-sm-7 sabai-directory-chart sabai-directory-radar-chart">
        <h2><?php echo __('Rating summary', 'sabai-directory');?></h2>
        <canvas height="319"></canvas>
    </div>
<?php endif;?>
</div>