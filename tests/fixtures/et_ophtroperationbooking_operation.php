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

return array(
	'eo1' => array(
		'event_id' => 1,
		'eye_id' => 1,
		'consultant_required' => 1,
		'anaesthetic_type_id' => 1,
		'overnight_stay' => 0,
		'site_id' => 1,
		'priority_id' => 1,
		'decision_date' => date('Y-m-d'),
		'comments' => 'Test comments',
		'total_duration' => 100,
		'status_id' => 5,
		'anaesthetist_required' => 0,
		'operation_cancellation_date' => null,
		'created_date' => date('Y-m-d 00:00:00'),
		'last_modified_date' => date('Y-m-d 00:00:00'),
	),
	'eo2' => array(
		'event_id' => 2,
		'eye_id' => 1,
		'consultant_required' => 1,
		'anaesthetic_type_id' => 1,
		'overnight_stay' => 0,
		'site_id' => 1,
		'priority_id' => 1,
		'decision_date' => date('Y-m-d'),
		'comments' => 'Test comments',
		'total_duration' => 100,
		'status_id' => 1,
		'anaesthetist_required' => 0,
		'operation_cancellation_date' => null,
		'created_date' => date('Y-m-d 00:00:00'),
		'last_modified_date' => date('Y-m-d 00:00:00'),
	),
	'eo3' => array(
		'event_id' => 3,
		'eye_id' => 1,
		'consultant_required' => 1,
		'anaesthetic_type_id' => 2,
		'overnight_stay' => 0,
		'site_id' => 1,
		'priority_id' => 1,
		'decision_date' => date('Y-m-d'),
		'comments' => 'Test comments',
		'total_duration' => 100,
		'status_id' => 1,
		'anaesthetist_required' => 1,
		'operation_cancellation_date' => null,
		'created_date' => date('Y-m-d 00:00:00'),
		'last_modified_date' => date('Y-m-d 00:00:00'),
	),
	'eo4' => array(
		'event_id' => 4,
		'eye_id' => 1,
		'consultant_required' => 1,
		'anaesthetic_type_id' => 2,
		'overnight_stay' => 0,
		'site_id' => 1,
		'priority_id' => 1,
		'decision_date' => date('Y-m-d'),
		'comments' => 'Test comments',
		'total_duration' => 100,
		'status_id' => 1,
		'anaesthetist_required' => 1,
		'operation_cancellation_date' => null,
		'created_date' => date('Y-m-d 00:00:00'),
		'last_modified_date' => date('Y-m-d 00:00:00'),
	),
	'eo5' => array(
		'event_id' => 5,
		'eye_id' => 1,
		'consultant_required' => 1,
		'anaesthetic_type_id' => 5,
		'overnight_stay' => 0,
		'site_id' => 1,
		'priority_id' => 1,
		'decision_date' => date('Y-m-d'),
		'comments' => 'Test comments',
		'total_duration' => 100,
		'status_id' => 1,
		'anaesthetist_required' => 1,
		'operation_cancellation_date' => null,
		'created_date' => date('Y-m-d 00:00:00'),
		'last_modified_date' => date('Y-m-d 00:00:00'),
	),
	'eo6' => array(
		'event_id' => 6,
		'eye_id' => 1,
		'consultant_required' => 1,
		'anaesthetic_type_id' => 5,
		'overnight_stay' => 0,
		'site_id' => 1,
		'priority_id' => 1,
		'decision_date' => date('Y-m-d'),
		'comments' => 'Test comments',
		'total_duration' => 100,
		'status_id' => 1,
		'anaesthetist_required' => 1,
		'operation_cancellation_date' => null,
		'created_date' => date('Y-m-d 00:00:00'),
		'last_modified_date' => date('Y-m-d 00:00:00'),
		'rtt_id' => 1,
		'comments_rtt' => 'these are RTT comments',
		'referral_id' => 1,
		'latest_booking_id' => 2,
	),
);