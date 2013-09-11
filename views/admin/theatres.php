<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
?>
<div class="report curvybox white">
	<div class="reportInputs">
		<h3 class="georgia">Theatres</h3>
		<div>
			<form id="theatres">
				<ul class="grid reduceheight">
					<li class="header">
						<span class="column_checkbox"><input type="checkbox" id="checkall" class="theatres" /></span>
						<span class="column_site">Site</span>
						<span class="column_name">Name</span>
						<span class="column_code">Code</span>
						<span class="column_ward">Ward</span>
					</li>
					<div class="sortable">
						<?php
						$criteria = new CDbCriteria;
						$criteria->order = "display_order asc";
						foreach (OphTrOperationbooking_Operation_Theatre::model()->findAll() as $i => $theatre) {?>
							<li class="<?php if ($i%2 == 0) {?>even<?php } else {?>odd<?php }?>" data-attr-id="<?php echo $theatre->id?>">
								<span class="column_checkbox"><input type="checkbox" name="theatre[]" value="<?php echo $theatre->id?>" class="theatres" /></span>
								<span class="column_site"><?php echo $theatre->site->name?></span>
								<span class="column_name"><?php echo $theatre->name?></span>
								<span class="column_code"><?php echo $theatre->code?></span>
								<span class="column_ward"><?php echo $theatre->ward ? $theatre->ward->name : 'None'?></span>
							</li>
						<?php }?>
					</div>
				</ul>
			</form>
		</div>
	</div>
</div>
<div>
	<?php echo EventAction::button('Add', 'add_theatre', array('colour' => 'blue'))->toHtml()?>
	<?php echo EventAction::button('Delete', 'delete_theatre', array('colour' => 'blue'))->toHtml()?>
</div>
<div id="confirm_delete_theatres" title="Confirm delete theatre" style="display: none;">
	<div>
		<div id="delete_theatres">
			<div class="alertBox" style="margin-top: 10px; margin-bottom: 15px;">
				<strong>WARNING: This will remove the theatres from the system.<br/>This action cannot be undone.</strong>
			</div>
			<p>
				<strong>Are you sure you want to proceed?</strong>
			</p>
			<div class="buttonwrapper" style="margin-top: 15px; margin-bottom: 5px;">
				<input type="hidden" id="medication_id" value="" />
				<button type="submit" class="classy red venti btn_remove_theatres"><span class="button-span button-span-red">Remove theatre(s)</span></button>
				<button type="submit" class="classy green venti btn_cancel_remove_theatres"><span class="button-span button-span-green">Cancel</span></button>
				<img class="loader" src="<?php echo Yii::app()->createUrl('img/ajax-loader.gif')?>" alt="loading..." style="display: none;" />
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	handleButton($('#et_delete_theatre'),function(e) {
		e.preventDefault();

		if ($('input[type="checkbox"][name="theatre[]"]:checked').length <1) {
			new OpenEyes.Dialog.Alert({
				content: "Please select the theatre(s) you wish to delete."
			}).open();
			enableButtons();
			return;
		}

		$.ajax({
			'type': 'POST',
			'url': baseUrl+'/OphTrOperationbooking/admin/verifyDeleteTheatres',
			'data': $('form#theatres').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
			'success': function(resp) {
				if (resp == "1") {
					enableButtons();

					if ($('input[type="checkbox"][name="theatre[]"]:checked').length == 1) {
						$('#confirm_delete_theatres').attr('title','Confirm delete theatre');
						$('#delete_theatres').children('div').children('strong').html("WARNING: This will remove the theatre from the system.<br/><br/>This action cannot be undone.");
						$('button.btn_remove_theatres').children('span').text('Remove theatre');
					} else {
						$('#confirm_delete_theatres').attr('title','Confirm delete theatres');
						$('#delete_theatres').children('div').children('strong').html("WARNING: This will remove the theatres from the system.<br/><br/>This action cannot be undone.");
						$('button.btn_remove_theatres').children('span').text('Remove theatres');
					}

					$('#confirm_delete_theatres').dialog({
						resizable: false,
						modal: true,
						width: 560
					});
				} else {
					new OpenEyes.Dialog.Alert({
						content: "One or more of the selected theatres have active future bookings and so cannot be deleted."
					}).open();
					enableButtons();
				}
			}
		});
	});

	$('button.btn_cancel_remove_theatres').click(function(e) {
		e.preventDefault();
		$('#confirm_delete_theatres').dialog('close');
	});

	handleButton($('button.btn_remove_theatres'),function(e) {
		e.preventDefault();

		// verify again as a precaution against race conditions
		$.ajax({
			'type': 'POST',
			'url': baseUrl+'/OphTrOperationbooking/admin/verifyDeleteTheatres',
			'data': $('form#theatres').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
			'success': function(resp) {
				if (resp == "1") {
					$.ajax({
						'type': 'POST',
						'url': baseUrl+'/OphTrOperationbooking/admin/deleteTheatres',
						'data': $('form#theatres').serialize()+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
						'success': function(resp) {
							if (resp == "1") {
								window.location.reload();
							} else {
								new OpenEyes.Dialog.Alert({
									content: "There was an unexpected error deleting the theatres, please try again or contact support for assistance",
									onClose: function() {
										enableButtons();
										$('#confirm_delete_theatres').dialog('close');
									}
								}).open();
							}
						}
					});
				} else {
					new OpenEyes.Dialog.Alert({
						content: "One or more of the selected theatres now have active future bookings and so cannot be deleted.",
						onClose: function() {
							enableButtons();
							$('#confirm_delete_theatres').dialog('close');
						}
					}).open();
				}
			}
		});
	});
</script>