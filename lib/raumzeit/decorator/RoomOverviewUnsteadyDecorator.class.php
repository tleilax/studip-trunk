<?
require_once('lib/raumzeit/decorator/Decorator.class.php');

class RoomOverviewUnsteadyDecorator extends Decorator {
	var $sem = NULL;
	var $xml_export = FALSE;
	var $link = FALSE;
	var $showRoomList = FALSE;
	var $hideRooms = FALSE;
	var $onlyRegular = FALSE;
	var $admin_view = FALSE;

	function RoomOverviewUnsteadyDecorator($data) {
		parent::Decorator($data);
	}

	function toString() {
		global $RESOURCES_ENABLE, $TERMIN_TYP;
		$data = $this->undecoratedData;

		$xml = '';

		if (empty($data['regular']['turnus'])) {
			$repeat = 'w�chentlich';
		} else {
			$repeat = '14-t�glich';
		}

		if ($this->link) {
			$ret .= '<table cellspacing="0" cellpadding="1" border="0">';
		}

		$commas = 0;

		if (is_array($data['regular']['turnus_data'])) {
			foreach ($data['regular']['turnus_data'] as $key => $val) {
				$raum_list = '';
				$raum = '';
				$xml_raum = '';
				$xml_raum_freetext = '';
				$zeit = '';

				$zeit = leadingZero($val['start_hour']).':'.leadingZero($val['start_minute']).'-'.leadingZero($val['end_hour']).':'.leadingZero($val['end_minute']);

				if ($RESOURCES_ENABLE) {
					if ($this->showRoomList && $raum = $this->sem->getFormattedPredominantRooms($key, $this->link)) {

					} else if ($zraum = $this->sem->getPredominantRoom($key, TRUE)) {
						foreach ($zraum as $raum_id) {
							$resObj =& ResourceObject::Factory($raum_id);
							if ($this->link) {
								$raum_list[] = $resObj->getFormattedLink(TRUE, TRUE, TRUE);
							} else {
								$raum_list[] = $resObj->getName();
							}
						}

						for ($i = 0; $i < min(3,sizeof($raum_list)); $i++) {
							if ($i) {
								$raum .= ', ';
								$xml_raum .= ', ';
							}

							$raum .= $raum_list[$i];
							$xml_raum .= $raum_list[$i];
						}

						if (sizeof($raum_list) > 3) {
							$info = getWeekDay($val['day']).'.&nbsp;'.$zeit.',&nbsp;'.$repeat.', R�ume:\n';
							$xml_raum = '';
							foreach ($raum_list as $raum_info) {
								$info .= $raum_info.'\n';
								$xml_raum .= $raum_info.', ';
							}
							$info = strip_tags($info);
							$title = str_replace('\n', '  ', $info);

							if ($this->link) {
								$raum .= ", <A href=\"javascript:alert('$info')\" alt=\"$title\" title=\"$title\">und ".(sizeof($raum_list)-3).' weitere</A>';
								$raum .= " <img src=\"{$GLOBALS['ASSETS_URL']}/images/info.gif\" border=\"0\" align=\"absMiddle\" onClick=\"alert('";
								$raum .= $info."')\" alt=\"$title\" title=\"$title\">";
							} else {
								$raum .= ', und '.(sizeof($raum_list)-3).' weitere';
							}
						}

					} else if ($raum = $this->sem->getFreeTextPredominantRoom($key)) {
						if (!$this->xml_export) {
							$raum = '('.$raum.')';
						} else {
							$xml_raum_freetext = $raum;
						}
					} else {
						$raum = _("k.A.");
						$xml_raum_freetext = _("k.A.");
					}
				}

				if ($this->link) {
					$ret .= '<tr><td width="20%" nowrap><font size="-1">'.getWeekDay($val['day']).'.&nbsp;'.$repeat.'&nbsp;</font></td>';
					$ret .= '<td width="20%" nowrap><font size="-1">'.$zeit.'</font></td>';
					if (!$this->hideRooms) {
						$ret .= '<td width="60%"><font size="-1">&nbsp;&nbsp;'.$raum.'</font></td>';
					}
					$ret .= '</tr>';
				} else {
					if ($commas > 0) $ret .= ',<br/>';
					$ret .= getWeekDay($val['day']).'.&nbsp;'.$repeat.'&nbsp;';
					$ret .= $zeit.'&nbsp;';
					if (!$this->hideRooms) {
						$ret .= 'Ort: '.$raum;
					}
					$commas++;
				}

				if ($this->xml_export) {
					$xml .= '<raumzeit>';
					$xml .= "<datum>$repeat</datum>";
					$xml .= '<wochentag>'.getWeekDay($val['day']).'</wochentag>';
					$xml .= "<zeit>$zeit</zeit>";
					$xml .= "<raum><gebucht>$xml_raum</gebucht><freitext>$xml_raum_freetext</freitext></raum>";
					$xml .= '</raumzeit>';
				}
			}
		}

		if (!$this->onlyRegular) {
			// get irregular dates
			$raum = '';
			$zeit = '';
			$sd = '';
			if (is_array($data['irregular'])) {
				// group the singledates
				foreach ($data['irregular'] as $val) {
					$sd[$val['start_time'].'_'.$val['end_time']][] = $val;
				}

				//echo '<pre>', print_r($sd, true), '</pre>';
				foreach ($sd as $termine) {
					$zeit = date('H:i', $termine[0]['start_time']).'-'.date('H:i', $termine[0]['end_time']);
					$zraum = array();
					$xml_zraum = array();
					$xml_zraum_freetext = array();
					$raum = '';
					$xml_raum = '';
					foreach ($termine as $id) {
						if ($RESOURCES_ENABLE && ($id['resource_id'])) {
							$resObj =& ResourceObject::Factory($id['resource_id']);
							if ($this->link) {
								$zraum[] = $resObj->getFormattedLink(TRUE, TRUE, TRUE);
							} else {
								$zraum[] = $resObj->getName();
							}
							$xml_zraum[] = $resObj->getName();
						} else {
							if ($id['raum'] == '') {
								if ($this->admin_view) {
									if ($id['requested_room']) {
										$zraum[] = '<I>(angefragt: '.$id['requested_room'].')</I>';
									} else {
										$zraum[] = _("k.A.");
									}
								} else {
									$zraum[] = _("k.A.");
								}
							} else {
								$zraum[] = '('.$id['raum'].')';
								$xml_zraum_freetext[] = $id['raum'];
							}
						}
					}

					for ($i = 0; $i < min(3,sizeof($zraum)); $i++) {
						if ($i) $raum .= ', ';
						$raum .= $zraum[$i];
					}

					$first_room = true;
					foreach ($xml_zraum as $r) {
						if (!$first_room) $xml_raum .= ', ';
						$xml_raum .= $r;
						$first_room = false;
					}

					$xml_raum_freetext = '';

					$first_room = true;
					foreach ($xml_zraum_freetext as $r) {
						if (!$first_room) $xml_raum_freetext .= ', ';
						$xml_raum_freetext .= $r;
						$first_room = false;
					}

					if (sizeof($termine) > 3) {
						$info = getWeekDay(date('w', $termine[0]['start_time'])).'.&nbsp;'.date('d.m.Y', $termine[0]['end_time']).',&nbsp;'.$zeit.', R�ume:\n';
						foreach ($zraum as $raum_info) {
							$info .= $raum_info.'\n';
						}
						$info = strip_tags($info);
						$title = str_replace('\n', '  ', $info);

						if ($this->link) {
							$raum .= ", <A href=\"javascript:alert('$info')\" alt=\"$title\" title=\"$title\">und ".(sizeof($termine)-3).' weitere</A>';
							$raum .= " <img src=\"{$GLOBALS['ASSETS_URL']}/images/info.gif\" border=\"0\" align=\"absMiddle\" onClick=\"alert('";
							$raum .= $info."')\" alt=\"$title\" title=\"$title\">";
						} else {
							$raum .= ', und '.(sizeof($raum_list)-3).' weitere';
						}
					}

					$typ = '';
					if ($termine[0]['typ'] != 1 && $termine[0]['typ'] != 7) {
						$typ = $GLOBALS['TERMIN_TYP'][$termine[0]['typ']]['name'];
					}

					if ($this->link) {
						$ret .= '<tr><td width="20%" nowrap><font size="-1">'.getWeekDay(date('w', $termine[0]['start_time'])).'. '.date('d.m.Y', $termine[0]['end_time']).'</font>&nbsp;&nbsp;</td>';
						$ret .= '<td width="20%" nowrap><font size="-1">'.$zeit.'</font></td>';
						if (!$this->hideRooms) {
							$ret .= '<td width="60%"><font size="-1">&nbsp;&nbsp;'.$raum.(($typ) ? ", <I>$typ</I>":'').'</font></td></tr>';
						} else {
							$ret .= '<td width="60%"><font size="-1">&nbsp;&nbsp;'.(($typ) ? ", <I>$typ</I>":'').'</font></td></tr>';
						}
					} else {
						if ($commas > 0) $ret .= ',<br/>';
						$ret .= getWeekDay(date('w', $termine[0]['start_time'])).'. '.date('d.m.Y', $termine[0]['end_time']).'&nbsp;';
						$ret .= $zeit.'&nbsp;';
						if (!$this->hideRooms) {
							$ret .= $raum.(($typ) ? ", <I>$typ</I>":'');
						}
						$commas++;
					}

					if ($this->xml_export) {
						$xml .= '<raumzeit>';
						$xml .= '<datum>'.date('d.m.Y', $termine[0]['start_time']).'</datum>';
						$xml .= '<wochentag>'.getWeekDay(date('w', $termine[0]['start_time']), true).'</wochentag>';
						$xml .= "<zeit>$zeit</zeit>";
						$xml .= '<termin_art>'.$typ.'</termin_art>';
						$xml .= "<raum><gebucht>$xml_raum</gebucht><freitext>$xml_raum_freetext</freitext></raum>";
						$xml .= '</raumzeit>';
					}
				}
			}
		}

		if ($this->link) {
			$ret .= '</table>';
		}

		return ($this->xml_export) ? $xml : $ret;
	}
}
?>
