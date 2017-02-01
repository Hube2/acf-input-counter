(function ($) {
        $(document).ready(function () {
                /**
                 * Sync conters with qtranslatex language switchers.
                 */
                $('body').on('click', '.qtranxs-lang-switch', function () {
                        var parent = $('.multi-language-field'), language = $(this).attr('lang');
                        // this is for qtranslate_text and qtranslate_textarea fields
                        parent.find('input[data-language="' + language + '"], textarea[data-language="' + language + '"]').trigger('input');
                        // this is for qtranslate_wysiwyg field
                        parent.find('textarea[name$="[' + language + ']"]').trigger('change');
                });
        });

        acf.change_count_text = function (e) {
                var $max = e.$el.attr('maxlength');
                if (typeof ($max) == 'undefined' || e.$el.closest('.acf-input').find('.count').length == 0) {
                        return;
                }
                var $value = e.$el.val();

                var $length = $value.length;
                e.$el.closest('.acf-input').find('.count').text($length);
        }

        acf.change_count_textarea = function (e) {
                var $max = e.$el.attr('maxlength');
                if (typeof ($max) == 'undefined') {
                        return;
                }
                var $value = e.$el.val();
                var $length = $value.length;
                e.$el.closest('.acf-input').find('.count').text($length);
        }

        acf.refresh_hidden = function (e) {
                var self = $(e.$field);
                self.$el = self.find('.acf-input');

                /**
                 * if qTranslete is enabled get the active language
                 */
                var active_lang = typeof qTranslateConfig == 'object' ? qTranslateConfig.activeLanguage : false;

                switch (e.type) {
                        case 'text':
                        case 'qtranslate_text':
                                /**
                                 * this is needed if the field is hidden by tab layout
                                 */
                                active_lang ? self.$el.find('input[data-language="' + active_lang + '"]').trigger('input') : self.$el.find('input').trigger('input');
                                break;
                        case 'textarea':
                        case 'qtranslate_textarea':
                                /**
                                 * this is needed if the field is hidden by tab layout
                                 */
                                active_lang ? self.$el.find('textarea[data-language="' + active_lang + '"]').trigger('input') : self.$el.find('textarea').trigger('input');
                                break;
                }

        }

        acf.check_maxlength = function (e) {
                var self = $(e.$field);
                // get element
                self.$el = self.find('.acf-input');
                /**
                 * timeout is needed to wait for tinymce/quicktags activation
                 */
                setTimeout(function () {
                        self.$textarea = self.$el.find('textarea');
                        if (!self.$el.attr('maxlength')) {
                                var ajax_data = acf.prepare_for_ajax({
                                        action: 'get_maxlength',
                                        field_key: acf.get_data(self.$el.parent(), 'key')
                                });
                                // get maxlength 
                                $.ajax({
                                        url: acf.get('ajaxurl'),
                                        dataType: 'json',
                                        type: 'post',
                                        data: ajax_data,
                                        success: function (json) {
                                                if (json.success) {
                                                        //console.info(json, self.$el);
                                                        self.$textarea.attr('maxlength', json.data.maxlength);
                                                } else {
                                                        console.warn(json, self.$el);
                                                }
                                        },
                                        complete: function () {
                                                /**
                                                 * if qTranslete is enabled get the active language
                                                 */
                                                var active_lang = typeof qTranslateConfig == 'object' ? qTranslateConfig.activeLanguage : false;
                                                /**
                                                 * this is needed if the field is hidden by tab layout
                                                 */
                                                active_lang ? self.$el.find('textarea[name$="[' + active_lang + ']"]').trigger('change') : self.$textarea.trigger('change');
                                        }
                                });
                        }
                }, 1);
        }

        acf.fields.text_counter = acf.field.extend({
                type: 'text',
                actions: {
                        'load': 'refresh_hidden',
                },
                events: {
                        'input input': 'change_count',
                        'focus input': 'change_count',
                },
                change_count: function (e) {
                        acf.change_count_text(e)
                },
                refresh_hidden: function () {
                        acf.refresh_hidden(this)
                }

        });

        acf.fields.qtranslate_text_counter = acf.field.extend({
                type: 'qtranslate_text',
                actions: {
                        'load': 'refresh_hidden',
                },
                events: {
                        'input input': 'change_count',
                        'focus input': 'change_count',
                },
                change_count: function (e) {
                        acf.change_count_text(e)
                },
                refresh_hidden: function () {
                        acf.refresh_hidden(this)
                }

        });

        acf.fields.textarea_counter = acf.field.extend({
                type: 'textarea',
                actions: {
                        'load': 'refresh_hidden',
                },
                events: {
                        'input textarea': 'change_count',
                        'focus textarea': 'change_count',
                },
                change_count: function (e) {
                        acf.change_count_textarea(e)
                },
                refresh_hidden: function () {
                        acf.refresh_hidden(this)
                }

        });

        acf.fields.qtranslate_textarea_counter = acf.field.extend({
                type: 'qtranslate_textarea',
                actions: {
                        'load': 'refresh_hidden',
                },
                events: {
                        'input textarea': 'change_count',
                        'focus textarea': 'change_count',
                },
                change_count: function (e) {
                        acf.change_count_textarea(e)
                },
                refresh_hidden: function () {
                        acf.refresh_hidden(this)
                }

        });

        acf.fields.wysiwyg_counter = acf.field.extend({
                type: 'wysiwyg',
                actions: {
                        'load': 'check_maxlength',
                },
                events: {
                        'input textarea': 'change_count',
                        'focus textarea': 'change_count',
                        'change .wp-editor-area': 'change_count',
                },
                check_maxlength: function (e) {
                        acf.check_maxlength(e)
                },
                change_count: function (e) {
                        acf.change_count_textarea(e)
                }

        });

        acf.fields.qtranslate_wysiwyg_counter = acf.field.extend({
                type: 'qtranslate_wysiwyg',
                actions: {
                        'load': 'check_maxlength',
                },
                events: {
                        'input textarea': 'change_count',
                        'focus textarea': 'change_count',
                        'change .qtx-wp-editor-area': 'change_count',
                        'click .wp-editor-tabs button, a.wp-switch-editor': 'check_editor',
                },
                check_maxlength: function () {
                        acf.check_maxlength(this)
                },
                change_count: function (e) {
                        acf.change_count_textarea(e)
                },
                check_editor: function (e) {
                        /**
                         * timeout is needed to wait for tinymce/quicktags switching
                         */
                        setTimeout(function () {
                                var tinymceActive = (typeof tinyMCE !== 'undefined') && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden();
                                /* Check if TinyMCE is active */
                                if (tinymceActive) {
                                        var id = $(tinyMCE.activeEditor.container.closest('.multi-language-field')).find('.acf-editor-wrap.current-language textarea').attr('id');
                                        console.log(tinymce.get(id));
                                        tinymce.get(id).fire('keyUp');
                                } else {
                                        $('.qtx-wp-editor-area').trigger('change').focus();
                                }
                        }, 100);
                }
        });
})(jQuery);
