<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_UsersActivity extends HM_View_Infoblock_ScreenForm
{

    protected $id = 'usersActivity';

    public function usersActivity($title = null, $attribs = null, $options = null)
    {
        $this->getFlexGraph();
        $content = $this->view->render('usersActivity.tpl');
        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }

    /**
     * fucking shit! need to be refactored..
     * @return void
     */
    protected function getFlexGraph() {
        $time = time() - 30*24*3600; //дата начала просмотра статистики
        $max = 0;
        //определяем активность
        $sql = "SELECT DISTINCT `MID`, `start` FROM `sessions` WHERE `start` >= '".date('Y-m-d', $time)."'";
        $res = sql($sql);
        $registred = $mids = array();
        while ($row = sqlget($res)) {
            $dummyDate = substr(str_replace('-', '', $row['start']),0,8);
            if (!isset($mids[$dummyDate])) {
                $mids[$dummyDate] = array();
            }
            if (!in_array($row['MID'], $mids[$dummyDate]) || !$registred[$dummyDate]) {
                $mids[$dummyDate][$row['MID']] = $row['MID'];
            ++$registred[$dummyDate];
            }
        }
        //echo $sql;
        //var_dump($registred);
        //exit();
        $sql = "SELECT *
                FROM Students
                WHERE
                    (time_registered >= '".(date('Ymd', $time))."' OR
                    time_registered >= '".(date('Y-m-d', $time))."') AND
                    CID <> 0";
        $res = sql($sql);
        $deps = array();
        while ($row = sqlget($res)) {
            $crntDate = substr(str_replace('-', '', $row['time_registered']), 0, 8);
            $dummyCount = isset($deps[$crntDate][$crntDate]['capacity']) ? ($deps[$crntDate][$crntDate]['capacity']+1) : 1;
            $deps[$crntDate][$crntDate] = array(
                'name'    => '',
                'total'    => $registred[$crntDate],//$row['total'],
                'info'    => '',
                'capacity' => $dummyCount
                );
        }
        //var_dump($deps);
        //exit();

        $complexdArr = array_merge(array_keys($registred), array_keys($deps));
        sort($complexdArr);
        $ret = array();
        $crntDate = date('Ymd', time());
        $regdate  = date('Ymd', $time);
        while ($crntDate >= $regdate) {
            $name  = substr($regdate, 6, 2).'.';
            $name .= substr($regdate, 4, 2).'.';
            $name .= substr($regdate, 0, 4);
            $ret[$regdate][$regdate] = array(
                'name'    => $name,
                'total'    => (int) $registred[$regdate],//$row['total'],
                'info'    => '',
                'capacity' => (int) $deps[$regdate][$regdate]['capacity']
                );
            $max = (int)max($registred[$regdate], $deps[$regdate][$regdate]['capacity'], $max);
            $time += 3600*24;
            $regdate = date('Ymd', $time);
        }



        $fp = fopen($GLOBALS['wwf'].'/temp/3data.xml', 'w');
        fwrite($fp, iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, 'UTF-8', $this->getXML4flexGraph($ret, 0, $max+10)));
        touch($GLOBALS['wwf'].'/temp/3data.xml');

    }

    protected function getXML4flexGraph($deps, $min = 0, $max = 50) {
        //return file_get_contents('mockup.xml');
        $colors['Другое'] = '#E6F3EB';
        $colors['Колледжи'] = '#F3F2E6';
        $colors['ВУЗы'] = '#F3E6F0';

        $xml = domxml_new_doc("1.0");
        $profile_xml = $xml->create_element('profile');
        $profile_xml->set_attribute('mid', $GLOBALS['s']['mid']);
        $profile_xml->set_attribute('color', '#004540');
        $profile_xml->set_attribute('value_min', $min);
        $profile_xml->set_attribute('value_max', $max);

        foreach ($deps as $type=>$info) {
            $cluster_xml = $xml->create_element('cluster');
            $cluster_xml->set_attribute('title', $type);
            $cluster_xml->set_attribute('background-color', $colors[$type]);

            foreach ($info as $courseInfo) {

                $competence_xml = $xml->create_element('competence');
                $competence_xml->set_attribute('title', $courseInfo['name']);

                $cdata_xml = $xml->create_cdata_section($courseInfo['info']);
                $competence_xml->append_child($cdata_xml);

                $profile_required_xml = $xml->create_element('profile_required');
                $profile_required_xml->set_attribute('value', $courseInfo['total']);
                $cdata_xml = $xml->create_cdata_section('');
                $profile_required_xml->append_child($cdata_xml);
                $competence_xml->append_child($profile_required_xml);

                $profile_actual_xml = $xml->create_element('profile_actual');
                $profile_actual_xml->set_attribute('value', $courseInfo['capacity']);
                $cdata_xml = $xml->create_cdata_section('');
                $profile_actual_xml->append_child($cdata_xml);
                $competence_xml->append_child($profile_actual_xml);

                $cluster_xml->append_child($competence_xml);
            }
            $profile_xml->append_child($cluster_xml);

        }

        $labels = $xml->create_element('labels');

        $label = $xml->create_element('label');
        $label->set_attribute('id', 'data1');
        $cdata_xml = $xml->create_cdata_section(_('Количество поданных заявок'));
        $label->append_child($cdata_xml);

        $labels->append_child($label);

        $label = $xml->create_element('label');
        $label->set_attribute('id', 'data2');
        $cdata_xml = $xml->create_cdata_section(_('Количество входов пользователей'));
        $label->append_child($cdata_xml);

        $labels->append_child($label);

/*        $label = $xml->create_element('label');
        $label->set_attribute('id', 'difference');
        $cdata_xml = $xml->create_cdata_section(_('Разность ПЗ-П'));
        $label->append_child($cdata_xml);

        $labels->append_child($label);

        $label = $xml->create_element('label');
        $label->set_attribute('id', 'correlation');
        $cdata_xml = $xml->create_cdata_section(_('Совмещение показателей на одном графике'));
        $label->append_child($cdata_xml);

        $labels->append_child($label);
*/
        $profile_xml->append_child($labels);

        $xml->append_child($profile_xml);
        return $xml->dump_mem(true, 'UTF-8');
    }
}