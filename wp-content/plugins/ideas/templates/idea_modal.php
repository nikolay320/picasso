<script type="text/javascript">
jQuery(document).ready(function($){
    $('#ideaModal').on('show.bs.modal', function() {
        $('html').css({overflow: 'hidden'});
    });
    <?php $ideas = get_posts(array('post_type'=>'ideas')); ?>
    <?php if($ideas): ?>
      <?php 
        $autocomplete = '';
        foreach ($ideas as $key=>$value):
          if($key!=0){
            $autocomplete.=',';
          }
          $autocomplete.='"'.preg_replace('/"/', '\\"', $value->post_title).'"';
        endforeach 
      ?>
      $( ".idea_title" ).autocomplete({
        source: [ <?php echo $autocomplete ?> ]
      });
    <?php endif ?>

    function resetErrors(){
      $('#idea_title,#idea_text').removeClass('input_error')
      $('.max-size').removeClass('file_error')
      $('.form_error').remove()
    }

    

    function resetImagePreview(){
      $('#idea_image_preview').attr('src',"<?php echo plugins_url( 'ideas/assets/img/noimage.png' ) ?>")
      $(this).hide()
      $('#idea_image').val('')
    }

    //validation start
    function formValidation(){
      var form_errors = 0
      var image = $('#idea_image').get(0).files[0]
      var file  = $('#idea_attachment').get(0).files[0]
      var video  = $('#idea_video').get(0).files[0]

      resetErrors()

      if($('#idea_title').val()==''){
        $('#idea_title').addClass('input_error')
        $('#idea_title').prev().append('<span class="form_error"> <?php _e("Field is required",IDEAS_TEXT_DOMAIN); ?></span>')
        form_errors ++
      }

      if($('#idea_text').val()==''){
        $('#idea_text').addClass('input_error')
        $('#idea_text').prev().append('<span class="form_error"> <?php _e("Field is required",IDEAS_TEXT_DOMAIN); ?></span>')
        form_errors ++
      }

      if(image){
        if(image.size > 5000000){
          $('.max-size.img').addClass('file_error')
          form_errors ++
        }
      }
      if(file){
        if(file.size > 100000000){
          $('.max-size.attch').addClass('file_error')
          form_errors ++
        }
      }
      if(video){
        if(video.size > 262144000){
          $('.max-size.video').addClass('file_error')
          form_errors ++
        }

        var type = video.type;
        var video_type = type.split('/');
        
        if(video_type[0]!='video'){
          $('.max-size.video').addClass('file_error')
          form_errors ++
        }
      }
      if(form_errors>0){
        return false
      }
      return true
    }
    //validation end

    $('.reset_image').on('click',function(){
      resetImagePreview()
      $(this).hide()
    })

    $('#ideaModal').on('hide.bs.modal',function(){
      resetErrors();
      $('html').css({overflow: 'auto'});
      $('#upload-video').html('');
      $('#upload-image').html('');
      $('#upload-attachment').html('');
    })
    $('.idea_info_block i').on('click',function(){
      $('.idea_info_block').hide()
      $('.idea_info_block span').html('')
    })

    prevent_double_post=false
		$('.submit-idea-button').on('click',function(){
    if(prevent_double_post==true){
        prevent_double_post=false;
      } else {
      prevent_double_post=true  
      var reader  = new FileReader()
      var form = new FormData($('#add_idea_form').get(0))
      form.append( 'action', 'add_idea' )
      if(formValidation()){
        $('#ideaModal').modal('hide')
        $('.idea_loading_block').show()
        $.ajax({            
          type: "POST",
          url: '<?php echo admin_url("admin-ajax.php") ?>',
          data: form,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function ( response ) {
            resetImagePreview()     
            $('#add_idea_form').get(0).reset()
            $('.idea_info_block span').html(response.html)
            $('.idea_loading_block').hide()
            $('.idea_info_block').show()
            $('.idea_info_block').show()
            }
          })
        }
      }   
    })

})
</script>

<div class="modal fade" id="ideaModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display:none">
  <div class="modal-dialog" role="document" >
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?php _e("Add Idea",IDEAS_TEXT_DOMAIN); ?></h4>
      </div>
      <div class="modal-body">
        <form id="add_idea_form" enctype="multipart/form-data">
            <div class="form-group">
                <label><?php _e("Idea Title",IDEAS_TEXT_DOMAIN); ?>*</label>
                <input name="title" type="text" id="idea_title" class="form-control idea_title">
            </div>
            <div class="form-group">
                <label><?php _e("Idea Content",IDEAS_TEXT_DOMAIN); ?>*</label>
                <textarea name="text" id="idea_text" class="form-control" style="max-width:100%"></textarea>
            </div>
            <div class="form-group">
                <label><?php _e("Video (youtube)",IDEAS_TEXT_DOMAIN); ?></label>
                <input name="youtube" type="text" class="form-control">
            </div>
            <?php if(post_type_exists('campaigns')): ?>
              <?php if(!$current_campaign): ?>
                <?php $campaigns = get_posts(array('post_type'=>'campaigns','nopaging' => true)) ?>
                <?php if(!empty($campaigns)): ?>
                  <div class="form-group">
                    <label><?php _e("Campaigns",IDEAS_TEXT_DOMAIN); ?></label>
                    <select name="idea_campaign" class="form-control">
                      <option value=""><?php _e("Select campaign",IDEAS_TEXT_DOMAIN); ?></option>
                      <?php foreach ($campaigns as $key => $val): ?>
                        <?php $post_meta = get_post_meta($val->ID); ?>
                        <?php if(isset($post_meta['campaign_end_date'][0]) && !empty($post_meta['campaign_end_date'][0]) ): ?>
                          <?php if(strtotime($post_meta['campaign_end_date'][0]) > time()): ?>
                            <option value="<?php echo $val->ID ?>"><?php echo $val->post_title ?></option>
                          <?php endif ?>
                        <?php endif ?>
                      <?php endforeach ?>
                    </select>
                  </div>
                <?php endif ?>
              <?php else: ?>
                <input type="hidden" name="idea_campaign" value="<?php echo $current_campaign ?>">
              <?php endif ?>
            <?php endif ?>
            <div class="row">
              <div class="form-group col-md-4" style="margin-bottom: 0;">
                <label><span class="max-size img"><?php _e("Max file size",IDEAS_TEXT_DOMAIN); ?> ( 5 Mo )</span></label>
                <span class="btn btn-default btn-block btn-file">
                  <?php _e("Upload image",IDEAS_TEXT_DOMAIN); ?> <input name="image" type="file" id="idea_image" onchange="uploadfile('image')"> 
                </span>
                <b id="upload-image"></b>
              </div>
              <div class="form-group col-md-4" style="margin-bottom: 0;">
                <label><span class="max-size video"><?php _e("Max file size",IDEAS_TEXT_DOMAIN); ?> ( 250 Mo )</span></label>
                <span class="btn btn-default btn-block btn-file">
                  <?php _e("Upload video",IDEAS_TEXT_DOMAIN); ?> <input name="video" id="idea_video" type="file" onchange="uploadfile('video')">
                </span>
                <b id="upload-video"></b>
              </div>
              <div class="form-group col-md-4" style="margin-bottom: 0;">
                <label><span class="max-size attch"><?php _e("Max file size",IDEAS_TEXT_DOMAIN); ?> ( 100 Mo )</span></label>
                <span class="btn btn-default btn-block btn-file">
                <?php _e("Upload attachment",IDEAS_TEXT_DOMAIN); ?> <input name="attachment" id="idea_attachment" type="file" onchange="uploadfile('attachment')">
                </span>
                <b id="upload-attachment"></b>
              </div>
              <div class="clearfix"></div>
            </div>
        </form>
        <div class="form_errors"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e("Close",IDEAS_TEXT_DOMAIN); ?></button>
        <button type="button" class="btn btn-primary submit-idea-button" style="margin-bottom: 5px;"><?php _e("Add",IDEAS_TEXT_DOMAIN); ?></button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  function uploadfile(file){
      $('#upload-'+file).html('');
      var name_file = $('#idea_'+file).get(0).files[0];
      $('#upload-'+file).html(name_file.name);
    }
</script>
