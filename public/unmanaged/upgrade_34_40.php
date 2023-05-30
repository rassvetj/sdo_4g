<?php
include("1.php");
define("HARDCODE_WITHOUT_SESSION", true);
include "../../application/cmd/cmdBootstraping.php";

switch ($_GET['case']) {
	case 'copy_resources':
	    require_once(dirname(__FILE__) . '/../../library/HM/Model/Abstract.php');
	    require_once(dirname(__FILE__) . '/../../application/model/HM/Files/FilesModel.php');
	    $cids = explode(',', $_GET['cids']);
	    foreach ($cids as $cid) {
    		$sql = "
                SELECT
                library_parent.bid as dest,
                (CASE WHEN library_child.bid IS NULL THEN library_parent.filename ELSE library_child.filename END) AS src
                FROM library library_parent LEFT JOIN library library_child ON (library_parent.bid = library_child.parent AND library_child.is_active_version=1)
                WHERE library_parent.cid='{$cid}' ORDER BY library_parent.bid
    		";
    		$res = sql($sql);
    		while ($row = sqlget($res)) {
    		    $src = dirname(__FILE__) . '/library/' . $row['src'];
    		    if (file_exists($src)) {
        		    $size = filesize($src);
        			copy($src, dirname(__FILE__) . '/../../data/upload/resource/' . $row['dest']);
        		    $size_string = ($size > 512)?(  ($size/1024 > 512)  ?sprintf("%.02f MB",($size/1024)/1024)  :sprintf("%.02f kB",$size/1024))  :sprintf("%d B",$size);
        		    $filetype = HM_Files_FilesModel::getFileType($src);
                    sql("UPDATE resources SET volume='{$size_string}', filetype='{$filetype}' WHERE resource_id='{$row['dest']}'");
        //            unlink($src);
        		}
    		}
		}
		break;
//Копирование материалов не привязанных к курсам из библиотеки 32 34 в информационные ресурсы 40
    case 'copy_from_library_to_resources_32_40':
        require_once(dirname(__FILE__) . '/../../library/HM/Model/Abstract.php');
	    require_once(dirname(__FILE__) . '/../../application/model/HM/Files/FilesModel.php');
		$bid = $_GET['bid'] ? " AND library_parent.bid IN ('".implode("','",explode(',',$_GET['bid']))."')":"";
        $sql = "
                SELECT
                library_parent.bid as dest, library_parent.title as title, library_parent.author as author, library_parent.publisher as publisher,
                library_parent.publish_date as publish_date, library_parent.description as description, library_parent.upload_date,
                library_parent.is_active_version as is_active_version, library_parent.filename as src, library_parent.mid as mid
                FROM library library_parent
                WHERE library_parent.cid='0' $bid ORDER BY library_parent.bid
    		";
    		$res = sql($sql);
    		while ($row = sqlget($res)) {
    		    $src = dirname(__FILE__) . '/library/' . $row['src'];
    		    if (file_exists($src)) {
        		    $size = filesize($src);
        			copy($src, dirname(__FILE__) . '/../../data/upload/resource/' . $row['dest']);
        		    $size_string = ($size > 512)?(  ($size/1024 > 512)  ?sprintf("%.02f MB",($size/1024)/1024)  :sprintf("%.02f kB",$size/1024))  :sprintf("%d B",$size);
        		    $filetype = HM_Files_FilesModel::getFileType($src);
                    $row['src']=str_replace("/".$row['dest']."/",'',$row['src']);
                    sql("INSERT INTO resources (resource_id,title,url,volume,filename,type,filetype,description,content,created,updated,created_by,services,subject_id,status,location)
                         VALUE ('{$row['dest']}','".$row['title']."(".$row['author'].")','0','{$size_string}','{$row['src']}','0','{$filetype}',
                         'Издатель: ".$row['publisher']."<br>Дата: ".$row['publisher_date']."<br>Описание: ".$row['description']."','',
                         '{$row['upload_date']}','{$row['upload_date']}','{$row['mid']}','0','0','{$row['is_active_version']}',1)");
        		}else{
				  echo 'error file not found : bid-'.$row['dest'].'title - '.$row['title'];
				}
    		}
          die('done');
        break;
//Перенос специальностей из 34 в электронный деканат 4g
   case "migration_edo_dev_from_34_40":
      class migration_edo_dev{
         public $specialities=array();
         public $subjects=array();
         public $schedules=array();
         public $training_periods=array();
         public $base_curriculums=array();
         public $curriculums=array();
         public $training_period_id=1;
         public $curriculums_subjects=array();
         public $cur_subjects_ref_base_cur=array();
         public $cur_subjects_ref_courses=array();
         public $students=array();
         public $teacher=array();
         public $stuff_union=array();
         public $subjects_staffing=array();
         public $workload_types=array();
         public $workloads=array();
         public $groups=array();
         public $groups_student=array();
         public $subjects_courses=array();
         public $subjects_con_courses=array();
         public $students_subjects=array();
         public $subid=array();
         public $teacher_courses=array();
         public $study_id=1;

         public $tabels=array('edo_base_curriculums','edo_current_controls','edo_curriculums','edo_curriculums_subjects',
                              'edo_cur_subjects_ref_base_cur','edo_cur_subjects_ref_courses','edo_schedules','edo_specialities',
                              'edo_staff_units','edo_students','edo_subjects','edo_subjects_staffing','edo_teachers','edo_training_periods',
                              'edo_workloads','edo_workload_types','edo_groups','edo_groups_students');

            function __construct(){
              $this->select_data();
              $this->insert_data();
            }

            function select_data(){
                $this->clear_db();
                $this->select_study_id();
                $this->select_specialities();
                $this->select_subjects();
                $this->select_schedules();
                $this->select_curriculums();
                $this->select_stuff_union_teacher();
          //      $this->select_students();
          //      $this->select_teacher();
          //      $this->select_groups();
            }

            function insert_data(){
                $this->clear_db();
                $this->insert_db();
            }
            function select_specialities(){
               $res=sql("SELECT * FROM tracks");
               if(sqlrows($res)){
                   while($row=sqlget($res)){
                       $this->specialities[$row['trid']]=array('speciality_id'=>$row['trid'],
                                                               'name'=>$row['name'],
                                                               'speciality_code'=>$row['id'],
                                                               'full_name'=>$row['id']." ".$row['name'],
                                                               'qualification_id'=>'1',
                                                               'qualification_in_diploma'=>_('бакалавр'),
                                                               'subdivision_id'=>'43',
                                                               'annotation'=>$row['description'],
                                                               );
                   }
               }
            }

            function select_subjects(){
                 $res=sql("SELECT tracks2course.CID,Courses.Title FROM tracks2course
                           INNER JOIN Courses ON (Courses.CID=tracks2course.CID)
                           GROUP BY Courses.CID");
                 if(sqlrows($res)){
                     while($row=sqlget($res)){
                         $this->subjects[$row['CID']]=array('subject_id'=>$row['CID'],
                                                            'designation'=>$row['Title'],
                                                            'name'=>$row['Title']);
                     }
                 }
            }

            function filter_key_array($key,$array){
                if(is_array($array)&&count($array)){
                     if(array_key_exists($key,$array)){
                              return false;
                     }
                }
                return true;
            }

            function select_schedules(){
               $res=sql("SELECT trid,number_of_levels FROM tracks");
                if(sqlrows($res)){
                    while($row=sqlget($res)){
                         $this->update_schedules($row['number_of_levels']);
                    }
                }
            }

            function update_schedules($level){
                $schedule_id=count($this->schedules);
                for($i=1;$i<=(int) $level;$i++){
                    if($this->filter_key_array($level.'-'.$i,$this->schedules)){
                        $this->schedules[$level.'-'.$i]=array('schedule_id'=>$schedule_id+$i,
                                                             'designation'=>_(' Семестров всего ').$level._(' (семестр ').$i.')',
                                                             'start_year'=>date("Y"),
                                                             'end_year'=>date("Y"),
                                                             'training_time'=>'1',
                                                             'state'=>'2',
                                                             'blocked'=>'0',
                                                             'training_period'=>$this->training_period_id
                                                             );
                        $this->select_training_period($schedule_id+$i,$level);
                    }
                }
            }

            function select_training_period($speciality_id,$level){
                 for($i=1;$i<=(int) $level;$i++){
                       $this->training_periods[$this->training_period_id]=array(
                                                        'training_period_id'=>$this->training_period_id,
                                                        'designation'=>_('Семестр ').$i,
                                                        'name'=>_('Семестр ').$i,
                                                        'schedule_id'=>$speciality_id,
                                                        'year'=>'1',
                                                        'start_date'=>date("Y-m-d h:i:s"),
                                                        'end_date'=>date("Y-m-d h:i:s"),
                                                        'training_time'=>'1',
                                                        'lessons_grid_id'=>'3',
                                                        );
                      $this->training_period_id++;
                 }
            }

            function select_curriculums(){
                foreach($this->specialities as $id=>$v){
                     $this->select_base_curriculums($v);
                     $this->select_main_curriculums($v);
                }
            }



            function select_base_curriculums($v){
                 $this->base_curriculums[$v['speciality_id']]=array('base_curriculum_id'=>$v['speciality_id'],
                                                    'designation'=>$v['name'],
                                                    'name'=>$v['name'],
                                                    'study_id'=>$this->study_id,
                                                    'speciality_id'=>$v['speciality_id'],
                                                    'blocked'=>'0',
                                                    'state'=>'2'
                                                   );
                 $this->select_base_curriculums_subjects($v['speciality_id']);
            }

            function select_study_id(){
                $res=sql("SELECT study_id FROM edo_studies LIMIT 1 ORDER BY study_id");
                $row=sqlget($res);
                $this->study_id=$row['study_id'];
            }

            function select_base_curriculums_subjects($id){
                if(!count($this->curriculums_subjects)) $i=1;
                else $i=count($this->curriculums_subjects);
                $res=sql("SELECT Courses.Title,Courses.CID FROM tracks2course
                          INNER JOIN Courses ON (Courses.CID=tracks2course.cid)
                          WHERE tracks2course.trid='$id'
                          GROUP BY Courses.CID");
                if(sqlrows($res)){
                    while($row=sqlget($res)){
                        $this->curriculums_subjects[$i]=array('curriculum_subject_id'=>$i,
                                                            'base_curriculum_subject_id'=>$id,
                                                            'curriculum_id'=>'0',
                                                            'subjects_cycle_id'=>'5',
                                                            'training_component_id'=>'1',
                                                            'code'=>'0',
                                                            'subject_id'=>$row['CID'],
                                                            'full_name'=>$row['Title'],
                                                            'training_duration'=>'1',
                                                            'planned_training_load'=>'1',
                                                            'diploma_mark'=>'2',
                                                            'lecture_room_id'=>'null',
                                                            'room_training_load'=>'1');
                        $this->cur_subjects_ref_base_cur[]=array('curriculum_subject_id'=>$i,
                                                                    'base_curriculum_id'=>$id);
                        $i++;
                    }
                }
            }

            function select_main_curriculums($v){
               $curriculum_id=count($this->curriculums);
               $res=sql("SELECT number_of_levels from tracks WHERE trid='".$v['speciality_id']."' ");
                if(sqlrows($res)){
                   $row=sqlget($res);
                   for($i=1;$i<=(int) $row['number_of_levels'];$i++){
                      $this->curriculums[$v['speciality_id'].'-'.$i]=array('curriculum_id'=>$curriculum_id+$i,
                                                                           'designation'=>$v['name']._(' период ').$i,
                                                                           'study_id'=>$this->study_id,
                                                                           'name'=>$v['name']._(' период ').$i,
                                                                           'speciality_id'=>$v['speciality_id'],
                                                                           'schedule_id'=>$this->schedules[$row['number_of_levels'].'-'.$i]['schedule_id'],
                                                                           'training_period_id'=>$this->select_training_period_id($row['number_of_levels'].'-'.$i,$i),
                                                                           'base_curriculum_id'=>$v['speciality_id'],
                                                                           'estimation_scale_id'=>'1',
                                                                           'ects_coefficient'=>'36',
                                                                           'blocked'=>'0',
                                                                           'state'=>'2');
                      $this->select_main_curriculums_subjects($v['speciality_id'],$i,$curriculum_id+$i,$v['name'],$row['number_of_levels']);
                      $this->select_groups($v['speciality_id'],$curriculum_id+$i,$i);
                      $this->select_students($v['speciality_id'],$curriculum_id+$i,$i);
                   }
                }
            }

            function select_main_curriculums_subjects($id,$level,$curriculum_id,$name,$number_of_levels){
                $i=count($this->curriculums_subjects)+1;
                $res=sql("SELECT Courses.Title,Courses.CID FROM tracks2course
                          INNER JOIN Courses ON (Courses.CID=tracks2course.cid)
                          WHERE tracks2course.trid='$id'
                          GROUP BY Courses.CID");
                if(sqlrows($res)){
                    while($row=sqlget($res)){
                         $this->curriculums_subjects[$i]=array('curriculum_subject_id'=>$i,
                                                               'base_curriculum_subject_id'=>'0',
                                                               'curriculum_id'=>$curriculum_id,
                                                               'subjects_cycle_id'=>'5',
                                                               'training_component_id'=>'1',
                                                               'code'=>'0',
                                                               'subject_id'=>$row['CID'],
                                                               'full_name'=>$row['Title'].'('.$name.')'._(' (период ').$level.')',
                                                               'training_duration'=>'1',
                                                               'planned_training_load'=>'1',
                                                               'diploma_mark'=>'2',
                                                               'lecture_room_id'=>'null',
                                                               'room_training_load'=>'1');

                         $this->select_cur_subjects_ref_courses($i,$level,($this->select_subjects_courses($row['CID'],$level,$name,$id)),$row['CID'],$id,$number_of_levels);
                         $i++;
                    }
                }

            }

            function select_subjects_courses($cid,$level,$name,$curriculum_id){
                if(!isset($this->subid['this'])){
                    $res=sql("SELECT subid FROM subjects ORDER BY subid DESC LIMIT 1 ");
                    $row=sqlget($res);
                    $this->subid=array('first'=>$row['subid'],
                                       'this'=>$row['subid']);
                    sqlfree($res);
                }
                $res=sql("SELECT * FROM subjects WHERE subid='$cid'");
                $row=sqlget($res);
                $this->subid['this']++;
                $this->subjects_con_courses[$this->subid['this']]=array('subid'=>$this->subid['this'],
                                                'name'=>$row['name'].'('.$name.')'._(' (период ').$level.')',
                                                'type'=>0,
                                                'reg_type'=>$row['reg_type'],
                                                'description'=>'',
                                                'begin'=>$row['begin'],
                                                'end'=>$row['end'],
                                                'period'=>$row['period'],
                                                'last_updated'=>$row['last_updated'],
                                                'access_mode'=>$row['access_mode'],
                                                'access_elements'=>$row['access_element'],
                                                'mode_free_limit'=>$row['mode_free_limit']);
                $this->subjects_courses[]=array('subject_id'=>$this->subid['this'],
                                                'course_id'=>$cid);
                $this->select_teacher($cid,$this->subid['this']);
                $this->update_students($this->subid['this'],$level,$curriculum_id);
                return $this->subid['this'];
            }

            function update_students($subid,$level,$curriculum_id){
                $res=sql("SELECT tracks2mid.mid FROM tracks2mid
                          WHERE tracks2mid.mid!='0' AND tracks2mid.mid IS NOT NULL AND tracks2mid.level=$level
                          AND tracks2mid.trid='$curriculum_id'");
                if($row=sqlrows($res)){
                     while($row=sqlget($res)){
                         $this->students_subjects[]=array('MID'=>$row['mid'],
                                                          'CID'=>$subid
                                                           );
                     }
                }
            }

            function select_cur_subjects_ref_courses($cir_sub_id,$level,$subid,$cid,$speciality_id,$number_of_level){
                 $level_id=array();
                 $res=sql("SELECT level FROM  tracks2course WHERE cid='$cid' AND trid='$speciality_id' GROUP BY level");
                 while($row=sqlget($res)){
                    $level_id[]=$this->select_training_period_id($number_of_level.'-'.$level,$row['level']);
                 }
                  if(is_array($level_id)&&count($level_id)){
                         foreach($level_id as $v){
                             $this->cur_subjects_ref_courses[]=array('curriculum_subject_id'=>$cir_sub_id,
                                                                     'training_period_id'=>$v,
                                                                     'subid'=>$subid);
                         }
                  }
            }

            function select_students($trid,$curriculum_id,$level){
                $res=sql("SELECT trmid,trid,mid,started,changed,stoped FROM tracks2mid WHERE mid!='0' AND mid IS NOT NULL AND trid='$trid' AND level='$level' ");
                if(sqlrows($res)){
                    while($row=sqlget($res)){
                        if(!in_array($row['trmid'],$this->students)){
                            $this->students[$row['trmid']]=array('student_id'=>$row['trmid'],
                                                             'MID'=>$row['mid'],
                                                             'curriculum_id'=>$curriculum_id,
                                                             'student_category_id'=>'1',
                                                             'student_state_id'=>'1',
                                                             );
                        }
                    }
                }
            }

            function select_groups($trid,$curriculums_id,$level){
                if(!count($this->groups))
                  $group_id=1;
                else $group_id=count($this->groups)+1;

                $res=sql("SELECT groupuser.mid,groupuser.gid,groupname.name,tracks2mid.trmid,tracks2mid.trid,tracks2mid.level FROM groupuser
                          INNER JOIN groupname ON (groupname.gid=groupuser.gid)
                          INNER JOIN tracks2mid ON (tracks2mid.mid=groupuser.mid)
                          WHERE tracks2mid.trmid!='' AND tracks2mid.trmid IS NOT NULL AND tracks2mid.trid=$trid");
                if(sqlrows($res)){
                    while($row=sqlget($res)){
                        if(is_array($this->groups_student)&&count($this->groups_student)){
                            if(key_exists($row['mid'],$this->groups_student))
                                continue;
                        }
                        if(is_array($this->groups)&&count($this->groups)){
                            if(!key_exists($row['gid'].'-'.$level,$this->groups)){
                                $this->add_groups($row['gid'],$row['name'],$curriculums_id,$level,$group_id);
                                $group_id++;
                            }
                        }else{
                            $this->add_groups($row['gid'],$row['name'],$curriculums_id,$level,$group_id);
                            $group_id++;
                        }
                        if($row['level']==$level){
                           $this->select_groups_student($row['gid'].'-'.$level,$row['mid'],$row['trmid']);
                        }
                    }
                }
            }

            function add_groups($gid,$name,$curriculums_id,$level,$group_id){
                $this->groups[$gid.'-'.$level]=array('group_id'=>$group_id,
                                                      'designation'=>$name,
                                                      'curriculum_id'=>$curriculums_id,
                                                      'planned_number'=>'200',
                                                       );
            }

            function select_groups_student($gid,$mid,$trmid){
                $this->groups_student[$mid]=array('groups_student_id'=>$mid,
                                                  'group_id'=>$this->groups[$gid]['group_id'],
                                                  'student_id'=>$trmid);
            }

            function select_stuff_union_teacher(){
                $res=sql("SELECT Teachers.* FROM Teachers
                          INNER JOIN tracks2course ON (Teachers.CID=tracks2course.cid)
                          WHERE Teachers.CID!='0' AND Teachers.MID!='0' AND tracks2course.cid!='0' AND tracks2course.cid IS NOT NULL
                          GROUP BY Teachers.MID
                          ORDER BY Teachers.PID");
                 $i=1;
                while($row=sqlget($res)){
                    $this->stuff_union[$row['MID']]=array('staff_unit_id'=>$row['MID'],
                                                          'designation'=>_('Штатная единица ').$i,
                                                          'name'=>_('Штатная единица ').$i,
                                                          'subdivision_id'=>'1',
                                                          'post_id'=>'1',
                                                          );
                    $this->teacher[$row['MID']]=array('teacher_id'=>$row['MID'],
                                                      'MID'=>$row['MID'],
                                                      'staff_unit_id'=>$row['MID'],
                                                      'engagement_condition_id'=>'1',
                                                      );
                    $i++;
                }
                sqlfree($res);

                $res=sql("SELECT Teachers.* FROM Teachers
                          INNER JOIN tracks2course ON (Teachers.CID=tracks2course.cid)
                          WHERE Teachers.CID!='0' AND Teachers.MID!='0' AND tracks2course.cid!='0' AND tracks2course.cid IS NOT NULL
                          ORDER BY Teachers.PID");
                if(sqlrows($res)){
                    $subject_staffing_id=1;
                    while($row=sqlget($res)){
                        foreach($this->subjects as $k=>$v){
                            $this->subjects_staffing[$row['MID'].'-'.$k]=array('subject_staffing_id'=>$subject_staffing_id,
                                                                               'subject_id'=>$k,
                                                                               'teacher_id'=>$row['MID']);
                            $subject_staffing_id++;
                        }
                    }
                }
                $this->select_workload_types();
                $this->select_workloads();
            }


            function select_teacher($cid,$subid){
                $res=sql("SELECT * FROM Teachers WHERE CID=$cid AND Teachers.MID!='0'");
                if(sqlrows($res)){
                    while($row=sqlget($res)){
                        $this->teacher_courses[$subid.'-'.$row['MID']]=array('CID'=>$subid,
                                                                             'MID'=>$row['MID']);
                    }
                }
            }

            function select_workload_types(){
               $this->workload_types[]=array('workload_type_id'=>'1',
                                               'designation'=>_('Лекционная'),
                                               'name'=>_('Лекционная'),
                                               'category'=>'1');
            }

            function select_workloads(){
                foreach($this->cur_subjects_ref_courses as $k=>$v){
                      $this->update_workloads($v['curriculum_subject_id'],$v['training_period_id']);
                      $this->select_workloads_teacher($v['curriculum_subject_id'],$v['training_period_id']);
                }
            }

            function select_workloads_teacher($cur_sub_id,$training_period_id){
                $CID=$this->curriculums_subjects[$cur_sub_id]['subject_id'];
                $res=sql("SELECT MID FROM Teachers WHERE CID='$CID'");
                if(sqlrows($res)){
                    while($row=sqlget($res)){
                        $this->update_workloads($cur_sub_id,$training_period_id,$this->subjects_staffing[$row['MID'].'-'.$CID]['subject_staffing_id']);
                    }
                }
            }

            function update_workloads($cur_sub_id,$tr_per_id,$pid=0){
                 $this->workloads[]=array('curriculum_subject_id'=>$cur_sub_id,
                                          'training_period_id'=>$tr_per_id,
                                          'workload_type_id'=>'1',
                                          'hours_count'=>'1',
                                          'hours_used'=>'1',
                                          'subject_staffing_id'=>$pid,
                                          'additional_info'=>'',
                                           );
            }

            function select_subject_stuff_id(){
            }

            function select_training_period_id($speciality_id,$i=1){
                return $this->training_periods[(int) $this->schedules[$speciality_id]['training_period']]['training_period_id']+$i-1;
            }

            function clear_db(){
                foreach($this->tabels as $v){
                  $sql=sql("DELETE FROM $v");
                  $sql=sql("UPDATE subjects SET end='".date("Y-m-d h:i:s")."'");
//debug remove from main server
                  $sql=sql("DELETE FROM Teachers WHERE CID>1073");
                  $sql=sql("DELETE FROM subjects WHERE subid>1073");
                  $sql=sql("DELETE FROM subjects_courses WHERE subject_id>1073");
                  $sql=sql("DELETE FROM Students WHERE CID>1073");
                }
            }

            function insert_db(){
                $this->qvery_insert('edo_specialities',$this->specialities);
                $this->qvery_insert('edo_subjects',$this->subjects);
                $this->qvery_insert('edo_schedules',$this->schedule_optimais($this->schedules));
                $this->qvery_insert('edo_training_periods',$this->training_periods);
                $this->qvery_insert('edo_base_curriculums',$this->base_curriculums);
                $this->qvery_insert('edo_curriculums',$this->curriculums);
                $this->qvery_insert('edo_curriculums_subjects',$this->curriculums_subjects);
                $this->qvery_insert('edo_cur_subjects_ref_base_cur',$this->cur_subjects_ref_base_cur);
                $this->qvery_insert('edo_cur_subjects_ref_courses',$this->cur_subjects_ref_courses);
                $this->qvery_insert('edo_students',$this->students);
                $this->qvery_insert('edo_teachers',$this->teacher);
                $this->qvery_insert('edo_staff_units',$this->stuff_union);
                $this->qvery_insert('edo_subjects_staffing',$this->subjects_staffing);
                $this->qvery_insert('edo_workload_types',$this->workload_types);
                $this->qvery_insert('edo_workloads',$this->workloads);
                $this->qvery_insert('edo_groups',$this->groups);
                $this->qvery_insert('edo_groups_students',$this->groups_student);
                $this->qvery_insert('subjects',$this->subjects_con_courses);
                $this->qvery_insert('subjects_courses',$this->subjects_courses);
                $this->qvery_insert('Students',$this->students_subjects);
                $this->qvery_insert('Teachers',$this->teacher_courses);
            }

            function schedule_optimais($arr){
                $schedules=array();
                foreach($arr as $id=>$line){
                    foreach($line as $k=>$v){
                        if($k=='training_period') continue;
                        $schedules[$id][$k]=$v;
                    }
                }
                return $schedules;
            }

            function qvery_insert($table,$array){
              $result=1;
               foreach($array as $key=>$value){
                     $sql=sql("INSERT INTO $table (".join(',',array_keys($value)).")
                                             VALUE ('".join("','",$value)."')");
                     if(!$sql) $result=0;
                     sqlfree($sql);

               }
               if($result) echo "result insert $table sucsess <br>";
               else echo "error insert $table <br>";
            }
         }
        $result = new migration_edo_dev();
        die('done');
   break;


//Копирование материалов не привязанных к курсам из библиотеки 32 34 в информационные ресурсы 40
    case 'copy_from_library_to_resources_32_40':
        require_once(dirname(__FILE__) . '/../../library/HM/Model/Abstract.php');
	    require_once(dirname(__FILE__) . '/../../application/model/HM/Files/FilesModel.php');
        $sql = "
                SELECT
                library_parent.bid as dest, library_parent.title as title, library_parent.author as author, library_parent.publisher as publisher,
                library_parent.publish_date as publish_date, library_parent.description as description, library_parent.upload_date,
                library_parent.is_active_version as is_active_version, library_parent.filename as src, library_parent.mid as mid
                FROM library library_parent
                WHERE library_parent.cid='0' ORDER BY library_parent.bid
    		";
    		$res = sql($sql);
    		while ($row = sqlget($res)) {
                if(empty($row['src'])) continue;
                if(stripos('http://www',$row['src'])){
                      sql("INSERT INTO resources (resource_id,title,url,volume,filename,type,filetype,description,content,created,updated,created_by,services,subject_id,status,location)
                           VALUE ('{$row['dest']}','".$row['title']."(".$row['author'].")','1','{$size_string}','{$row['src']}','".$row['description']." <a target=\"_blank\" href=\"".$row['src']."\">','{$filetype}',
                           '','',
                           '{$row['upload_date']}','{$row['upload_date']}','{$row['mid']}','0','0','{$row['is_active_version']}',1)");
                      continue;
                }
    		    $src = dirname(__FILE__) . '/library/' . $row['src'];
    		    if (file_exists($src)) {
        		    $size = filesize($src);
        			copy($src, dirname(__FILE__) . '/../../data/upload/resource/' . $row['dest']);
        		    $size_string = ($size > 512)?(  ($size/1024 > 512)  ?sprintf("%.02f MB",($size/1024)/1024)  :sprintf("%.02f kB",$size/1024))  :sprintf("%d B",$size);
        		    $filetype = HM_Files_FilesModel::getFileType($src);
                    $row['src']=str_replace("/".$row['dest']."/",'',$row['src']);
                    sql("INSERT INTO resources (resource_id,title,url,volume,filename,type,filetype,description,content,created,updated,created_by,services,subject_id,status,location)
                           VALUE ('{$row['dest']}','".$row['title']."(".$row['author'].")','0','{$size_string}','{$row['src']}','0','{$filetype}',
                           'Издатель: ".$row['publisher']."<br>Дата: ".$row['publisher_date']."<br>Описание: ".$row['description']."','',
                           '{$row['upload_date']}','{$row['upload_date']}','{$row['mid']}','0','0','{$row['is_active_version']}',1)");
        		}
    		}
          die('done');
    break;
    // Для 32 в каждом курсе создается пункт библиотека куда попадают материалы привязанные к библиотеке в курсе
    case "great_library_in_courses_32_40":
            $sql=sql("SELECT oid FROM organizations WHERE title  LIKE ('%Библиотека%')");
            if(sqlrows($sql)){
                $controller->setMessage("Преобразование уже было запущено повторный запуск может вызвать ошибки.{$msg}");
                $controller->terminate();
                exit;
            }
            $res = sql("SELECT CID FROM Courses");
            while ($row = sqlget($res)) {
                    $oidLastElement=last_element_organization($row['CID']);
                    $content=select_content($row['CID']);
                    update_content($content,$oidLastElement,$row['CID']);
            }
            sql("UPDATE Courses SET tree=''");
            $controller->setMessage("Преобразование выполнено успешно.{$msg}");
            $controller->terminate();
            exit();
            function last_element_organization($CID){
                $maxOid=0;
                $maxPrev_ref=0;
                $sql="SELECT prev_ref,oid FROM organizations WHERE CID='$CID' ";
                $r=sql($sql);
                while($row=sqlget($r)){
                    if($maxPrev_ref<$row['prev_ref']){
                        $maxPrev_ref=$row['prev_ref'];
                        $maxOid=$row['oid'];
                    }
                }
                return $maxOid;
            }

            function select_content($CID){
                $content=array();
                $sql="SELECT mod_list.Title as listTitle,mod_list.ModID as listModID,mod_list.Descript as listDescript,
                           mod_list.Pub as listPub,mod_list.PID as listPID,mod_list.forum_id,mod_list.test_id,mod_list.run_id,
                           mod_content.*
                    FROM mod_content
                    INNER JOIN mod_list ON (mod_list.ModID=mod_content.ModID)
                    WHERE mod_list.CID='$CID'
                    GROUP BY mod_content.McID
                    ORDER BY mod_list.ModID,mod_content.McID";
                $res=sql($sql);
                while($row=sqlget($res)){
                    $row['mod_l']=str_replace('http://elearn.msou.ru/','',$row['mod_l']);
                    $row['mod_l']='/../'.$row['mod_l'];
                    $content[$row['listModID']]['attributes']=array('title'   =>  $row['listTitle'],
                                                       'descript'=>  $row['listDescript'],
                                                       'pub'     =>  $row['listPub'],
                                                       'cid'     =>  $CID,
                                                       'modid'   =>  $row['listModID'],
                                                       'pid'     =>  $row['listPID']);
                    $content[$row['listModID']]['children'][$row['McID']]=array('title'=> $row['Title'],
                                                                        'mod_l'       => $row['mod_l'],
                                                                        'type'        => $row['type'],
                                                                        'mcid'        => $row['McID'],
                                                                        'conttype'    => $row['conttype'],
                                                                        'scorm_params'=> $row['scorm_params']);
              }
              return $content;
            }

            function update_content($content,$prev_ref,$CID){
                  $res1 = sql("INSERT INTO organizations (title, cid, level, prev_ref, vol1, vol2, module) VALUES
                               ('Библиотека','{$CID}', '0', '{$prev_ref}', '0', '0', '0')");
                  $prev_ref = sqllast();
                        foreach ($content as $ModID => $list){
                              $res2 = sql("INSERT INTO organizations (title, cid, level, prev_ref, vol1, vol2, module) VALUES
                                          ('{$list['attributes']['title']}', '{$CID}', '1', '{$prev_ref}', '0', '0', '0')");
                              $prev_ref = sqllast();
                              foreach($list['children'] as $McID=>$content){
                                   $res3 = sql("INSERT INTO library (filename, mid, title, cid, is_active_version) VALUES
                                   ('{$content['mod_l']}', '{$_SESSION['s']['mid']}', ".$GLOBALS['adodb']->Quote($content['title']).", '{$CID}', '1')");
                                   $bid = sqllast();
                                   $res4 = sql("INSERT INTO organizations (title, cid, level, prev_ref, vol1, vol2, module) VALUES
                                   ('{$content['title']}', '{$CID}', '2', '{$prev_ref}', '0', '0', '{$bid}')");
                                   $prev_ref = sqllast();
                              }
                        }
            }
    break;
    case "people_photo_transfer_34_40":
        function imgGreatFromString($str,$mid){
            $im=@imagecreatefromstring($str);
            $path=floor($mid/800);
            $filePath = "../upload/photo";
            if(!is_dir($filePath . DIRECTORY_SEPARATOR . $path)){
               mkdir($filePath . DIRECTORY_SEPARATOR . $path, 0664);
            }
            if($im){
                if(imagejpeg($im,$filePath."/".$path."/".$mid.".jpg")){
                    list($width, $height) = getimagesize($filePath."/".$path."/".$mid.".jpg");
                    if($width>116||$height>152){
                        $procent=$width/116;
                        $d_im=imagecreatetruecolor(round($width/$procent),round($height/$procent));
                        if(imagecopyresized($d_im,$im,0,0,0,0,(int)round($width/$procent),(int)round($height/$procent),(int)$width,(int)$height)){
                            if(imagejpeg($d_im,$filePath."/".$path."/".$mid.".jpg",100))
                                 return true;
                        }
                    }
                }
            } return false;
        }

        function info($info){
            require_once($GLOBALS['wwf']."/metadata.lib.php");
            require_once($GLOBALS['wwf']."/positions.lib.php");
            $metadataTypes = explode(';',REGISTRATION_FORM);
            if (is_array($metadataTypes) && count($metadataTypes)) {
                foreach($metadataTypes as $metadataType) {

                    $metadata = read_metadata(stripslashes($info['Information']), $metadataType);
                    $default_metadata = load_metadata($metadataType);
                    $flow = '';
                    if (is_array($metadata) && count($metadata)) {
                        foreach($metadata as $key => $value) {
                            if (($key == 0) && ($value['flow'] == 'line')) $flow = 'line';
                            if(is_array($value) && count($value)) {
                                if (isset($value['not_public']) && $value['not_public']) {
                                    continue;
                                }
                                if(trim($value['value']) != trim($default_metadata[$key]['value'])) {
                                    if ($flow != 'line') {
                                        $info['meta'][$value['name']]   = $value['value'];
                                        if (strlen($value['title'])) {
                                            $info['titles'][$value['name']] = $value['title'];
                                        } else {
                                            $info['titles'][$value['name']] = get_reg_block_title($metadataType);
                                        }
                                  /*      if (isset($value['title'])) {
                                            $info['meta'][$value['title']] = $value['value'];
                                        } else {
                                            $info['meta']['&nbsp;'] = $value['value'];
                                        }*/
                                        } else {

                                        if (!isset($info['meta'][$metadataType])) {
                                            $info['meta'][$metadataType] = '';
                                            $info['titles'][$metadataType] = get_reg_block_title($metadataType);
                                        }

                                        $info['meta'][$metadataType] .= $value['value'].' ';
                                    }
                                }
                                $flow = $value['flow'];
                            }

                        }
                    }
                    $result[$metadataType]=$metadata;
                }
            }
            return $result;
        }

        function metadata($arr){
            $sep_item  = '~|~';
            $sep_value = '~=~';
            $string='';
            foreach($arr as $k=>$v){
              $string.=$k.$sep_value.$v.$sep_item;
            }
            return $string;
        }

        require_once(dirname(__FILE__) . '/../../library/HM/Model/Abstract.php');
	    require_once(dirname(__FILE__) . '/../../application/model/HM/Files/FilesModel.php');
        $res=sql("SELECT People.MID, People.Information, filefoto.foto FROM People
                  LEFT JOIN filefoto ON (People.MID=filefoto.mid)");
        if(sqlrows($res)){
            $updates=array('additional_info'=>'',
                           'gender'=>'',
                           'year_of_birth'=>'',
                           'tel'=>'',
                           'team'=>'');
            while($row=sqlget($res)){
                if(!empty($row['foto']))  $img=imgGreatFromString($row['foto'],$row['MID']);
                if($img) $updates['gender']='1';
                $info = info($row);
                foreach($info as $k=>$v){
                    if($k=='contacts'){
                        foreach($v as $content){
                            if(($content['name']=='CellularNumber'||$content['name']=='PhoneNumber')&&
                                $content['value']&&!empty($content['value'])) $updates['tel']=$content['value'];
                            elseif(!empty($content['value'])&&$content['value']&&!empty($content['title'])){
                                $updates['additional_info'].=$content['title'].":".$content['value']."<br>";
                            }
                        }
                    }
                    if($k=='add_info'){
                        foreach($v as $content){
                            if(!empty($content['value'])&&$content['value']) $updates['additional_info']=$content['value']."<br>";
                        }
                    }
                }
                $r=sql("UPDATE People SET Information='".metadata($updates)."' WHERE MID=".$row['MID']."");
                sqlfree($r);
            }
            die('done');
        }
    break;
    case 'upgrade_loguser_32_40':
        $res=sql("SELECT params,SHEID FROM schedule");
        if(sqlrows($res)){
            while($row=sqlget($res)){
                $type=explode(';',$row['params']);
                foreach($type as $k){
                    $module=explode('=',$k);
                    if($module['0']=='module_id'){
                           $tid[$module['1']]=$row['SHEID'];
                    }
                }
            }
            if(is_array($tid)&&count($tid)){
                foreach($tid as $k=>$v){
                    $r=sql("UPDATE loguser SET sheid=$v WHERE tid='$k'");
                    sqlfree($r);
                }
            }
        }
        die('done');
    break;
  case 'iconv_logseance':
        function iconv_log($otv,$stid,$kod,$gl_k=''){
            $items=array();
            if(is_array($otv)&&count($otv)){
                foreach($otv as $k=>$v){
                    if(is_array($v)&&count($v)){
                        $items[$k]=iconv_log($v,$stid,$kod,$k);
                    }else{
                        $iconv=iconv("WINDOWS-1251","UTF-8//TRANSLIT",$v);
                        if($iconv)
                          $items[$k]=$iconv;
                        else{
                            if($gl_k=='main')
                                echo 'Ошибка конвертации данных где stid='.$stid.' kod='.$kod.'key='.$k;
                            $items[$k]=$v;
                        }
                    }
                }
            }else{
                return false;
            }
            return $items;
        }
        $res=sql("SELECT stid,kod,vopros,otvet FROM logseance");
        if(sqlrows($res)){
            while($row=sqlget($res)){
                if(!empty($row['otvet'])){
                    $items=iconv_log(unserialize(iconv('UTF-8','WINDOWS-1251//TRANSLIT',$row['otvet'])),$row['stid'],$row['kod']);
                    if($items){
                         $items=serialize($items);
                         $sql=sql("UPDATE logseance SET otvet=".$GLOBALS['adodb']->Quote($items)." WHERE stid='".$row['stid']."' AND kod='".$row['kod']."'");
                         sqlfree($sql);
                    }
                }
            }
        }
        die('done');
    break;
    case 'view_otv':
         $res=sql("SELECT stid,kod,vopros,otvet FROM logseance");
        if(sqlrows($res)){
            echo '<pre>';
            while($row=sqlget($res)){
               echo print_r(unserialize($row['otvet']));
            }
            echo '</pre>';
        }
        die('done');
    break;
  case 'count_questions':
      function metadata_unserialize($str){
          $sep = '~';
          $list=explode($sep,$str);
          return $list;
      }

      function isset_questions($kod){
          $questions=array();
          $res=sql("SELECT COUNT(kod) AS kod_c FROM list WHERE kod IN ('".join("','",$kod)."')");
          if(sqlrows($res)){
              $row=sqlget($res);
              $count=$row['kod_c'];
              $questions=array('data'=>metadata_serialize(select_isset_questions($kod)),
                               'count'=>$count);
              return $questions;
          }else return 0;
      }

      function select_isset_questions($kod){
          $res=sql("SELECT kod FROM list WHERE kod IN ('".join("','",$kod)."')");
          if(sqlrows($res)){
              while($row=sqlget($res)){
                  $kods[]=$row['kod'];
              }
          }
          return $kods;
      }

      function metadata_serialize($arr){
          if(is_array($arr)&&count($arr)){
              $sep = '~~';
              $list=join($sep,$arr);
              return $list;
          }else{
              return '';
          }
      }

      $update=array();
      $res=sql("SELECT test_id,questions,data FROM test_abstract");
      if(sqlrows($res)){
          while($row=sqlget($res)){
              if(!empty($row['data'])){
                  $questions=metadata_unserialize($row['data']);
                  if(count($questions)&&is_array($questions)){
                      $questions = isset_questions($questions);
                      if($row['questions']!=$questions['count']){
                          $update[$row['test_id']]=$questions;
                      }
                  }
              }
          }
          if(is_array($update)&&count($update)){
              foreach($update as $k=>$v){
                  $data =$GLOBALS['adodb']->Quote($v['data']);
                  $res=sql("UPDATE test_abstract SET data=".$GLOBALS['adodb']->Quote($v['data']).", questions='".$v['count']."' WHERE test_id='$k'");
                  sqlfree($res);
              }
          }
      }
      die('done');
  break;
  case 'iconv_list_weight':
        function iconv_logs($otv,$stid=null,$kod,$gl_k=''){
            $items=array();
            if(is_array($otv)&&count($otv)){
                foreach($otv as $k=>$v){
                    if(is_array($v)&&count($v)){
                        $items[$k]=iconv_logs($v,$stid,$kod,$k);
                    }else{
                        $iconv=iconv("WINDOWS-1251","UTF-8//TRANSLIT",$v);
                        if($iconv)
                          $items[$k]=$iconv;
                        else{
                            if($gl_k=='main')
                                echo 'Ошибка конвертации данных где kod='.$kod.'key='.$k;
                            $items[$k]=$v;
                        }
                    }
                }
            }else{
                return false;
            }
            return $items;
        }
        $res=sql("SELECT kod,weight FROM list");
        if(sqlrows($res)){
            while($row=sqlget($res)){
                if(!empty($row['weight'])){
                    $items=iconv_logs(unserialize(iconv('UTF-8','WINDOWS-1251//TRANSLIT',$row['weight'])),'',$row['kod']);
                    if($items){
                         $items=serialize($items);
                         $sql=sql("UPDATE list SET weight=".$GLOBALS['adodb']->Quote($items)." WHERE kod='".$row['kod']."'");
                         sqlfree($sql);
                    }
                }
            }
        }
        die('done');
  break;
  case 'unstripslashes_list_weight':
      $res=sql("SELECT kod,weight FROM list WHERE weight!=''");
      if(sqlrows($res)){
          while($row=sqlget($res)){
               $items = str_replace('\\','',$row['weight']);
               $sql=sql("UPDATE list SET weight=".$GLOBALS['adodb']->Quote($items)." WHERE kod='".$row['kod']."'");
               sqlfree($sql);
          }
      }
      die('done');
  break;
  case 'view_iconv_weight':
         $res=sql("SELECT kod,weight FROM list WHERE weight!=''");
        if(sqlrows($res)){
            echo '<pre>';
            while($row=sqlget($res)){
               echo $row['kod']."=>"."<br/>";
               echo print_r(unserialize($row['weight']));
               echo '++++++++++++++++++++++++++++++++++++++++++++++'."<br/>";
            }

        }
      die('done');
  break;
  case "greated_test_for_lessons":
      $sheid_tid=array();
      $res=sql("SELECT schedule.params, schedule.SHEID, test.tid
                FROM schedule
                LEFT JOIN test ON schedule.SHEID = test.lesson_id
                WHERE test.tid IS NULL
                AND params IS NOT NULL
                GROUP BY schedule.SHEID");
      if(sqlrows($res)){
          while($row=sqlget($res)){
              if(!empty($row['params'])){
                  $params=explode(";",$row['params']);
                  foreach($params as $param){
                      $module=explode("=",$param);
                      if($module['0']=='module_id'){
                           $sheid_tid[$row['SHEID']]=$module[1];
                      }
                  }
              }
          }
          sqlfree($res);
          if(count($sheid_tid)){
              foreach($sheid_tid as $sheid=>$tid){
                  $items=array();
                  $sql=sql("SELECT * FROM test WHERE tid='$tid' AND lesson_id=''");
                  if(sqlrows($sql)){
                      while($res=sqlget($sql)){
                          unset($res['tid']);
                          unset($res['lesson_id']);
                          unset($res['comments']);
                          $r=sql("INSERT INTO test (".join(',',array_keys($res)).",lesson_id) VALUE ('".join("','",$res)."','$sheid')");
                          sqlfree($r);
                      }
                  }
              }
          }
          die('done');
      }
  break;
  case 'update_versies_library':
       $update = array();
       $sql=sql("SELECT bid,parent,filename FROM library WHERE parent!=0");
       if(sqlrows($sql)){
           while($row=sqlget($sql)){
                if($row['parent']!=0){
                    $update[$row['parent']]=$row['filename'];
                }
           }
       }
       if(count($update)&&is_array($update)){
           foreach($update as $bid=>$filename){
               $sql=sql("UPDATE library SET filename=\"$filename\" WHERE bid='$bid'");
               sqlfree($sql);
           }
       }
   break;
  case "move_gender":

    $services = Zend_Registry::get('serviceContainer');
    $users = $services->getService('User')->fetchAll();

    foreach ($users as $user) {
    	$metadata = $user->getMetadataValues();
    	if (!empty($metadata['year_of_birth'])) {
            $year = (int)$metadata['year_of_birth'];
            if (($year > 1910) && ($year < date('Y'))) {
                sql("UPDATE People SET BirthDate='{$metadata['year_of_birth']}-01-01' WHERE MID={$user->MID}");
            }
    	}
    	if (!empty($metadata['gender'])) {
    	    sql("UPDATE People SET Gender={$metadata['gender']} WHERE MID={$user->MID}");
    	}
    }
    die('done');
    break;

    default:
    break;
}

?>