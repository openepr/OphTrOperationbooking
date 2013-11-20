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

class TheatreDiaryController extends BaseModuleController
{
	public $layout='//layouts/main';
	public $renderPatientPanel = false;

	public function accessRules()
	{
		return array(
			array('allow',
				'actions' => array('index', 'search', 'filterFirms', 'filterTheatres', 'filterWards', 'setDiaryFilter', 'getSessionTimestamps', 'checkRequired'),
				'roles' => array('OprnViewClinical'),
			),
			array('allow',
				'actions' => $this->printActions(),
				'roles' => array('OprnPrint'),
			),
			array('allow',
				'actions' => array('saveSession'),
				'roles' => array('OprnEditTheatreSession'),
			),
		);
	}

	/**
	 * @return array
	 * (non-phpdoc)
	 * @see parent::printActions()
	 */
	public function printActions()
	{
		return array('printDiary', 'printList');
	}

	/**
	 * Shows the standard theatre diary list
	 *
	 * @throws CHttpException
	 */
	public function actionIndex()
	{
		//TODO: determine whether we actually need this check
		$firm = Firm::model()->findByPk($this->selectedFirmId);

		if (empty($firm)) {
			// No firm selected, reject
			throw new CHttpException(403, 'You are not authorised to view this page without selecting a firm.');
		}

		$theatres = array();
		$wards = array();

		if (empty($_POST)) {
			// look for values from the session
			$theatre_searchoptions = Yii::app()->session['theatre_searchoptions'];

			if (!empty($theatre_searchoptions)) {
				foreach (Yii::app()->session['theatre_searchoptions'] as $key => $value) {
					$_POST[$key] = $value;
				}

				if (isset($_POST['site-id'])) {
					$wards = $this->getFilteredWards($_POST['site-id']);
					$theatres = $this->getFilteredTheatres($_POST['site-id']);
				}

				if (!isset($_POST['firm-id'])) {
					$_POST['firm-id'] = $theatre_searchoptions['firm-id'] = Yii::app()->session['selected_firm_id'];
					$_POST['subspecialty-id'] = $theatre_searchoptions['subspecialty-id'] = $firm->getSubspecialtyID();
				}

				Yii::app()->session['theatre_searchoptions'] = $theatre_searchoptions;

			} else {
				$_POST = Yii::app()->session['theatre_searchoptions'] = array(
					'firm-id' => Yii::app()->session['selected_firm_id'],
					'subspecialty-id' => $firm->getSubspecialtyID()
				);

				Yii::app()->session['theatre_searchoptions'] = $_POST;
			}

			Audit::add('diary','view');
		} else {
			Audit::add('diary','search',serialize($_POST));
		}

		$this->jsVars['NHSDateFormat'] = Helper::NHS_DATE_FORMAT;

		$this->render('index', array('wards'=>$wards, 'theatres'=>$theatres));
	}

	/**
	 * Print the diary
	 */
	public function actionPrintDiary()
	{
		Audit::add('diary','print',serialize($_POST));

		Yii::app()->getClientScript()->registerCssFile(Yii::app()->createUrl(
			Yii::app()->getAssetManager()->publish(
				Yii::getPathOfAlias('application.modules.'.$this->getModule()->name.'.assets')
			).'/css/module.css'
		));

		$this->renderPartial('_print_diary', array('diary'=>$this->getDiaryTheatres($_POST)), false, true);
	}

	/**
	 * Print the booking list
	 */
	public function actionPrintList()
	{
		Audit::add('diary','print list',serialize($_POST));

		Yii::app()->getClientScript()->registerCssFile(Yii::app()->createUrl(
			Yii::app()->getAssetManager()->publish(
				Yii::getPathOfAlias('application.modules.'.$this->getModule()->name.'.assets')
			).'/css/module.css'
		));

		$this->renderPartial('_print_list', array('bookings'=>$this->getBookingList($_POST)), false, true);
	}

	/**
	 * Ajax action to retrieve diary data
	 */
	public function actionSearch()
	{
		Audit::add('diary','search',serialize($_POST));

		$list = $this->renderPartial('_list', array('diary' => $this->getDiaryTheatres($_POST), 'assetPath'=> $this->assetPath), true, true);

		echo json_encode(array('status'=>'success','data'=>$list));
	}

	/**
	 * Uses $data criteria to retrieve theatre objects that have operations booked
	 * The theatre objects will preload relevant related objects for use in displaying data in the diary layout
	 *
	 * @param $data
	 * @return OphTrOperationbooking_Operation_Theatre[] $theatres
	 */
	public function getDiaryTheatres($data)
	{
		$firmId = Yii::app()->session['selected_firm_id'];

		$data['date-start'] = Helper::convertNHS2MySQL(@$data['date-start']);
		$data['date-end'] = Helper::convertNHS2MySQL(@$data['date-end']);

		if (empty($data['date-start']) || empty($data['date-end'])) {
			$startDate = $endDate = $this->getNextSessionDate($firmId);
		} else {
			$startDate = $data['date-start'];
			$endDate = $data['date-end'];

			if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2})$/',$startDate,$m)) {
				$m[1] = str_pad($m[1],2,0,STR_PAD_LEFT);
				$m[2] = str_pad($m[2],2,0,STR_PAD_LEFT);
				$startDate = "20{$m[3]}-{$m[2]}-{$m[1]}";
			}

			if (preg_match('/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{2})$/',$endDate,$m)) {
				$m[1] = str_pad($m[1],2,0,STR_PAD_LEFT);
				$m[2] = str_pad($m[2],2,0,STR_PAD_LEFT);
				$endDate = "20{$m[3]}-{$m[2]}-{$m[1]}";
			}

			if (!strtotime($startDate) || !strtotime($endDate)) {
				echo json_encode(array('status'=>'error','message'=>'Invalid start and end dates.'));
				Yii::app()->end();
			}

			if (strtotime($endDate) < strtotime($startDate)) {
				list($startDate,$endDate) = array($endDate,$startDate);
			}
		}

		$criteria = new CDbCriteria;

		$criteria->addCondition("date >= :startDate");
		$criteria->addCondition("date <= :endDate");

		$criteria->params = array(
			':startDate' => $startDate,
			':endDate' => $endDate,
		);

		if (@$data['emergency_list']) {
			$criteria->addCondition("firm.id is null");
		} else {
			$criteria->addCondition("firm.id is not null");

			if (@$data['site-id']) {
				$criteria->addCondition("`t`.site_id = :siteId");
				$criteria->params[':siteId'] = $data['site-id'];
			}
			if (@$data['theatre-id']) {
				$criteria->addCondition("theatre.id = :theatreId");
				$criteria->params[':theatreId'] = $data['theatre-id'];
			}
			if (@$data['subspecialty-id']) {
				$criteria->addCondition("subspecialty_id = :subspecialtyId");
				$criteria->params[':subspecialtyId'] = $data['subspecialty-id'];
			}
			if (@$data['firm-id']) {
				$criteria->addCondition("firm.id = :firmId");
				$criteria->params[':firmId'] = $data['firm-id'];
			}
			if (@$data['ward-id']) {
				$criteria->addCondition("ward.id = :wardId");
				$criteria->params[':wardId'] = $data['ward-id'];
			}
		}

		$criteria->addCondition("(event.deleted = :deleted or event.deleted is null) and (episode.deleted = :deleted or episode.deleted is null)");
		$criteria->params[':deleted'] = 0;
		$criteria->order = 'site.short_name, `t`.display_order, `t`.code, sessions.date, sessions.start_time, sessions.end_time, activeBookings.display_order';

		Yii::app()->event->dispatch('start_batch_mode');

		return OphTrOperationbooking_Operation_Theatre::model()
			->with(array(
				'site',
				'sessions' => array(
					'with' => array(
						'activeBookings' => array(
							'with' => array(
								'operation' => array(
									'with' => array(
										'anaesthetic_type',
										'priority',
										'event' => array(
											'with' => array(
												'episode' => array(
													'with' => array(
														'patient' => array(
															'with' => 'contact',
														),
													),
												),
											),
										),
										'procedures',
										'op_usermodified',
										'op_user',
										'eye',
									),
								),
								'ward',
							),
						),
						'firm' => array(
							'with' => array(
								'serviceSubspecialtyAssignment' => array(
									'with' => 'subspecialty',
								),
							),
						),
						'session_user',
						'session_usermodified',
						'theatre',
					),
				),
			))
			->findAll($criteria);
	}

	/**
	 * Get the date of the next session for the given firm id, or return today's date
	 *
	 * @param $firm_id
	 * @return string $date Y-m-d
	 */
	public function getNextSessionDate($firm_id)
	{
		if ($session = OphTrOperationbooking_Operation_Session::getNextSessionForFirmId($firm_id)) {
			return $session->date;
		} else {
			return date('Y-m-d');
		}
	}

	/**
	 * Get bookings for the given selection criteria
	 *
	 * @param $data
	 * @return OphTrOperationbooking_Operation_Booking[] $bookings
	 * @throws Exception
	 */
	public function getBookingList($data)
	{
		foreach (array('date-start', 'date-end', 'subspecialty-id', 'site-id') as $required) {
			if (!isset($data[$required])) {
				throw new Exception('invalid request for booking list');
			}
		}

		$criteria = new CDbCriteria;

		$criteria->addCondition('session.date >= :dateFrom and session.date <= :dateTo');
		$criteria->addInCondition('operation.status_id',array(2,4));

		$criteria->params[':dateFrom'] = Helper::convertNHS2MySQL($data['date-start']);
		$criteria->params[':dateTo'] = Helper::convertNHS2MySQL($data['date-end']);

		if (@$data['emergency_list']) {
			$criteria->addCondition('firm.id IS NULL');
		} else {
			$criteria->addCondition('theatre.site_id = :siteId and subspecialty_id = :subspecialtyId');
			$criteria->params[':siteId'] = $data['site-id'];
			$criteria->params[':subspecialtyId'] = $data['subspecialty-id'];
		}

		if (@$data['ward-id']) {
			$criteria->addCondition('ward.id = :wardId');
			$criteria->params[':wardId'] = $data['ward-id'];
		}

		if (@$data['firm-id']) {
			$criteria->addCondition('firm.id = :firmId');
			$criteria->params[':firmId'] = $data['firm-id'];
		}

		$criteria->addCondition('`t`.booking_cancellation_date is null');

		$criteria->order = 'ward.code, patient.hos_num';

		Yii::app()->event->dispatch('start_batch_mode');

		return OphTrOperationbooking_Operation_Booking::model()
			->with(array(
				'session' => array(
					'with' => array(
						'theatre',
						'firm' => array(
							'with' => array(
								'serviceSubspecialtyAssignment' => array(
									'with' => 'subspecialty',
								),
							),
						),
					),
				),
				'operation' => array(
					'with' => array(
						'event' => array(
							'with' => array(
								'episode' => array(
									'with' => array(
										'patient' => array(
											'with' => 'contact',
										),
									),
								),
							),
						),
					),
				),
				'ward',
			))
			->findAll($criteria);
	}

	/**
	* Generates a firm list based on a subspecialty id provided via POST
	* echoes form option tags for display
	*/
	public function actionFilterFirms()
	{
		if (@$_POST['empty']) {
			echo CHtml::tag('option', array('value'=>''), CHtml::encode('- Firm -'), true);
		} else {
			echo CHtml::tag('option', array('value'=>''), CHtml::encode('All firms'), true);
		}

		if (!empty($_POST['subspecialty_id'])) {
			$subspecialty_id = $_POST['subspecialty_id'];
		} elseif (!empty($_POST['service_id'])) {
			$subspecialty_id = ServiceSubspecialtyAssignment::model()->find('service_id=?',array($_POST['service_id']))->subspecialty_id;
		}

		if (isset($subspecialty_id)) {
			$firms = $this->getFilteredFirms($subspecialty_id);

			foreach ($firms as $id => $name) {
				echo CHtml::tag('option', array('value'=>$id), CHtml::encode($name), true);
			}
		}
	}

	/**
	* Generates a theatre list based on a site id provided via POST
	* echoes form option tags for display
	*/
	public function actionFilterTheatres()
	{
		if (@$_POST['empty']) {
			echo CHtml::tag('option', array('value'=>''), CHtml::encode('- Theatre -'), true);
		} else {
			echo CHtml::tag('option', array('value'=>''), CHtml::encode('All theatres'), true);
		}

		if (!empty($_POST['site_id'])) {
			$theatres = $this->getFilteredTheatres($_POST['site_id']);

			foreach ($theatres as $id => $name) {
				echo CHtml::tag('option', array('value'=>$id), CHtml::encode($name), true);
			}
		}
	}

	/**
	* Generates a theatre list based on a site id provided via POST
	* echoes form option tags for display
	*/
	public function actionFilterWards()
	{
		echo CHtml::tag('option', array('value'=>''), CHtml::encode('All wards'), true);

		if (!empty($_POST['site_id'])) {
			$wards = $this->getFilteredWards($_POST['site_id']);

			foreach ($wards as $id => $name) {
				echo CHtml::tag('option', array('value'=>$id), CHtml::encode($name), true);
			}
		}
	}

	/**
	 * Ajax action to update a session
	 */
	public function actionSaveSession()
	{
		if (!$session = OphTrOperationbooking_Operation_Session::model()->findByPk(@$_POST['session_id'])) {
			throw new Exception('Session not found: '.@$_POST['session_id']);
		}

		$errors = array();
		$bookings = array();

		foreach ($_POST as $key => $value) {
			if (preg_match('/^admitTime_([0-9]+)$/',$key,$m)) {
				if (!$operation = Element_OphTrOperationbooking_Operation::model()->findByPk($m[1])) {
					throw new Exception('Operation not found: '.$m[1]);
				}
				if (!$booking = $operation->booking) {
					throw new Exception('Operation has no active booking: '.$m[1]);
				}
				$booking_data = array(
						'original_display_order' => $booking->display_order,
						'booking_id' => $booking->id,
						'changed' => false,
				);

				// Check to see if the booking has been changed and so needs saving
				$confirmed = @$_POST['confirm_'.$m[1]];
				if ((date('H:i', strtotime($booking->admission_time)) != $value) || $booking->confirmed != $confirmed) {
					$booking_data['changed'] = true;
					$booking->admission_time = $value;
					$booking->confirmed = @$_POST['confirm_'.$m[1]];
				}

				$booking_data['booking'] = $booking;
				$bookings[] = $booking_data;

				if (!$booking->validate()) {
					$formErrors = $booking->getErrors();
					$errors[(integer) $m[1]] = $formErrors['admission_time'][0];
				}
			}
		}

		if (!empty($errors)) {
			echo json_encode($errors);
			return;
		}

		if ($this->checkAccess('OprnEditTheatreSessionDetails')) {
			$session->consultant = $_POST['consultant_'.$session->id];
			$session->paediatric = $_POST['paediatric_'.$session->id];
			$session->anaesthetist = $_POST['anaesthetist_'.$session->id];
			$session->general_anaesthetic = $_POST['general_anaesthetic_'.$session->id];
			$session->available = $_POST['available_'.$session->id];
		}

		$session->comments = $_POST['comments_'.$session->id];

		if (!$session->save()) {
			throw new Exception('Unable to save session: '.print_r($session->getErrors(),true));
		}

		// Create array of booking IDs in the original display order
		$original_bookings = array();
		foreach($bookings as $booking_data) {
			// this is an array [] because it's theoretically possible for bad data to occur where there are multiple bookings with the same display_order
			$original_bookings[$booking_data['original_display_order']][] = $booking_data['booking_id'];
		}
		ksort($original_bookings);

		$original_booking_ids = array();
		foreach ($original_bookings as $original_display_order => $booking_ids) {
			foreach ($booking_ids as $booking_id) {
				$original_booking_ids[] = $booking_id;
			}
		}

		$previous_display_order = -1;
		foreach ($bookings as $new_position => $booking_data) {

			// Check if relative position of booking has changed and adjust display_order as required
			if($booking_data['booking_id'] != $original_booking_ids[$new_position]) {
				$booking_data['booking']->display_order = $previous_display_order +1;
				$booking_data['changed'] = true;
			}
			$previous_display_order = $booking_data['booking']->display_order;

			// Save booking if it has changed
			if ($booking_data['changed']) {
				if (!$booking_data['booking']->save()) {
					throw new Exception('Unable to save booking: '.print_r($booking_data['booking']->getErrors(), true));
				}
			}
		}

		echo json_encode(array());
	}

	/**
	* Helper method to fetch firms by subspecialty ID
	*
	* @param integer $subspecialty_id
	*
	* @return array
	*/
	protected function getFilteredFirms($subspecialty_id)
	{
		$criteria = new CDbCriteria;
		$criteria->addCondition('subspecialty_id = :subspecialty_id');
		$criteria->params[':subspecialty_id'] = $subspecialty_id;
		$criteria->order = 'name';

		$firms = CHtml::listData(Firm::model()->with('serviceSubspecialtyAssignment')->findAll($criteria),'id','name');

		return $firms;
	}

	/**
	* Helper method to fetch theatres by site ID
	*
	* @param integer $site_id
	*
	* @return array
	*/
	protected function getFilteredTheatres($site_id)
	{
		$criteria = new CDbCriteria;
		$criteria->addCondition('site_id = :site_id');
		$criteria->params[':site_id'] = $site_id;
		$criteria->order = 'display_order';

		return CHtml::listData(OphTrOperationbooking_Operation_Theatre::model()->findAll($criteria),'id','name');
	}

	/**
	* Helper method to fetch theatres by site ID
	*
	* @param integer $site_id
	*
	* @return array
	*/
	protected function getFilteredWards($site_id)
	{
		$criteria = new CDbCriteria;
		$criteria->addCondition('site_id = :site_id');
		$criteria->params[':site_id'] = $site_id;
		$criteria->order = 'name';

		return CHtml::listData(OphTrOperationbooking_Operation_Ward::model()->findAll($criteria),'id','name');
	}

	/**
	 * Ajax method to store theatre search options to the session
	 * @TODO: should the keys for this not be validated?
	 */
	public function actionSetDiaryFilter()
	{
		foreach ($_POST as $key => $value) {
			YiiSession::set('theatre_searchoptions',$key,$value);
		}
	}

	/**
	 * Ajax action to retrieve the modification data for a given session
	 */
	public function actionGetSessionTimestamps()
	{
		if (isset($_POST['session_id'])) {
			if ($session = Session::model()->findByPk($_POST['session_id'])) {
				$ex = explode(' ',$session->last_modified_date);
				$last_modified_date = $ex[0];
				$last_modified_time = $ex[1];
				$user = User::model()->findByPk($session->last_modified_user_id);
				echo "Modified on ".Helper::convertMySQL2NHS($last_modified_date)." at ".$last_modified_time." by ".$user->first_name." ".$user->last_name;
			}
		}
	}

	/**
	 * Ajax method to check whether various attributes are required on a given session
	 * (used to prevent them being turned off when they are needed on the session)
	 *
	 * @throws Exception
	 */
	public function actionCheckRequired()
	{
		if (!$session = OphTrOperationbooking_Operation_Session::model()->findByPk(@$_POST['session_id'])) {
			throw new Exception('Session not found: '.$_POST['session_id']);
		}

		Yii::app()->event->dispatch('start_batch_mode');

		switch (@$_POST['type']) {
			case 'consultant':
				$criteria = new CDbCriteria;
				$criteria->addInCondition('`t`.status_id',array(2,4));
				$criteria->addCondition('session.id = :sessionId and booking.booking_cancellation_date is null and `t`.consultant_required = :required');
				$criteria->params[':sessionId'] = $session->id;
				$criteria->params[':required'] = 1;

				if (Element_OphTrOperationbooking_Operation::model()->with(array('booking'=>array('with'=>'session')))->find($criteria)) {
					echo "1";
				} else {
					echo "0";
				}
				return;
			case 'paediatric':
				foreach ($session->activeBookings as $booking) {
					if ($booking->operation->event->episode->patient->isChild($session->date)) {
						echo "1";
						return;
					}
				}
				echo "0";
				return;
			case 'anaesthetist':
				$criteria = new CDbCriteria;
				$criteria->addCondition('session.id = :sessionId and booking.booking_cancellation_date is null and `t`.anaesthetist_required = :required');
				$criteria->addInCondition('`t`.status_id',array(2,4));
				$criteria->params[':sessionId'] = $session->id;
				$criteria->params[':required'] = 1;

				if (Element_OphTrOperationbooking_Operation::model()
					->with(array(
						'booking' => array(
							'with' => 'session',
						),
					))
					->find($criteria)) {
					echo "1";
				} else {
					echo "0";
				}
				return;
			case 'general_anaesthetic':
				$criteria = new CDbCriteria;
				$criteria->addCondition('session.id = :sessionId and booking.booking_cancellation_date is null and `t`.anaesthetic_type_id = :anaestheticType');
				$criteria->addInCondition('`t`.status_id',array(2,4));
				$criteria->params[':sessionId'] = $session->id;
				$criteria->params[':anaestheticType'] = 5;

				if (Element_OphTrOperationbooking_Operation::model()
						->with(array(
							'booking' => array(
								'with' => 'session',
							),
						))
					->find($criteria)) {
					echo "1";
				} else {
					echo "0";
				}
				return;
		}

		throw new Exception('Unknown type: '.@$_POST['type']);
	}
}
