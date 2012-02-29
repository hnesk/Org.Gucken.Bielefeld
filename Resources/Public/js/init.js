$(document).ready(function($) {
	
	var initializeExternalIdLookup = function() {
		$('.externalIdentifierScheme').change(function() {
			var $selectHolder = $(this).nextAll('span.selectHolder').first();			
			$selectHolder.load($selectHolder.data('url'), {type:$(this).val()});
		});		
	}
	
	var initializeAjaxLoadingIndicator = function() {			
		$('#ajax-indicator').
			ajaxStart(function() {$(this).text('loading...');}).
			ajaxStop(function() {$(this).text(' ');});
	}
	
	
	var update = function(data) {
		if(data.update) {
			$.each(data.update, function(id, html) {$('#'+id).html(html);});
		}
		if(data.replace) {
			$.each(data.replace, function(id, html) {$('#'+id).replaceWith(html);});
		}
		if(data.remove) {
			$.each(data.remove, function(id, html) {$('#'+id).replaceWith('');});
		}
		if(data.append) {
			$.each(data.append, function(id, html) {$('#'+id).html($('#'+id).html() + html)});
		}
		
		
		$('.ui-draggable').draggable('enable');
	};	
	
	
	var initializePopover = function() {
		$('a[rel=popover]').popover({
			content: function () {return $($(this).attr('href')).html();},
			placement:'left',
			trigger:'manual'
		}).click(function() {
			$(this).popover('toggle');
		});

		$('a[rel=ajaxpopover]').popover({
			placement:'left',
			trigger:'manual'
		}).click(function(event) {
			var el = $(this);
			if (!el.attr('data-content') && el.attr('href')) {			
				$.ajax({
					url: el.attr('href'),
					dataType: 'json',
					success: function(data) {
						el.attr('data-content', data.content);
						el.popover('toggle');
						update(data);
					}
				});
			} else {
				el.popover('toggle');				
			}
			event.preventDefault();
		});
	}
	
	var initializeAjaxableActions = function() {
		$('a.ajaxable').click(function (e) {		 	
			$.ajax({
				url: $(this).attr('href'),
				success:update,
				dataType:'json'
			});
			e.preventDefault();
		});
	}
	
	var initializeDangerousButtons = function() {
		$('.btn-danger').click(function (e) {		 	
			var what = $(this).attr('title');
			what = what ? what : 'das';
			if (!window.confirm('Bist du sicher, dass du ' + what + ' willst?')) {
				e.preventDefault();
			}
		});
	}

	var initializeAutoAdd = function() {
		$('.autoadd').each(function() {
			var $self = $(this);		
			var $prototype = null;
			var $insertAfter = $self;		
			var data = $self.data('autoadd');
			var $observedElements = $self.find(data.selector);
			var initialNumber = 1 * $observedElements.first().attr('name').replace(/^.+\[(\d+)\].+$/,'$1');
			var currentNumber = initialNumber;

			$observedElements.each(function () {
				$self.bind(data.event,function() {
					currentNumber++;
					var $newElement = $prototype.clone(true,true);				
					$newElement.find('input, select, textarea').each(function() {
						var $formElement = $(this);

						if ($formElement.attr('name')) {
							$formElement.attr('name', $formElement.attr('name').replace('['+initialNumber+']', '['+currentNumber+']'));
						}
						if ($formElement.attr('id')) {
							$formElement.attr('id', $formElement.attr('id').replace('_'+initialNumber+'_', '_'+currentNumber+'_'));
						}					
					});		
					$insertAfter.after($newElement);							
					$insertAfter = $newElement;

					$focusElement = $newElement.find(data.selector).first();
					$focusElement.focus();
					$focusElement.val('');
				});						
			});
			$prototype = $self.clone(true,true);
		});
		
	}

	var initializeDragAndDrop = function() {

		$('.identities').droppable({
			accept: '.identity',
			greedy:true,
			activeClass: "dropaccept",
			hoverClass: "drophover",
			drop: function( event, ui ) {
				var $identity = $(ui.draggable);
				$identity.appendTo(this);
				$('.ui-draggable').draggable('disable');			
				var ajaxOptions = {
					url: $identity.data('mergeurl'),
					data: {event:$(this).parents('.event').data('identity')},
					success:update,
					dataType:'json'
				};
				$.ajax(ajaxOptions);						
			}		
		});

		$('.factoids .identity').draggable({
			handle:'span.grip', 
			revert:true,
			distance:20
		});

		$('.events').droppable({
			accept: '.identity',
			activeClass: "dropaccept",
			hoverClass: "drophover",
			drop: function( event, ui ) {
				$(ui.draggable).appendTo(this);
				$('.ui-draggable').draggable('disable');
				var convertActionLink = $(ui.draggable).find('a.convert').first().attr('href');
				var ajaxOptions = {
					url: convertActionLink,
					data: {event:$(this).parents('.event').data('identity')},
					success:update,
					dataType:'json'
				};
				$.ajax(ajaxOptions);						
			}		
		});		
	}
	

	initializeDragAndDrop();
	initializeAutoAdd();
	initializePopover();
	initializeAjaxLoadingIndicator();
	initializeAjaxableActions();
	initializeExternalIdLookup();
	initializeDangerousButtons();
	
	//$(".collapse").collapse();

});
