<?php /**
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

/**
 * This is the model class for table "et_ophtroperationbooking_letter_contact_rule".
 *
 * The followings are the available columns in table:
 * @property integer $id
 * @property integer $contact_type_id
 * @property integer $site_id
 * @property integer $subspecialty_id
 * @property integer $theatre_id
 * @property integer $firm_id
 * @property string $telephone
 *
 * The followings are the available model relations:
 *
 * @property Site $site
 * @property Subspecialty $subspecialty
 * @property OphTrOperationbooking_Operation_Theatre $theatre
 * @property OphTrOperationbooking_Operation_Firm $firm
 */

class OphTrOperationbooking_Letter_Contact_Rule extends BaseActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'ophtroperationbooking_letter_contact_rule';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('parent_rule_id, contact_type_id, site_id, subspecialty_id, theatre_id, firm_id, refuse_telephone, health_telephone, refuse_title, rule_order', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, contact_type_id, site_id, subspecialty_id, theatre_id', 'safe', 'on' => 'search'),
		);
	}
	
	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'site' => array(self::BELONGS_TO, 'Site', 'site_id'),
			'subspecialty' => array(self::BELONGS_TO, 'Subspecialty', 'subspecialty_id'),
			'theatre' => array(self::BELONGS_TO, 'OphTrOperationbooking_Operation_Theatre', 'theatre_id'),
			'firm' => array(self::BELONGS_TO, 'Firm', 'firm_id'),
			'user' => array(self::BELONGS_TO, 'User', 'created_user_id'),
			'usermodified' => array(self::BELONGS_TO, 'User', 'last_modified_user_id'),
			'children' => array(self::HAS_MANY, 'OphTrOperationbooking_Letter_Contact_Rule', 'parent_rule_id'),
			'parent' => array(self::BELONGS_TO, 'OphTrOperationbooking_Letter_Contact_Rule', 'parent_rule_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'parent_rule_id' => 'Parent rule',
			'rule_order' => 'Rule order',
			'site_id' => 'Site',
			'subspecialty_id' => 'Subspecialty',
			'firm_id' => 'Firm',
			'theatre_id' => 'Theatre',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id, true);

		return new CActiveDataProvider(get_class($this), array(
				'criteria' => $criteria,
			));
	}

	public function applies($site_id, $subspecialty_id, $theatre_id, $firm_id) {
		foreach (array('site_id','subspecialty_id','theatre_id','firm_id') as $field) {
			if ($this->{$field} && $this->{$field} != ${$field}) {
				return false;
			}
		}

		return true;
	}

	public function parse($site_id, $subspecialty_id, $theatre_id, $firm_id) {
		foreach ($this->children as $child_rule) {
			if ($child_rule->applies($site_id, $subspecialty_id, $theatre_id, $firm_id)) {
				return $child_rule->parse($site_id, $subspecialty_id, $theatre_id, $firm_id);
			}
		}

		return $this;
	}

	public function findAllAsTree($parent=null, $first=true, $text='text') {
		$tree = array();
		$criteria = new CDbCriteria;
		$criteria->addCondition('parent_rule_id <=> :parent_rule_id');
		$criteria->params[':parent_rule_id'] = $parent ? $parent->id : null;
		$criteria->order = 'rule_order asc';

		if ($first && $parent) {
			$treeItem = array(
				'id' => $parent->id,
				'text' => $parent->$text,
				'children' => $this->findAllAsTree($parent,false,$text),
			);
			$treeItem['hasChildren'] = !empty($treeItem['children']);

			$tree[] = $treeItem;
		} else {
			foreach (OphTrOperationbooking_Letter_Contact_Rule::model()->findAll($criteria) as $rule) {
				$treeItem = array(
					'id' => $rule->id,
					'text' => $rule->$text,
					'children' => $this->findAllAsTree($rule,false,$text),
				);
				$treeItem['hasChildren'] = !empty($treeItem['children']);

				$tree[] = $treeItem;
			}
		}

		return $tree;
	}

	public function getText() {
		return $this->rule_order.': '.CHtml::openTag('a',array('href'=>'#','id'=>'item'.$this->id,'class'=>'treenode')).$this->textPlain.CHtml::closeTag('a')." <a href=\"#\" id=\"add$this->id\"><img width=\"46px\" height=\"23px\" src=\"".Yii::app()->createUrl('/img/_elements/btns/plus-sign.png')."\" /></a>\n";
	}

	public function getTextPlain() {
		$text = '';

		if ($this->site) {
			$text .= "[".$this->site->name."]";
		}

		if ($this->firm) {
			if ($text) $text .= ' ';
			$text .= "[".$this->firm->name."]";
		}

		if ($this->theatre) {
			if ($text) $text .= ' ';
			$text .= "[".$this->theatre->name."]";
		}

		if ($this->subspecialty) {
			if ($text) $text .= ' ';
			$text .= "[".$this->subspecialty->ref_spec."]";
		}

		if ($this->refuse_telephone) {
			if ($text) $text .= ' ';
			$text .= "refuse: [".$this->refuse_telephone."]";
		}

		if ($this->refuse_title) {
			if ($text) $text .= ' ';
			$text .= "title: [".$this->refuse_title."]";
		}

		if ($this->health_telephone) {
			if ($text) $text .= ' ';
			$text .= "health: [".$this->health_telephone."]";
		}

		return $text;
	}

	public function getTreeName() {
		$text = '';

		if ($this->site) {
			$text .= "[".$this->site->name."]";
		}

		if ($this->firm) {
			if ($text) $text .= ' ';
			$text .= "[".$this->firm->name."]";
		}

		if ($this->theatre) {
			if ($text) $text .= ' ';
			$text .= "[".$this->theatre->name."]";
		}

		if ($this->subspecialty) {
			if ($text) $text .= ' ';
			$text .= "[".$this->subspecialty->ref_spec."]";
		}

		if ($this->refuse_telephone) {
			if ($text) $text .= ' ';
			$text .= "refuse: [".$this->refuse_telephone."]";
		}

		if ($this->health_telephone) {
			if ($text) $text .= ' ';
			$text .= "health: [".$this->health_telephone."]";
		}

		$parents = 0;
		$object = $this;

		while ($object->parent_rule_id) {
			$parents++;
			$object = $object->parent;
		}

		return str_repeat('+ ',$parents).$text;
	}

	public function getListAsTree($parent=null) {
		$list = array();

		$criteria = new CDbCriteria;
		$criteria->addCondition('parent_rule_id <=> :parent');
		$criteria->params[':parent'] = $parent ? $parent->id : null;
		$criteria->order = 'rule_order asc';

		foreach (OphTrOperationbooking_Letter_Contact_Rule::model()->findAll($criteria) as $rule) {
			$list[] = $rule;

			foreach ($this->getListAsTree($rule) as $child) {
				$list[] = $child;
			}
		}

		return $list;
	}

	public function delete() {
		if ($this->children) {
			foreach ($this->children as $child) {
				if (!$child->delete()) {
					return false;
				}
			}
		}

		return parent::delete();
	}
}
