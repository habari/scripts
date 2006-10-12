/*
 * Edit In Place
 * http://josephscott.org/code/js/eip/
 *
 * Version: 0.2.0
 * License: http://josephscott.org/code/js/eip/license.txt
 */
EditInPlace = function() { };
EditInPlace.settings = function(set) {
	var settings = {
		id:				false,
		save_url:		false,
		css_class:		'eip_editable',
		savebutton:		'eip_savebutton',
		cancelbutton:	'eip_cancelbutton',
		saving:			'eip_saving',
		type:			'text',
		orig_text:		false
	};

	for(var i in set) { settings[i] = set[i]; }

	return($H(settings));
};

EditInPlace.formField = function(set) {
	var field = '';
	set['orig_text'] = $(set['id']).innerHTML;

	if(set['type'] == 'text') {
		var size = set['orig_text'].length + 10;
		if(size >= 100) { size = 100; }
		if(set['size']) { size = set['size']; }

		field = '<span id="' + set['id'] + '_editor"><input id="'
			+ set['id'] + '_edit" class="' + set['css_class'] + '" name="'
			+ set['id'] + '_edit" type="text" size="' + size
			+ '" value="' + set['orig_text'] + '" />';
	}
	else if(set['type'] == 'textarea') {
		var cols = 50;
		if(set['cols']) { cols = set['cols']; }
		var rows = (set['orig_text'].length / cols) + 3;
		if(set['rows']) { rows = set['rows']; }

		field = '<span id="' + set['id'] + '_editor"><textarea id="'
			+ set['id'] + '_edit" class="' + set['css_class'] + '" name="'
			+ set['id'] + '_edit" rows="' + rows + '" cols="'
			+ cols + '">' + set['orig_text'] + '</textarea>';
	}

	return(field);
};

EditInPlace.formButtons = function(set) {
	return(
		'<br /><span><input id="' + set['id'] + '_save" class="'
		+ set['savebutton'] + '" type="button" value="SAVE" /> OR '
		+ '<input id="' + set['id'] + '_cancel" class="' 
		+ set['cancelbutton'] + '" type="button" value="CANCEL" />'
		+ '</span></span>'
	);
};

EditInPlace.setEvents = function(set) {
	Event.observe(
		set['id'],
		'mouseover',
		function() { Element.addClassName(set['id'], set['css_class']); },
		false
	);
	Event.observe(
		set['id'],
		'mouseout',
		function() { Element.removeClassName(set['id'], set['css_class']); },
		false
	);
	Event.observe(
		set['id'],
		'click',
		function() {
			Element.hide(set['id']);

			var field = EditInPlace.formField(set);
			var button = EditInPlace.formButtons(set);

			new Insertion.After(set['id'], field + button);
			Field.focus(set['id'] + '_edit');

			Event.observe(
				set['id'] + '_save',
				'click',
				function() { EditInPlace.saveChanges(set); },
				false
			);
			Event.observe(
				set['id'] + '_cancel',
				'click',
				function() { EditInPlace.cancelChanges(set); },
				false
			);
		},
		false
	);
};

EditInPlace.saveComplete = function(t, set) {
	$(set['id']).innerHTML = t.responseText;
};

EditInPlace.saveFailed = function(t, set) {
	$(set['id']).innerHTML = set['orig_text'];
	Element.removeClassName(set['id'], set['css_class']);
	alert('Failed to save changes.');
};

EditInPlace.saveChanges = function(set) {
	var new_text = escape($F(set['id'] + '_edit'));
	$(set['id']).innerHTML = 
		'<span class="' + set['saving'] + '">Saving ...</span>';

	Element.remove(set['id'] + '_editor');
	Element.show(set['id']);

	var params = 'id=' + set['id'] + '&content=' + new_text;
	var ajax_req = new Ajax.Request(
		set['save_url'],
		{
			method: 'post',
			postBody: params,
			onSuccess: function(t) { EditInPlace.saveComplete(t, set); },
			onFailure: function(t) { EditInPlace.saveFailed(t, set); }
		}
	);
};

EditInPlace.cancelChanges = function(set) {
	Element.remove(set['id'] + '_editor');
	Element.removeClassName(set['id'], set['css_class']);
	Element.show(set['id']);
}

EditInPlace.makeEditable = function(args) {
	EditInPlace.setEvents(EditInPlace.settings(args));
}
