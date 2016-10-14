(function($){
    SABAI.Markdown = SABAI.Markdown || {};
    SABAI.Markdown.editor = SABAI.Markdown.editor || function(id, langs, helpSettings) {
        if (!$("#wmd-input-" + id).length) return;
        var converter = Markdown.getSanitizingConverter(),
            helpButton = !helpSettings.url ? null : {
                handler: function(){
                    window.open(helpSettings.url, "markdown-help", "width=" + helpSettings.width + ",height=" + helpSettings.height + ",scrollbars=yes,resizable=yes,location=no,toolbar=no,menubar=no,status=no");
                }
            },
            editor = new Markdown.Editor(converter, "-" + id, {strings: langs, helpButton: helpButton});          
        editor.run();
    };
})(jQuery);