<?php
require_once('config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once($CFG->dirroot.'/user/profile/lib.php');
require_once($CFG->dirroot.'/group/lib.php');
require_once($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot.'/admin/tool/uploaduser/locallib.php');
require_once($CFG->dirroot.'/admin/tool/uploaduser/user_form.php');



$iid         = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

@set_time_limit(60*60); // 1 hour should be enough
raise_memory_limit(MEMORY_HUGE);

//require_login();
//admin_externalpage_setup('tooluploaduser');
//require_capability('moodle/site:uploadusers', get_context_instance(CONTEXT_SYSTEM));
	
$struserrenamed             = get_string('userrenamed', 'tool_uploaduser');
$strusernotrenamedexists    = get_string('usernotrenamedexists', 'error');
$strusernotrenamedmissing   = get_string('usernotrenamedmissing', 'error');
$strusernotrenamedoff       = get_string('usernotrenamedoff', 'error');
$strusernotrenamedadmin     = get_string('usernotrenamedadmin', 'error');

$struserupdated             = get_string('useraccountupdated', 'tool_uploaduser');
$strusernotupdated          = get_string('usernotupdatederror', 'error');
$strusernotupdatednotexists = get_string('usernotupdatednotexists', 'error');
$strusernotupdatedadmin     = get_string('usernotupdatedadmin', 'error');

$struseruptodate            = get_string('useraccountuptodate', 'tool_uploaduser');

$struseradded               = get_string('newuser');
$strusernotadded            = get_string('usernotaddedregistered', 'error');
$strusernotaddederror       = get_string('usernotaddederror', 'error');

$struserdeleted             = get_string('userdeleted', 'tool_uploaduser');
$strusernotdeletederror     = get_string('usernotdeletederror', 'error');
$strusernotdeletedmissing   = get_string('usernotdeletedmissing', 'error');
$strusernotdeletedoff       = get_string('usernotdeletedoff', 'error');
$strusernotdeletedadmin     = get_string('usernotdeletedadmin', 'error');

$strcannotassignrole        = get_string('cannotassignrole', 'error');

$struserauthunsupported     = get_string('userauthunsupported', 'error');
$stremailduplicate          = get_string('useremailduplicate', 'error');

$strinvalidpasswordpolicy   = get_string('invalidpasswordpolicy', 'error');
$errorstr                   = get_string('error');

$stryes                     = get_string('yes');
$strno                      = get_string('no');
$stryesnooptions = array(0=>$strno, 1=>$stryes);

$returnurl = new moodle_url('/course/view.php?catid='.$_GET['catid'].'&id='.$_GET['id']);
$bulknurl  = new moodle_url('/admin/user/user_bulk.php');

$today = time();
$today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);

// array of all valid fields for validation
$STD_FIELDS = array('id', 'firstname', 'lastname', 'username', 'email',
        'city', 'country', 'lang', 'timezone', 'mailformat',
        'maildisplay', 'maildigest', 'htmleditor', 'ajax', 'autosubscribe',
        'institution', 'department', 'idnumber', 'skype',
        'msn', 'aim', 'yahoo', 'icq', 'phone1', 'phone2', 'address',
        'url', 'description', 'descriptionformat', 'password',
        'auth',        // watch out when changing auth type or using external auth plugins!
        'oldusername', // use when renaming users - this is the original username
        'suspended',   // 1 means suspend user account, 0 means activate user account, nothing means keep as is for existing users
        'deleted',     // 1 means delete user
		'site_fk',
		'roleassign',
		'parentlist'
    );

$PRF_FIELDS = array();

if ($prof_fields = $DB->get_records('user_info_field')) {
    foreach ($prof_fields as $prof_field) {
        $PRF_FIELDS[] = 'profile_field_'.$prof_field->shortname;
    }
}
unset($prof_fields);

if (empty($iid)) {
	
    $mform1 = new admin_uploaduser_form1();
echo 'vhgp';
    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploaduser');
        $cir = new csv_import_reader($iid, 'uploaduser');

        $content = $mform1->get_file_content('userfile');
        
        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        unset($content);
		
        if ($readcount === false) {
            print_error('csvloaderror', '', $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }
        // test if columns ok
        $filecolumns = uu_validate_user_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
		 // continue to form2

    } else {
        //echo $OUTPUT->header();

        echo  html_writer::start_tag('div', array('style'=>'float:right;'));
       /*	echo  html_writer::start_tag('a');
       	echo html_writer::link(new moodle_url('/upload_user.csv'), get_string('uploadusertemp'), array('forcedownload'=>1));
       	echo  html_writer::end_tag('a');*/
        echo '<li><a href="'.$CFG->wwwroot.'/admin/download_file.php?filename=upload_user.csv" class="buttonstyled">'.get_string('uploadusertemp').'</a></li>';
       	echo  html_writer::end_tag('div');
        
        echo $OUTPUT->heading_with_help(get_string('uploadusers', 'tool_uploaduser'), 'uploadusers', 'tool_uploaduser');
      
	 	$mform1->display();
        //echo $OUTPUT->footer();
        //die;
    }
} else {
    $cir = new csv_import_reader($iid, 'uploaduser');
    $filecolumns = uu_validate_user_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
	
}


?>
<?php echo 'Chandra'; 