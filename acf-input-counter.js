
(function($) {
	acf.change_count_text = function(e){
		var $max = e.$el.attr('maxlength');
		if (typeof($max) == 'undefined' || e.$el.closest('.acf-input').find('.count').length == 0) {
			return;
		}
		var $value = e.$el.val();
                
		var $length = $value.length;
		e.$el.closest('.acf-input').find('.count').text($length);
	}
                
	acf.change_count_textarea = function(e){
		var $max = e.$el.attr('maxlength');
		if (typeof($max) == 'undefined') {
			return;
		}
		var $value = e.$el.val();
		var $length = $value.length;
		e.$el.closest('.acf-input').find('.count').text($length);
	}
        
        acf.check_maxlength = function (e) {
                var self = $(e);
                // get element
                self.$el = self.find('.acf-input');
                self.$textarea = self.$el.find('textarea');
                if(!self.$el.attr('maxlength')){
                        var ajax_data = acf.prepare_for_ajax({
                                action		: 'get_maxlength',
                                field_key	: acf.get_data(self.$el.parent(), 'key')
                        });
                        // get maxlength 
                        $.ajax({
                                url:            acf.get('ajaxurl'),
                                dataType:	'json',
                                type:		'post',
                                data:		ajax_data,
                                success:	function( json ){
                                        if(json.success){
                                                //console.info(json, self.$el);
                                                self.$textarea.attr('maxlength', json.data.maxlength );                                            
                                        }else{                                            
                                                console.warn(json, self.$el);
                                        }
                                }
                        });
                }
        }
                
	acf.fields.text_counter = acf.field.extend({
		type: 'text',
		
		events: {
			'input input': 'change_count',
                        'focus input': 'change_count',
		},
		
		change_count: function(e){acf.change_count_text(e)}
		
	});

	acf.fields.qtranslate_text_counter = acf.field.extend({
		type: 'qtranslate_text',
		
		events: {
			'input input': 'change_count',
			'focus input': 'change_count',
		},
		
		change_count: function(e){acf.change_count_text(e)}
		
	});
})(jQuery);


(function($) {
	acf.fields.textarea_counter = acf.field.extend({
		type: 'textarea',
		
		events: {
			'input textarea': 'change_count',
			'focus textarea': 'change_count',
		},
		
		change_count: function(e){acf.change_count_textarea(e)}
		
	});

	acf.fields.qtranslate_textarea_counter = acf.field.extend({
		type: 'qtranslate_textarea',
		
		events: {
			'input textarea': 'change_count',
			'focus textarea': 'change_count',
		},
		
		change_count: function(e){acf.change_count_textarea(e)}
		
	});

	acf.fields.wysiwyg_counter = acf.field.extend({
		type: 'wysiwyg',
		
		actions: {
			'ready': 'check_maxlength',
		},
		
		events: {
			'input textarea': 'change_count',
			'focus textarea': 'change_count',
			'change .wp-editor-area': 'change_count',
		},
                
                check_maxlength: function (e) {acf.check_maxlength(e)},
		
		change_count: function(e){acf.change_count_textarea(e)}
		
	});

	acf.fields.qtranslate_wysiwyg_counter = acf.field.extend({
		type: 'qtranslate_wysiwyg',
		
		actions: {
			'ready': 'check_maxlength',
		},
		
		events: {
			'input textarea': 'change_count',
			'focus textarea': 'change_count',
			'change .qtx-wp-editor-area': 'change_count',
			'click .wp-editor-tabs button, a.wp-switch-editor': 'check_editor',
		},
                
                check_maxlength: function (e) {acf.check_maxlength(e)},
		
		change_count: function(e){acf.change_count_textarea(e)},
                
		check_editor: function(e){           
                        var tinymceActive = (typeof tinyMCE != 'undefined') && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden();
                        /* Check if TinyMCE is active */
                        if (tinymceActive) {
                                var id = jQuery(tinyMCE.activeEditor.container.closest('.multi-language-field')).find('.acf-editor-wrap.current-language textarea').attr('id');
                                tinymce.get(id).fire('keyUp');
                        } else {
                                $('.qtx-wp-editor-area').trigger('change').focus();
                        }                
                }
		
	});
})(jQuery);
