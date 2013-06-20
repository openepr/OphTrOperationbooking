$(document).ready(function() {
	$('#erod_rules li .column_subspecialty, #erod_rules li .column_firms').click(function(e) {
		e.preventDefault();

		if ($(this).parent().attr('data-attr-id')) {
			window.location.href = baseUrl+'/OphTrOperationbooking/admin/editERODRule/'+$(this).parent().attr('data-attr-id');
		}
	});

	$('#et_add_erod_rule').click(function() {
		window.location.href = baseUrl+'/OphTrOperationbooking/admin/addERODRule';
	});

	$('#et_delete_erod_rule').click(function() {
		if ($('input.erod_rules[type="checkbox"]:checked').length == 0) {
			alert("You haven't selected any rules to delete.");
		} else {
			$.ajax({
				'type': 'POST',
				'url': baseUrl+'/OphTrOperationbooking/admin/deleteERODRules',
				'data': $('#erod_rules').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
				'success': function(resp) {
					if (resp == "1") {
						window.location.reload();
					} else {
						alert("Something went wrong trying to delete the rules. Please try again or contact support for assistance.");
					}
				}
			});
		}
	});

	$('#lcr_site_id').change(function() {
		var siteId = $(this).val();

		$('#rules li').children('a').removeAttr('style');

		if ($('#lcr_theatre_id').length >0) {
			$.ajax({
				'type': 'POST',
				'data': {'site_id': siteId, 'YII_CSRF_TOKEN': YII_CSRF_TOKEN, 'empty': 1},
				'url': baseUrl+'/OphTrOperationbooking/theatreDiary/filterTheatres',
				'success':function(data) {
					$('#lcr_theatre_id').html(data);
				}
			});
		} else {
			OphTrOperationbooking_showMatchingRule();
		}
	});

	$('#lcr_subspecialty_id').change(function() {
		var subspecialtyId = $(this).val();

		$('#rules li').children('a').removeAttr('style');

		$.ajax({
			'url': baseUrl+'/OphTrOperationbooking/theatreDiary/filterFirms',
			'type': 'POST',
			'data': 'subspecialty_id='+subspecialtyId+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN+"&empty=1",
			'success': function(data) {
				$('#lcr_firm_id').html(data);
			}
		});
	});

	$('#lcr_service_id').change(function() {
		var service_id = $(this).val();

		$('#rules li').children('a').removeAttr('style');

		$.ajax({
			'url': baseUrl+'/OphTrOperationbooking/theatreDiary/filterFirms',
			'type': 'POST',
			'data': 'service_id='+service_id+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN+"&empty=1",
			'success': function(data) {
				$('#lcr_firm_id').html(data);
			}
		});
	});

	$('#lcr_firm_id, #lcr_theatre_id, #lcr_is_child, #lcr_rule_type_id').change(function() {
		$('#rules li').children('a').removeAttr('style');

		OphTrOperationbooking_showMatchingRule();
	});

	$('#rules a.treenode').click(function() {
		var id = $(this).attr('id').match(/[0-9]+/);
		window.location.href = baseUrl+'/OphTrOperationbooking/admin/edit'+OE_rule_model+'/'+id;
	});

	$('#et_add_letter_contact_rule').click(function() {
		window.location.href = baseUrl+'/OphTrOperationbooking/admin/add'+OE_rule_model;
	});

	$('#rules a.addTreeItemHere').click(function(e) {
		e.preventDefault();
		window.location.href = baseUrl+'/OphTrOperationbooking/admin/add'+OE_rule_model+'?parent_rule_id='+$(this).attr('rel');
	});

	$('#et_add_operation_name_rule').click(function() {
		window.location.href = baseUrl+'/OphTrOperationbooking/admin/addOperationNameRule';
	});

	$('#operation_name_rules li .column_theatre, #operation_name_rules li .column_name').click(function(e) {
		e.preventDefault();

		if ($(this).parent().attr('data-attr-id')) {
			window.location.href = baseUrl+'/OphTrOperationbooking/admin/editOperationNameRule/'+$(this).parent().attr('data-attr-id');
		}
	});

	$('#et_delete_operation_name_rule').click(function() {
		if ($('input.operation_name_rules[type="checkbox"]:checked').length == 0) {
			alert("You haven't selected any rules to delete.");
		} else {
			$.ajax({
				'type': 'POST',
				'url': baseUrl+'/OphTrOperationbooking/admin/deleteOperationNameRules',
				'data': $('#operation_name_rules').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
				'success': function(resp) {
					if (resp == "1") {
						window.location.reload();
					} else {
						alert("Something went wrong trying to delete the rules. Please try again or contact support for assistance.");
					}
				}
			});
		}
	});

	$('#admin_sequences li.even .column_firm, #admin_sequences li.even .column_theatre, #admin_sequences li.even .column_dates, #admin_sequences li.even .column_time, #admin_sequences li.even .column_interval, #admin_sequences li.even .column_weekday, #admin_sequences li.even .column_attributes, #admin_sequences li.odd .column_firm, #admin_sequences li.odd .column_theatre, #admin_sequences li.odd .column_dates, #admin_sequences li.odd .column_time, #admin_sequences li.odd .column_interval, #admin_sequences li.odd .column_weekday, #admin_sequences li.odd .column_attributes').click(function(e) {
		e.preventDefault();

		if ($(this).parent().attr('data-attr-id')) {
			window.location.href = baseUrl+'/OphTrOperationbooking/admin/editSequence/'+$(this).parent().attr('data-attr-id');
		}
	});

	handleButton($('#et_add_sequence'),function(e) {
		e.preventDefault();
		window.location.href = baseUrl+'/OphTrOperationbooking/admin/addSequence';
	});
});

function OphTrOperationbooking_showMatchingRule() {
	if ($('#lcr_site_id').val() != '' && $('#lcr_firm_id').val() != '') {
		// only require these if they're in the dom
		if ($('#lcr_is_child').length >0 && $('#lcr_is_child').val() == '') return;
		if ($('#lcr_rule_type_id').length >0 && $('#lcr_rule_type_id').val() == '') return;
		if ($('#lcr_subspecialty_id').length >0 && $('#lcr_subspecialty_id').val() == '') return;
		if ($('#lcr_theatre_id').length >0 && $('#lcr_theatre_id').val() == '') return;
		if ($('#lcr_service_id').length >0 && $('#lcr_service_id').val() == '') return;

		$.ajax({
			'type': 'POST',
			'url': baseUrl+'/OphTrOperationbooking/admin/test'+OE_rule_model+'s',
			'data': $('#rulestest').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
			'dataType': 'json',
			'success': function(resp) {
				for (var i in resp) {
					$('#rules li[id="'+resp[i]+'"]').children('a').attr('style','color: #f00');
				}
			}
		});
	}
}
