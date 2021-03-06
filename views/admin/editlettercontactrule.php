<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2012
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2012, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

?>
<div class="box admin">
	<h2><?php echo $rule->id ? 'Edit' : 'Add'?> letter contact rule</h2>
	<?php
	$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
			'id'=>'adminform',
			'enableAjaxValidation'=>false,
			'focus'=>'#contactname',
			'layoutColumns' => array(
				'label' => 2,
				'field' => 5
			)
		))?>
	<?php echo $form->errorSummary($rule); ?>
	<?php echo $form->dropDownList($rule,'parent_rule_id',CHtml::listData(OphTrOperationbooking_Letter_Contact_Rule::model()->getListAsTree(),'id','treeName'),array('empty'=>'- None -'))?>
	<?php echo $form->textField($rule,'rule_order',array(),array(),array('field'=>2))?>
	<?php echo $form->dropDownList($rule,'site_id',Site::model()->getListForCurrentInstitution('name'),array('empty'=>'- Not set -'))?>
	<?php echo $form->dropDownList($rule,'firm_id',Firm::model()->getListWithSpecialties(),array('empty'=>'- Not set -'))?>
	<?php echo $form->dropDownList($rule,'subspecialty_id',CHtml::listData(Subspecialty::model()->findAllByCurrentSpecialty(),'id','name'),array('empty'=>'- Not set -'))?>
	<?php echo $form->dropDownList($rule,'theatre_id','OphTrOperationbooking_Operation_Theatre',array('empty'=>'- Not set -'))?>
	<?php echo $form->textField($rule,'refuse_telephone',array('size'=>20))?>
	<?php echo $form->textField($rule,'refuse_title',array('size'=>90))?>
	<?php echo $form->textField($rule,'health_telephone',array('size'=>90))?>
	<?php if ($rule->children) {?>
		<div class="row field-row">
			<div class="large-<?php echo $form->layoutColumns['label'];?> column">
				<div class="field-label">
					Descendants:
				</div>
			</div>
			<div class="large-<?php echo (12 - $form->layoutColumns['label']);?> column">
				<div class="panel" style="margin:0">
					<?php
						$this->widget('CTreeView',array(
							'data' => OphTrOperationbooking_Letter_Contact_Rule::model()->findAllAsTree($rule,true,'textPlain'),
						));
					?>
				</div>
			</div>
		</div>
	<?php }?>

	<?php echo $form->errorSummary($rule); ?>
	<?php echo $form->formActions(array(
		'delete' => $rule->id ? 'Delete' : false
	));?>
	<?php $this->endWidget()?>
</div>

<script type="text/javascript">
	handleButton($('#et_cancel'),function() {
		window.location.href = baseUrl+'/OphTrOperationbooking/admin/view'+OE_rule_model+'s';
	});
	handleButton($('#et_save'),function() {
		$('#adminform').submit();
	});
	handleButton($('#et_delete'),function(e) {
		e.preventDefault();
		window.location.href = baseUrl+'/OphTrOperationbooking/admin/delete'+OE_rule_model+'/<?php echo $rule->id?>';
	});
</script>
