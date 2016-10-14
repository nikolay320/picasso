<?php
wp_dequeue_script('cpm_chart');
wp_enqueue_script('chart-js');
?>
<div class="modal fade expert-reviews-chart" id="expert-reviews-chart" tabindex="-1" role="dialog" style="display:none; outline: none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php _e('Close', IDEAS_TEXT_DOMAIN); ?>"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php _e('Expert Rating Details', IDEAS_TEXT_DOMAIN); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 bar-chart">
                        <div class="text-center chart-title"><?php _e('Rating distribution', IDEAS_TEXT_DOMAIN); ?></div>
                        <canvas id="expert-rating-horizontal-chart"></canvas>
                    </div>
                    <div class="col-md-6 radar-chart">
                        <div class="text-center chart-title"><?php _e('Rating summary', IDEAS_TEXT_DOMAIN); ?></div>
                        <canvas id="expert-rating-radar-chart"></canvas>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close', IDEAS_TEXT_DOMAIN); ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->