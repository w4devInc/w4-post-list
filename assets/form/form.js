jQuery(document).ready(function($){

	/* Limit number of checkbox checks */
	$(".checkbox_limit2").each(function(item, i) {
		var _this = $(this), max = _this.data('max');

		var bol = _this.find("input[type=checkbox]:checked").length >= max;     
		_this.find("input[type=checkbox]").not(":checked").attr("disabled",bol);

		_this.find("input[type=checkbox]").click(function() {
			var bol = _this.find("input[type=checkbox]:checked").length >= max;     
			_this.find("input[type=checkbox]").not(":checked").attr("disabled",bol);
		});
	});


	/* Limit number of checkbox checks */
	$(".checkbox_limit").each(function(item, i) {
		var _wrap = $(this);

		//var bol = _this.find("input[type=checkbox]:checked").length >= max;     
		//_this.find("input[type=checkbox]").not(":checked").attr("disabled",bol);

		_wrap.find("input[type=checkbox]").click(function() {
			var _that = $(this);
			checkbox_through(_wrap, _that);

			//var bol = _this.find("input[type=checkbox]:checked").length >= max;     
			//_this.find("input[type=checkbox]").not(":checked").attr("disabled",bol);
		});
	});

	function checkbox_through(_wrap, _that){
		var is_checked = _that.attr("checked") === undefined ? false : true;
		//console.log(is_checked);
		var _parent_id = _that.data('parent');
		var _parent = $('#term_'+ _parent_id);
		if( _parent.length > 0 )
		{
			//console.log(_parent);
			if( ! is_checked && _parent.is(":checked") ){
				// var _grand_parent_id = _parent.data('parent');
				_shiblings = $('input[data-parent='+ _parent_id +']:checked').length;
				//console.log('--' + _shiblings);
				if( _shiblings < 1 ){
					_parent.removeAttr("checked").removeAttr("disabled");
				}
			}
			else if( is_checked && _parent.not(":checked") ){
				_parent.attr("checked",true).attr("disabled", true);
			}
			checkbox_through(_wrap, _parent);
		}

		var num_checked = 0;
		_wrap.find('input[type=checkbox]:checked').each(function(i, item) {
			//console.log( item );
			if( $('input[data-parent='+ $(item).data('tid') +']:checked').length < 1 )
				num_checked++;
		});
		

		var bol = num_checked >= _wrap.data('max');
		_wrap.find('input[type=checkbox]:checked');
		_wrap.find("input[type=checkbox]").not(":checked").attr("disabled",bol);
	}


	/* Limit number of tags, tags = words separated by comma or anything defined */
	$(".tags_limit").each(function(item, i) {
		var tags, lc, _this = $(this), max = _this.data('max'), sep = _this.data('sep');

		_this.find("input[type=text]").bind('keypress', function(e){
			lc = String.fromCharCode(e.which);
			if( lc == sep && (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57) && e.which != 46) )
			{
				tags = _this.find("input[type=text]").val();
				tags = tags.replace(/\s*,\s*/g, ',').replace(/,+/g, ',').replace(/[,\s]+$/, '').replace(/^[,\s]+/, '');
				tags = tags.split(sep);
				if( tags.length >= max )
				{
					e.preventDefault();
				}
			}
		});
	});


	/* Limit number of tags, tags = words separated by comma or anything defined */
	$("[data-term_suggest2]").each(function(i, item) {
		var _this = $(item);
		var _input = _this.find('input');
		var _tax = _this.data('term_suggest');
		
		
		var r = _input.suggest( UGC.ajaxurl + "?action=UGC_term_suggest&tax=" + _tax, {
			delay: 500,
			minchars: 2,
			multiple: 1,
			onSelect: function(s){
				if( _this.hasClass('tags_limit') )
				{
					tags = _this.find("input[type=text]").val();
					tags = tags.replace(/\s*,\s*/g, ',').replace(/,+/g, ',').replace(/[,\s]+$/, '').replace(/^[,\s]+/, '');
					tags = tags.split(_this.data('sep') );
					if( tags.length >= _this.data('max') )
					{
						_input.val( tags.join( _this.data('sep') + ' ' ) );
					}
				}
			}
		});
	});

	/* Limit number of tags, tags = words separated by comma or anything defined */
	$("[data-auc]").each(function(i, item) {
		var _this = $(item);
		var _input = _this.find('input');
		var _imax = _this.data('max') || 10;
		var _isep = _this.data('sep') || ',';

		var source = _this.data('auc');
		if( source.length < 1 )
			return false;
		//console.log(source);

		var r = _input .bind( "keydown focus", function( event ) {
			if ( event.keyCode === $.ui.keyCode.TAB && $( this ).data( "ui-autocomplete" ).menu.active ) {
				event.preventDefault();
			}
		})
		.autocomplete({
			minLength: 0,
			source: function( request, response ) {
				// delegate back to autocomplete, but extract the last term
				var results = $.ui.autocomplete.filter(	source, extractLast( request.term ) );
				response(results.slice(0, 10));
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push( ui.item.value );

				if( terms.length >= _imax )
				{
					this.value = terms.join( _isep + ' ' );
				}
				else
				{
					// add placeholder to get the comma-and-space at the end
					terms.push( "" );
					this.value = terms.join( _isep + " " );
				}
				return false;
			}
		});
	});

	function split( val ) {
		return val.split( /,\s*/ );
	}
	function extractLast( term ) {
		return split( term ).pop();
	}
});