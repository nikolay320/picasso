(function() {
    function getSmileySrc(smiley) {
        if (/\.([^.]+)$/.test(smiley)) {
            return _smileySettings.src_url + smiley;
        } else {
            return ''.concat(twemoji.base, twemoji.size, '/', twemoji.convert.toCodePoint(smiley), twemoji.ext);
        }
    }

    function getHtml() {
        var supportEmoji = _wpemojiSettings.supports && _wpemojiSettings.supports.everything;
        var smilies = _smileySettings.smilies;
        var idx = 1;
        var cols = 5;
        var emoticonsHtml;

        emoticonsHtml = '<table role="list" class="mce-grid">';

        for (var text in smilies) {
            var icon = smilies[text];

            if (idx % cols == 1) emoticonsHtml += '<tr>';

            emoticonsHtml += '<td><div data-mce-alt="' + text + '" tabindex="-1" role="option" aria-label="' + text + '">';

            if (supportEmoji && icon.indexOf(".") == -1) {
                emoticonsHtml += icon;
            } else {
                emoticonsHtml += '<img src="' + getSmileySrc(icon) + '" />';
            }

            emoticonsHtml += '</div></td>';

            if (idx % cols == 5) emoticonsHtml += '</tr>';

            idx++;
        }

        emoticonsHtml += '</table>';

        return emoticonsHtml;
    }

    tinymce.PluginManager.add('smiley', function(editor) {
        editor.addButton('smiley', {
            type: 'panelbutton',
            panel: {
                classes: 'smily',
                role: 'application',
                autohide: true,
                html: getHtml,
                onclick: function(e) {
                    var linkElm = editor.dom.getParent(e.target, 'div');

                    if (linkElm) {
                        editor.insertContent(
                            '&nbsp;' + linkElm.getAttribute('data-mce-alt') + '&nbsp;'
                        );

                        this.hide();
                    }
                }
            },
            tooltip: 'Emoticons'
        });
    });
})();