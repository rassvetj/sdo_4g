<?php
$_arrFieldNames = array(
	'actions'=>array('acid', 'name', 'url', 'title', 'sequence', 'type'), 
	'activity_resources'=>array('activity_id', 'activity_type', 'activity_name', 'subject_id', 'subject_name', 'volume', 'updated', 'resource_id', 'status'),
	'admins'=>array('AID', 'MID'), 
	'alt_mark'=>array('id', 'int', 'char'), 
    'blog' => array('id', 'title', 'body', 'created', 'created_by', 'subject_name', 'subject_id'),	
    'cam_casting'=>array('castID', 'cam_key', 'CID', 'MID', 'SHEID', 'FILE'), 
        'captcha'=>array('login', 'attempts', 'updated'),
	'cgname'=>array('cgid', 'name'), 
	'certificates'=>array('certificate_id', 'user_id', 'subject_id', 'created'), 
	'chain'=>array('id', 'name', 'order'), 
	'chain_agreement'=>array('id', 'cid', 'mid', 'subject', 'object', 'place', 'comment', 'date'), 
	'chain_item'=>array('id', 'chain', 'item', 'type', 'place'), 
	'chat'=>array('CHID', 'title', 'type', 'CID'), 
	'chat_messages'=>array('id', 'rid', 'cid', 'uid', 'message', 'posted', 'user', 'sendto','sheid'), 
	'chat_users'=>array('uid', 'rid', 'cid', 'joined', 'user'), 
	'CHATHISTORY'=>array('HISTORYID', 'CHATID', 'Login', 'TimeDate', 'MsgText', 'Params'), 
    'chat_channels' => array('id', 'subject_name', 'subject_id','lesson_id', 'name', 'start_date', 'end_date', 'show_history', 'start_time', 'end_time', 'is_general'),
    'chat_history' => array('id', 'channel_id', 'sender', 'receiver', 'message', 'created'),
    'chat_ref_users' => array('channel_id', 'user_id'),
	'claimants'=>array('SID', 'MID', 'CID', 'base_subject', 'Teacher', 'created', 'created_by', 'begin', 'end', 'status', 'type', 'mid_external', 'lastname', 'firstname', 'patronymic', 'comments', 'dublicate'),
	'classifiers'=>array('classifier_id', 'lft', 'rgt', 'level_', 'name', 'type_'), 
	'classifiers_images'=>array('classifier_image_id','item_id', 'type'), 
	'classifiers_links'=>array( 'item_id','classifier_id', 'type'), 
    'classifiers_types' => array('type_id', 'name', 'link_types', 'sap_type'),    
    'comments' => array('id', 'activity_name', 'subject_name', 'subject_id', 'user_id', 'item_id', 'message', 'created'),
    'comp2course'=>array('ccoid', 'cid', 'tid', 'coid', 'level'), 
	'competence'=>array('coid', 'name', 'type', 'level', 'status', 'info'), 
	'competence_roles'=>array('id', 'name', 'formula', 'dynamic', 'courses', 'specialization'), 
	'competence_roles_competences'=>array('role', 'competence', 'threshold', 'task'), 
	'competence_roles_specializations'=>array('role', 'competence', 'specialization'), 
	'conf_cid'=>array('cid', 'autoindex'), 
	'course2group'=>array('cid', 'gid', 'cgid'), 
	'Courses'=>array('CID', 'Title', 'Description', 'TypeDes', 'CD', 'cBegin', 'cEnd', 'Fee', 'valuta', 'Status', 'createby', 'createdate', 'longtime', 'did', 'credits_student', 'credits_teacher', 'locked', 'chain', 'is_poll', 'is_module_need_check', 'type', 'tree', 'progress', 'sequence', 'provider','provider_options', 'planDate', 'developStatus', 'lastUpdateDate', 'archiveDate', 'services', 'has_tree', 'new_window', 'emulate', 'format','author'),
	'courses_groups'=>array('did', 'name', 'mid', 'info', 'color', 'owner_did', 'not_in', 'file_image'), 
	'courses_links'=>array('cid', 'with'), 
	'courses_marks'=>array('cid', 'mid', 'mark', 'alias', 'confirmed'), 
	'Courses_stat'=>array('CID', 'last_access', 'MID', 'teacher'), 
	'crontask'=>array('crontask_id', 'crontask_runtime'), 
	'deans'=>array('DID', 'MID', 'subject_id'), 
    'deans_options' => array('user_id', 'unlimited_subjects', 'unlimited_classifiers', 'assign_new_subjects'),
    'deans_responsibilities' => array('user_id', 'classifier_id'),
	'dean_poll_users' => array('lesson_id', 'head_mid', 'student_mid'),
	'departments'=>array('did', 'name', 'mid', 'info', 'color', 'owner_did', 'not_in', 'application'), 
	'departments_courses'=>array('did', 'cid'), 
	'departments_groups'=>array('did', 'gid'), 
	'departments_soids'=>array('did', 'soid'), 
	'departments_specs'=>array('did', 'spid'), 
	'departments_tracks'=>array('id', 'did', 'track'), 
	'developers'=>array('mid', 'cid'),
    'employee' => array('user_id'),
    'events' => array('event_id', 'title', 'tool', 'scale_id', 'weight'),
	'EventTools'=>array('TypeID', 'TypeName', 'Student', 'Teacher', 'Deen', 'Icon', 'XSL', 'Description', 'tools', 'type', 'weight'), 
	'eventtools_weight'=>array('id', 'event', 'cid', 'weight'), 
	'exam_types'=>array('etid', 'title'), 
    'faq' => array('faq_id', 'question', 'answer', 'roles', 'published'),
	'exercises' => array('exercise_id', 'title', 'status', 'description', 'created', 'updated', 'created_by', 'questions','data', 'subject_id'),
	'file'=>array('kod', 'fnum', 'ftype', 'fname', 'fdata', 'fdate', 'fx', 'fy'), 
	'file_tranfer'=>array('ftID', 'ft_key', 'ModID', 't_date', 'MID'), 
	'filefoto'=>array('mid', 'foto', 'last', 'fx', 'fy'), 
    'files' => array('file_id', 'name', 'path','file_size'),
    'videoblock' => array('file_id', 'name'),
	'formula'=>array('id', 'name', 'formula', 'type', 'CID'), 
	'forumcategories'=>array('id', 'name', 'cid', 'create_by', 'create_date', 'cms'), 
	'forummessages'=>array('id', 'thread', 'posted', 'icon', 'name', 'email', 'sendmail', 'message', 'is_topic', 'mid', 'type', 'oid', 'parent'), 
	'forumthreads'=>array('thread', 'category', 'course', 'lastpost', 'answers', 'private'), 
	'forums_list' => array('forum_id','subject_id','user_id','user_name','user_ip','title','created','updated','flags'),
	'forums_sections' => array('section_id','lesson_id','forum_id','user_id','user_name','user_ip','parent_id','title','text','created','updated','last_msg','count_msg','order','flags', 'is_hidden'),
	'forums_messages' => array('message_id', 'forum_id','section_id','user_id','user_name','user_ip','level','answer_to','title','text','text_preview','text_size','created','updated','delete_date','deleted_by','rating','flags', 'is_hidden'),
	'forums_messages_showed' => array('user_id','message_id','created'),
	'glossary'=>array('id', 'name', 'cid', 'description'), 
	'graduated'=>array('SID', 'MID', 'CID', 'begin', 'end','created', 'certificate_id','score','status','progress','is_lookable', 'transfered'), 
	'groupname'=>array('gid', 'cid', 'name', 'owner_gid'), 
	'groupuser'=>array('mid', 'cid', 'gid'), 
	'hacp_debug'=>array('id', 'message', 'date', 'direction'),
	'help' => array('help_id','role', 'module','app_module', 'controller', 'action', 'link_subject','is_active_version','link', 'title', 'text', 'moderated','lang'),
	'holidays' => array('id','title', 'type','date_', 'user_id'),
	'htmlpage' => array('page_id', 'name', 'text', 'group_id', 'url', 'ordr'),
	'htmlpage_groups' => array('group_id', 'name', 'lft', 'rgt', 'level', 'role', 'ordr'),
	'interesting_facts' => array('interesting_facts_id', 'title', 'text', 'status'), 
	'interface'=>array('interface_id', 'role', 'user_id', 'block', 'necessity', 'x', 'y', 'param_id'), 
	'interview' => array('interview_id', 'title', 'lesson_id', 'user_id', 'to_whom', 'type', 'question_id', 'message','date','interview_hash'),
	'interview_files' => array('interview_id','file_id'),
	'kbase_items' => array('type', 'title', 'id', 'cdate', 'status'),    
	'Knigi'=>array('KID', 'CID', 'Name', 'Author', 'Izdatel', 'Year', 'Description', 'Url', 'access', 'file_ext', 'file_active'), 
	'laws'=>array('id', 'parent', 'categories', 'title', 'initiator', 'author', 'annotation', 'type', 'region', 'area_of_application', 'create_date', 'expire', 'modify_date', 'edit_reason', 'current_version', 'filename', 'upload_date', 'uploaded_by', 'access_level'), 
	'laws_categories'=>array('catid', 'name', 'parent'), 
	'laws_index'=>array('id', 'word', 'count'), 
	'laws_index_words'=>array('id', 'word'),
        'lessons'=>array('SHEID', 'title', 'url', 'descript', 'begin', 'end', 'createID', 'typeID', 'vedomost', 'CID', 'CHID', 'startday', 'stopday', 'timetype', 'isgroup', 'cond_sheid', 'cond_mark', 'cond_progress', 'cond_avgbal', 'cond_sumbal', 'cond_operation', 'period', 'rid', 'teacher', 'gid', 'perm', 'pub', 'sharepointId', 'connectId', 'params', 'all', 'recommend'),
	'library'=>array('bid', 'cid', 'parent', 'cats', 'mid', 'uid', 'title', 'author', 'publisher', 'publish_date', 'description', 'keywords', 'filename', 'location', 'metadata', 'need_access_level', 'upload_date', 'is_active_version', 'type', 'is_package', 'quantity', 'content', 'scorm_params', 'pointId', 'courses', 'lms', 'place', 'not_moderated'),
	'library_assign'=>array('assid', 'bid', 'mid', 'start', 'stop', 'closed', 'number', 'type'), 
	'library_categories'=>array('catid', 'name', 'parent'), 
	'library_cms_index'=>array('id', 'word', 'count'), 
	'library_cms_index_words'=>array('id', 'word'), 
	'library_index'=>array('id', 'module', 'file', 'keywords'), 
	'list'=>array('kod', 'qtype', 'qdata', 'qtema', 'qmoder', 'adata', 'balmax', 'balmin', 'url', 'last', 'timelimit', 'weight', 'is_shuffled', 'created_by', 'timetoanswer', 'prepend_test', 'is_poll', 'id', 'ordr', 'name'),
    'list_files' => array('kod', 'file_id'),
	'logseance'=>array('stid', 'mid', 'cid', 'tid', 'kod', 'number', 'time', 'bal', 'balmax', 'balmin', 'good', 'vopros', 'otvet', 'attach', 'filename', 'text', 'sheid', 'comments', 'review', 'review_filename','qtema'), 
	'loguser'=>array('stid', 'mid', 'cid', 'tid', 'balmax', 'balmin', 'balmax2', 'balmin2', 'bal', 'mark', 'questdone', 'questall', 'qty', 'free', 'skip', 'start', 'stop', 'fulltime', 'moder', 'needmoder', 'status', 'moderby', 'modertime', 'teachertest', 'log', 'sheid'),
	'managers'=>array('id', 'mid'), 
	'messages'=>array('message_id', 'from', 'to','subject','subject_id', 'message', 'created'), 
	'metadata_groups'=>array('group_id', 'name', 'roles', 'locked'), 
	'metadata_items'=>array('item_id', 'group_id', 'name', 'type', 'value', 'public', 'required', 'order', 'editable'), 
	'methodologist'=>array('mid', 'cid'), 
	'mod_attempts'=>array('id', 'ModID', 'mid', 'start'), 
	'mod_content'=>array('McID', 'Title', 'ModID', 'mod_l', 'type', 'conttype', 'scorm_params'), 
	'mod_list'=>array('ModID', 'Title', 'Num', 'Descript', 'Pub', 'CID', 'PID', 'forum_id', 'test_id', 'run_id'), 
	'money'=>array('moneyid', 'sum', 'mid', 'trid', 'date', 'type', 'sign', 'info'), 
	'new_news'=>array('nID', 'date', 'autor', 'email', 'Title', 'news', 'perm', 'inarhiv', 'image'), 
	'news'=>array('id', 'created', 'author', 'created_by', 'announce', 'message', 'subject_name', 'subject_id'), 
	'news2'=>array('nID', 'date', 'Title', 'author', 'message', 'lang', 'show', 'standalone', 'application', 'soid', 'type', 'resource_id'),
    'notice' => array('id', 'event', 'receiver', 'title', 'message', 'type'),    
    'oauth_apps' => array('app_id', 'title', 'description', 'created', 'created_by', 'callback_url', 'api_key', 'consumer_key', 'consumer_secret'),
    'oauth_tokens' => array('token_id', 'app_id', 'token', 'token_secret', 'state', 'verify', 'user_id'),
    'oauth_nonces' => array('nonce_id', 'app_id', 'ts', 'nonce'),
	'OPTIONS'=>array('OptionID', 'name', 'value'),
	'options_at'=>array('OptionID', 'name', 'value'), 
	'options_cms'=>array('OptionID', 'name', 'value'), 
	'organizations'=>array('oid', 'title', 'cid', 'root_ref', 'level', 'next_ref', 'prev_ref', 'mod_ref', 'status', 'vol1', 'vol2', 'metadata', 'module'), 
	'password_history' => array('user_id', 'password', 'change_date'),
	'People'=>array('MID', 'mid_external', 'LastName', 'FirstName', 'LastNameLat', 'FirstNameLat', 'Patronymic', 'Registered', 'Course', 'EMail', 'Phone', 'Information', 'Address', 'Fax', 'Login', 'Password', 'javapassword', 'BirthDate', 'CellularNumber', 'ICQNumber', 'Gender', 'last', 'countlogin', 'rnid', 'Position', 'PositionDate', 'PositionPrev', 'invalid_login', 'isAD', 'polls', 'Access_Level', 'rang', 'preferred_lang', 'blocked', 'block_message', 'head_mid', 'force_password', 'lang', 'need_edit', 'dublicate', 'email_confirmed'),
	'periods'=>array('lid', 'starttime', 'stoptime', 'name', 'count_hours'), 
	'permission2act'=>array('pmid', 'acid', 'type'), 
	'permission2mid'=>array('pmid', 'mid'), 
	'permission_groups'=>array('pmid', 'name', 'default', 'type', 'rang', 'application'), 
	'personal'=>array('PID', 'FIO', 'work', 'tel', 'email', 'type'), 
	'personallog'=>array('id', 'mid', 'cid', 'item', 'session', 'datetime'), 
	'polls'=>array('id', 'name', 'description', 'begin', 'end', 'event', 'formula', 'data', 'deleted', 'sequence'), 
	'polls_criteries'=>array('id', 'name', 'poll', 'mid', 'soid', 'role'), 
	'polls_people'=>array('poll', 'mid', 'soid', 'role', 'kod'), 
	'polls_state'=>array('pid', 'mid', 'state', 'modified', 'inactive'), 
	'posts'=>array('posted', 'name', 'course', 'email', 'text'), 
	'posts3'=>array('PostID', 'posted', 'name', 'CID', 'email', 'text', 'mid', 'startday', 'stopday'), 
	'posts3_mids'=>array('postid', 'mid'), 
    'ppt2swf' => array('status', 'process', 'success_date', 'id_user', 'url', 'name', 'webinar_id', 'pool_id'),   
    'programm'=>array('programm_id', 'external_id', 'name', 'programm_type'),     
    'programm_events'=>array('programm_event_id', 'programm_id', 'name', 'type', 'item_id'),     
    'programm_events_users'=>array('programm_event_id', 'user_id', 'begin_date', 'end_date', 'status'),     
    'programm_users'=>array('programm_id', 'user_id', 'assign_date'),     
	'providers'=>array('id', 'title', 'address', 'contacts', 'description'), 
    'quizzes' => array('quiz_id', 'title', 'status', 'description', 'keywords', 'created', 'updated', 'created_by','questions', 'data', 'subject_id', 'location'),
	'quizzes_answers' => array('quiz_id', 'question_id','question_title','theme', 'answer_id', 'answer_title'),
    'quizzes_feedback' => array('user_id', 'subject_id', 'lesson_id', 'status', 'begin', 'end', 'place', 'title', 'trainer', 'trainer_id', 'subject_name', 'created'),
	'quizzes_results' => array('user_id', 'lesson_id', 'question_id', 'answer_id', 'freeanswer_data', 'quiz_id', 'subject_id', 'junior_id'),    
	'rank'=>array('rnid', 'Title'), 
	'ratings'=>array('id', 'mid', 'cid', 'teacher', 'rating'), 
	'reckoning_courses'=>array('trid', 'cid', 'mid', 'mark'), 
	'report_templates'=>array('rtid', 'template_name', 'report_name', 'created', 'creator', 'edited', 'editor', 'template'), 
	'resources'=>array('resource_id', 'resource_id_external', 'title','url', 'volume', 'filename', 'type','filetype', 'description', 'keywords','content', 'created', 'updated', 'created_by', 'services', 'subject_id', 'status', 'location', 'db_id', 'test_id', 'activity_id', 'activity_type', 'related_resources', 'parent_id', 'parent_revision_id'),
	'resource_revisions'=>array('revision_id', 'resource_id', 'url', 'volume', 'filename', 'filetype', 'content', 'updated', 'created_by'),
	'reviewers'=>array('mid', 'cid'), 
	'reviews'=>array('id', 'mid', 'module', 'date', 'review'), 
    'reports' => array('report_id','domain', 'name', 'fields', 'created', 'created_by','status'),
	'roles'=>array('mid', 'role'), 
	'room'=>array('rid', 'name', 'descript', 'typ', 'adminid'), 
	'rooms'=>array('rid', 'name', 'volume', 'status', 'type', 'description'), 
	'rooms2course'=>array('rid', 'cid'), 
    'session_guest' => array('session_guest_id', 'start', 'stop'),
    'scales' => array('scale_id', 'name', 'description', 'type'),
    'scale_values' => array('value_id', 'scale_id', 'value', 'text', 'description'),
	'schedule'=>array('SHEID', 'title', 'url', 'descript', 'begin', 'end', 'createID', 'createDate', 'typeID', 'vedomost', 'CID', 'CHID', 'startday', 'stopday', 'timetype', 'isgroup', 'cond_sheid', 'cond_mark', 'cond_progress', 'cond_avgbal', 'cond_sumbal', 'cond_operation', 'period', 'rid', 'teacher', 'gid', 'perm', 'pub', 'sharepointId', 'connectId', 'recommend', 'notice', 'notice_days', 'all', 'params', 'activities', 'order', 'tool', 'moderator','isfree','section_id'),
	'schedule_locations'=>array('sheid', 'location', 'teacher'), 
	'schedulecount'=>array('mid', 'sheid', 'qty', 'last'), 
	'scheduleID'=>array('SSID', 'SHEID', 'MID', 'gid', 'isgroup', 'V_STATUS','V_DONE', 'V_DESCRIPTION', 'DESCR', 'SMSremind', 'ICQremind', 'EMAILremind', 'ISTUDremind', 'test_corr', 'test_wrong', 'test_date', 'test_answers', 'test_tries', 'toolParams', 'comments', 'chief', 'created', 'updated', 'launched'), 
	'schedule_marks_history' => array('MID', 'SSID', 'mark', 'updated', 'confirmed'),
	'scorm_tracklog'=>array('trackID', 'mid', 'cid', 'ModID', 'McID','lesson_id', 'trackdata', 'stop', 'start', 'score', 'scoremax', 'scoremin', 'status'),
    'scorm_report'=>array('report_id','mid','lesson_id','report_data','updated','cid'),	
	'seance'=>array('stid', 'mid', 'cid', 'tid', 'kod', 'attach', 'filename', 'text', 'time', 'bal', 'lastbal', 'comments', 'review', 'review_filename'), 
	'sections'=>array('section_id', 'subject_id', 'name', 'order'), 
	'sequence_current'=>array('mid', 'cid', 'current','subject_id','lesson_id'), 
	'sequence_history'=>array('id','mid', 'cid', 'item', 'date', 'subject_id','lesson_id'), 
	'sessions'=>array('sessid', 'sesskey', 'mid', 'course_id', 'lesson_id', 'lesson_type', 'start', 'stop', 'ip', 'logout', 'browser_name', 'browser_version', 'flash_version', 'os', 'screen', 'cookie', 'js', 'java_version', 'silverlight_version', 'acrobat_reader_version', 'msxml_version'),
    'specializations'=>array('spid', 'name', 'discription'), 
	'states'=>array('scope', 'scope_id', 'state', 'title'), 
	'str_of_organ2competence'=>array('coid', 'soid', 'percent'), 
	'structure_of_organ'=>array('soid', 'soid_external', 'name', 'code', 'mid', 'info', 'owner_soid', 'agreem', 'type', 'own_results', 'enemy_results', 'display_results', 'threshold', 'specialization', 'claimant', 'level', 'lft', 'rgt', 'rgt', 'is_manager', 'blocked'), 
	'structure_of_organ_courses'=>array('soid', 'cid'), 
	'structure_of_organ_roles'=>array('soid', 'role'), 
	'Students'=>array('SID', 'MID', 'CID', 'cgid', 'Registered', 'time_registered', 'offline_course_path', 'time_ended'), 
    'storage_filesystem' => array('id', 'parent_id', 'subject_id', 'subject_name', 'name', 'alias', 'is_file', 'description', 'user_id', 'created', 'changed'),
	'subjects'=>array('subid', 'id_sap', 'external_id', 'code', 'name','shortname', 'supplier_id', 'description', 'type', 'reg_type', 'begin', 'end', 'begin_planned', 'end_planned', 'price','price_currency', 'plan_users', 'services', 'period', 'period_restriction_type', 'last_updated', 'access_mode', 'access_elements', 'mode_free_limit', 'auto_done', 'base', 'base_id', 'claimant_process_id', 'last_update', 'longtime', 'base_color', 'state', 'default_uri', 'scale_id', 'auto_mark', 'auto_graduate', 'formula_id', 'threshold','mark_type'),
        'subjects_courses'=>array('subject_id', 'course_id'),
        'subjects_tests'=>array('subject_id', 'test_id'),
	'subjects_tasks'=>array('subject_id', 'task_id'),
        'subjects_exercises'=>array('subject_id', 'exercise_id'),
        'subjects_quizzes'=>array('subject_id', 'quiz_id'),
        'subjects_resources'=>array('subject_id', 'resource_id'),
    'suppliers'=>array('supplier_id', 'title', 'address', 'contacts', 'description'), 
        'supervisors' => array('user_id'),
	'processes' => array('process_id', 'name', 'chain', 'type'),
	'state_of_process' => array('state_of_process_id', 'item_id', 'process_id', 'process_type', 'current_state', 'status', 'params'),
	'programm' => array('programm_id','name'),
	'programm_events' => array('programm_event_id','programm_id','name','type_','item_id'),
	'programm_events_users' => array('programm_event_id','user_id','begin_date','end_date','status'),
	'programm_users' => array('programm_id','user_id','assign_date'),
    'subscriptions' => array('subscription_id', 'user_id', 'channel_id'),
    'subscription_channels' => array('channel_id', 'activity_name', 'subject_name', 'subject_id', 'lesson_id', 'title', 'description', 'link'),
    'subscription_entries' => array('entry_id', 'channel_id', 'title', 'link', 'description', 'content', 'author'),
    'tag' => array('id', 'body'),
    'tag_ref' => array('tag_id', 'item_id','item_type'),
	'tasks' => array('task_id', 'title', 'status', 'description', 'keywords', 'created', 'updated', 'created_by','questions', 'data', 'subject_id', 'location'),	
    'Teachers'=>array('PID', 'MID', 'CID'),
	'teachnotes'=>array('NOTID', 'SHEID', 'MID', 'noteText', 'ISTUDremind', 'SMSremind', 'EMAILremind', 'ICQremind'), 
	'test'=>array('tid', 'cid', 'cidowner', 'title', 'datatype', 'data', 'random', 'lim', 'qty', 'sort', 'free', 'skip', 'rating', 'status', 'questres', 'endres', 'showurl', 'showotvet', 'timelimit', 'startlimit', 'limitclean', 'last', 'lastmid', 'cache_qty', 'random_vars', 'allow_view_log', 'created_by', 'comments', 'mode', 'is_poll', 'poll_mid', 'test_id', 'lesson_id', 'type', 'threshold', 'adaptive'),
    'test_abstract' => array('test_id', 'title', 'status', 'description', 'keywords', 'created', 'updated', 'created_by','questions', 'data', 'subject_id', 'location'),
    'tests_questions' => array('test_id', 'subject_id', 'kod'),
    'test_feedback' => array('test_feedback_id', 'title' , 'type', 'text', 'parent', 'treshold_min', 'treshold_max', 'test_id', 'question_id', 'answer_id', 'show_event', 'show_on_values'),
	'TestContent'=>array('QID', 'TID', 'xmlQ', 'questionText', 'type', 'attachFileName', 'attachExt', 'theme', 'isObligatory'), 
	'testcount'=>array('mid', 'tid', 'cid', 'qty', 'last', 'lesson_id'),
	'testneed'=>array('tid', 'kod'), 
	'testquestions'=>array('tid', 'cid', 'questions'), 
	'TestTitle'=>array('TID', 'Title', 'CID', 'timelim', 'blockQ', 'trylimit', 'orderQ', 'questlim'), 
	'tracks'=>array('trid', 'name', 'id', 'volume', 'status', 'type', 'owner', 'totalcost', 'currency', 'description', 'credits_free', 'threshold', 'number_of_levels', 'year', 'locked'), 
	'tracks2course'=>array('trid', 'cid', 'level', 'name', 'hours_samost', 'hours_lecture', 'hours_lab', 'hours_practice', 'hours_seminar', 'hours_kurs', 'type', 'control'), 
	'tracks2group'=>array('id', 'trid', 'level', 'gid', 'updated'), 
	'tracks2mid'=>array('trmid', 'trid', 'mid', 'level', 'started', 'changed', 'stoped', 'status', 'sign', 'info', 'do_next_level'), 
	'tracks_levels'=>array('id', 'trid', 'level', 'volume', 'date_begin', 'date_end'), 
	'training'=>array('trid', 'title', 'cid'), 
	'training_run'=>array('run_id', 'name', 'path', 'cid'),
    'updates' => array('update_id', 'version', 'created', 'created_by', 'updated', 'organization', 'description', 'servers'),
	'video'=>array('id', 'filename', 'created', 'title', 'main_video'), 
	'webinar_answers'=>array('aid', 'qid', 'text'), 
	'webinar_chat'=>array('id', 'pointId', 'message', 'datetime', 'userId'), 
	'webinar_history'=>array('id', 'pointId', 'userId', 'action', 'item', 'datetime'), 
	'webinar_plan'=>array('id', 'pointId', 'href', 'title', 'bid'), 
	'webinar_plan_current'=>array('pointId', 'currentItem'), 
	'webinar_questions'=>array('qid', 'text', 'type', 'point_id', 'is_voted'), 
	'webinar_users'=>array('pointId', 'userId', 'last'), 
	'webinar_votes'=>array('vid', 'user_id', 'qid', 'aid'), 
	'webinar_whiteboard'=>array('actionId', 'pointId', 'userId', 'actionType', 'datetime', 'color', 'tool', 'text', 'width', 'height'), 
	'webinar_whiteboard_points'=>array('pointId', 'actionId', 'x', 'y', 'type'),
    'wiki_articles' => array('id', 'created', 'title', 'subject_name', 'subject_id', 'lesson_id', 'changed'),
    'wiki_archive' => array('id', 'article_id', 'created', 'author', 'body'),	
    'user_login_log' => array('login', 'date', 'event_type', 'status', 'comments', 'ip'),
    'videochat_users' => array('pointId', 'userId', 'last'),
    'webinars' => array('webinar_id', 'name', 'create_date', 'subject_id'),
    'webinar_files' => array('webinar_id', 'file_id', 'num'),
);
?>