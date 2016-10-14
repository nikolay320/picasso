(function($){
    SABAI.File = SABAI.File || {};
    SABAI.File.upload = SABAI.File.upload || function (options) {
        var options = $.extend({
            selector: "",
            maxNumFiles: 0,
            maxNumFileExceededError: "",
            paramName: "sabai_file",
            inputName: "files",
            sortable: true,
            formData: {}
        }, options),
            numFilesUploaded = 0,
            $uploader = $(options.selector);
            
        if (!$uploader.length) {alert(1);return};
        
        var $container = $uploader.closest('.sabai-form-fields'),
            $progress = $container.find('.sabai-progress'),
            $progressBar = $progress.find('.sabai-progress-bar');
        
        $uploader.fileupload({
            url: options.url,
            dataType: 'json',
            paramName: options.paramName,
            formData: options.formData,
            singleFileUploads: true,
            //forceIframeTransport: true,
            submit: function (event, data) {
                if (options.maxNumFiles && numFilesUploaded + data.files.length > options.maxNumFiles) {
                    if (options.maxNumFileExceededError) alert(options.maxNumFileExceededError);
                    return false;
                }
                $progressBar.attr('aria-valuenow', 0).css('width', '0%').text('0%');
                $progress.show();
            },
            fail: function (e, data) {
                $progress.hide();
                SABAI.flash(data.result.error, 'danger');
            },
            done: function (e, data) {
                $progress.hide();
                if (data.result.error) {
                    SABAI.flash(data.result.error, 'danger');
                    return;
                }
                numFilesUploaded += data.result.files.length;
                var table = $container.find('.sabai-file-current table');
                $.each(data.result.files, function (index, file) {           
                    var new_row = "<tr class=\'sabai-file-row\'><td class=\'sabai-form-check\'><input name=\'" 
                        + options.inputName + "[current][" + file.id + "][check][]\' type=\'checkbox\' value=\'"
                        + file.id + "\' checked=\'checked\'></td><td>";
                    if (file.thumbnail) {
                        new_row += "<img src=\'" + file.thumbnail + "\' alt=\'\' />";
                    } else {
                        new_row += "<i class=\'fa " + file.icon + "\'></i>";
                    }
                    new_row += "</td><td><input name=\'" + options.inputName + "[current][" + file.id + "][name]\' type=\'text\' value=\'"
                        + file.title + "\' /></td><td>"+ file.size_hr + "</td></tr>";
                    
                    if (!table.has(".sabai-file-row").length) {
                        table.find("tbody").html(new_row).effect("highlight", {}, 2000);
                    } else {
                        $(new_row).appendTo(table.find("tbody")).effect("highlight", {}, 2000);
                    }
                });
                if (options.sortable) {
                    SABAI.init(table.find("tbody").sortable("destroy").sortable({containment:"parent", axis:"y"}).parent()); // reset table
                }
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $progressBar.attr('aria-valuenow', progress).css('width', progress + '%').text(progress + '%');
            }
        });
        $(options.form).submit(function () {
            if (options.maxNumFiles && $container.find(".sabai-file-current tbody input[type='checkbox']:checked").length > options.maxNumFiles) {
                if (options.maxNumFileExceededError) alert(options.maxNumFileExceededError);
                return false;
            }
        });
        if (options.sortable) {
            $container.find('.sabai-file-current tbody').sortable({containment:"parent", axis:"y"});
        }
    }
})(jQuery);