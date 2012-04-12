/*
* Delegation for your jQuery UI widgets
* https://github.com/aglemann/jquery-delegate/
* Copyright (c) 2011 Aeron Glemann
* Version: 1.0 (02/29/2011)
* Licensed under the MIT licenses:
* http://www.opensource.org/licenses/mit-license.php
* Requires: jQuery UI v1.8 or later
*/

DPGlobal.dates = {
	days: ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"],
	daysShort: ["Son", "Mon", "Die", "Mit", "Don", "Fre", "Sam", "Son"],
	daysMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa", "So"],
	months: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
	monthsShort: ["Jan", "Feb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"]
};

$(document).ready(function($) {

	var update = function(data, link, noReInit) {
		if(data.update) {
			$.each(data.update, function(id, html) {
				$('#'+id).html(html);
			});
		}
		if(data.replace) {
			$.each(data.replace, function(id, html) {
				$('#'+id).replaceWith(html);
			});
		}
		if(data.remove) {
			$.each(data.remove, function(id, html) {
				$('#'+id).replaceWith('');
			});
		}
		if(data.append) {
			$.each(data.append, function(id, html) {
				$('#'+id).html($('#'+id).html() + html);
			});
		}
		if(data.run) {
			$.each(data.run, function(fn, data) {
				update[fn](data,link);
			});
		}
	};

	update.toggleEvent = function(data, $link) {
		$('#event_'+data.event).collapse('show');
	};

	$('.datepicker').datepicker({
		'weekStart':1
	});

	//$('.typeahead').typeahead({});


	$('table.actiontable tbody').one('click','tr', function(e) {
		var $firstLink = $(this).find('a').first();
		window.document.location = $firstLink.attr('href');

	});

	var initializeExternalIdLookup = function() {
		$('.externalIdentifierScheme').change(function() {
			var $selectHolder = $(this).nextAll('span.selectHolder').first();
			$selectHolder.load($selectHolder.data('url'), {
				type:$(this).val()
			});
		});
	}

	var initializeAjaxLoadingIndicator = function() {
		$('#ajax-indicator').
		ajaxStart(function() {
			$(this).text('loading...');
		}).
		ajaxStop(function() {
			$(this).text(' ');
		});
	}


	var initializePopover = function() {
		$('a[rel=popover]').popover({
			content: function () {
				return $($(this).attr('href')).html();
			},
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
		$('body').on('click','a.ajaxable', function (e) {
			if (e.ctrlKey) {
				return;
			}
			var $self = $(this);
			var target = $self.attr('target');
			if (!$self.data('ajaxed')) {
				$.ajax({
					url: $self.attr('href'),
					success:function(data) {
						$self.data('ajaxed',true) ;
						update(data,$self,target);
					},
					dataType:'json'
				});
			}
			e.preventDefault();
		});
	};

	var initializeDangerousButtons = function() {
		$('body').on('click', '.btn-danger', function (e) {
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

	};

	initializeDragAndDrop = function() {

		$('body').on('mouseenter', '.factoids .identity',function() {
			var $this = $(this);
			if(!$this.is(':data(draggable)')) {
				$this.draggable({
					handle:'span.grip',
					revert:true,
					distance:20
				});
			}
		});

		$('body').on('mouseenter', '.identities',function() {
			var $this = $(this);
			if(!$this.is(':data(droppable)')) {
				$this.droppable({
					accept: '.identity',
					greedy:true,
					activeClass: "dropaccept",
					hoverClass: "drophover",
					drop: function( event, ui ) {
						var $identity = $(ui.draggable);
						$identity.appendTo(this);
						$('.ui-draggable').draggable('disable');
						$.ajax({
							url: $identity.data('mergeurl'),
							data: {event:$(this).parents('.event').data('identity')},
							success:update,
							dataType:'json'
						});
					}
				});
			}
		});


		$('body').on('mouseenter', '.events',function() {
			var $this = $(this);
			if(!$this.is(':data(droppable)')) {
				$this.droppable({
					accept: '.identity',
					activeClass: "dropaccept",
					hoverClass: "drophover",
					drop: function( event, ui ) {
						$(ui.draggable).appendTo(this);
						$('.ui-draggable').draggable('disable');
						$.ajax({
							url: $(ui.draggable).find('a.convert').first().attr('href'),
							data: {event:$(this).parents('.event').data('identity')},
							success:update,
							dataType:'json'
						});
					}
				});
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
