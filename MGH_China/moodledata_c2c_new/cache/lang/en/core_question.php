<?php $this->cache['en']['core_question'] = array (
  'addcategory' => 'Add category',
  'adminreport' => 'Report on possible problems in your question database.',
  'availableq' => 'Available?',
  'badbase' => 'Bad base before **: {$a}**',
  'behaviour' => 'Behaviour',
  'broken' => 'This is a "broken link", it points to a nonexistent file.',
  'byandon' => 'by <em>{$a->user}</em> on <em>{$a->time}</em>',
  'cannotcopybackup' => 'Could not copy backup file',
  'cannotcreate' => 'Could not create new entry in question_attempts table',
  'cannotcreatepath' => 'Cannot create path: {$a}',
  'cannotdeletebehaviourinuse' => 'You cannot delete the behaviour \'{$a}\'. It is used by question attempts.',
  'cannotdeletecate' => 'You can\'t delete that category it is the default category for this context.',
  'cannotdeletemissingbehaviour' => 'You cannot uninstall the missing behaviour. It is required by the system.',
  'cannotdeletemissingqtype' => 'You cannot uninstall the missing question type. It is needed by the system.',
  'cannotdeleteneededbehaviour' => 'Cannot delete the question behaviour \'{$a}\'. There are other behaviours installed that rely on it.',
  'cannotdeleteqtypeinuse' => 'You cannot delete the question type \'{$a}\'. There are questions of this type in the question bank.',
  'cannotdeleteqtypeneeded' => 'You cannot delete the question type \'{$a}\'. There are other question types installed that rely on it.',
  'cannotenable' => 'Question type {$a} cannot be created directly.',
  'cannotenablebehaviour' => 'Question behaviour {$a} cannot be used directly. It is for internal use only.',
  'cannotfindcate' => 'Could not find category record',
  'cannotfindquestionfile' => 'Could not find question data file in zip',
  'cannotgetdsfordependent' => 'Cannot get the specified dataset for a dataset dependent question! (question: {$a->id}, datasetitem: {$a->item})',
  'cannotgetdsforquestion' => 'Cannot get the specified dataset for a calculated question! (question: {$a})',
  'cannothidequestion' => 'Was not able to hide question',
  'cannotimportformat' => 'Sorry, importing this format is not yet implemented!',
  'cannotinsertquestion' => 'Could not insert new question!',
  'cannotinsertquestioncatecontext' => 'Could not insert the new question category {$a->cat} illegal contextid {$a->ctx}',
  'cannotloadquestion' => 'Could not load question',
  'cannotmovequestion' => 'You can\'t use this script to move questions that have files associated with them from different areas.',
  'cannotopenforwriting' => 'Cannot open for writing: {$a}',
  'cannotpreview' => 'You can\'t preview these questions!',
  'cannotread' => 'Cannot read import file (or file is empty)',
  'cannotretrieveqcat' => 'Could not retrieve question category',
  'cannotunhidequestion' => 'Failed to unhide the question.',
  'cannotunzip' => 'Could not unzip file.',
  'cannotwriteto' => 'Cannot write exported questions to {$a}',
  'categorycurrent' => 'Current category',
  'categorycurrentuse' => 'Use this category',
  'categorydoesnotexist' => 'This category does not exist',
  'categoryinfo' => 'Category info',
  'categorymove' => 'The category \'{$a->name}\' contains {$a->count} questions (some of them may be old, hidden, questions that are still in use in some existing quizzes). Please choose another category to move them to.',
  'categorymoveto' => 'Save in category',
  'categorynamecantbeblank' => 'The category name cannot be blank.',
  'clickflag' => 'Flag question',
  'clicktoflag' => 'Flag this question for future reference',
  'clicktounflag' => 'Remove flag',
  'clickunflag' => 'Remove flag',
  'contexterror' => 'You shouldn\'t have got here if you\'re not moving a category to another context.',
  'copy' => 'Copy from {$a} and change links.',
  'created' => 'Created',
  'createdby' => 'Created by',
  'createdmodifiedheader' => 'Created / last saved',
  'createnewquestion' => 'Create a new question ...',
  'cwrqpfs' => 'Random questions selecting questions from sub categories.',
  'cwrqpfsinfo' => '<p>During the upgrade to Moodle 1.9 we will separate question categories into
different contexts. Some question categories and questions on your site will have to have their sharing
status changed. This is necessary in the rare case that one or more \'random\' questions in a quiz are set up to select from a mixture of
shared and unshared categories (as is the case on this site). This happens when a \'random\' question is set to select
from subcategories and one or more subcategories have a different sharing status to the parent category in which
the random question is created.</p>
<p>The following question categories, from which \'random\' questions in parent categories select questions from,
will have their sharing status changed to the same sharing status as the category with the \'random\' question in
on upgrading to Moodle 1.9. The following categories will have their sharing status changed. Questions which are
affected will continue to work in all existing quizzes until you remove them from these quizzes.</p>',
  'cwrqpfsnoprob' => 'No question categories in your site are affected by the \'Random questions selecting questions from sub categories\' issue.',
  'defaultfor' => 'Default for {$a}',
  'defaultinfofor' => 'The default category for questions shared in context \'{$a}\'.',
  'deletebehaviourareyousure' => 'Delete behaviour {$a}: are you sure?',
  'deletebehaviourareyousuremessage' => 'You are about to completely delete the question behaviour {$a}. This will completely delete everything in the database associated with this question behaviour. Are you SURE you want to continue?',
  'deletecoursecategorywithquestions' => 'There are questions in the question bank associated with this course category. If you proceed, they will be deleted. You may wish to move them first, using the question bank interface.',
  'deleteqtypeareyousure' => 'Delete question type {$a}: are you sure?',
  'deleteqtypeareyousuremessage' => 'You are about to completely delete the question type {$a}. This will completely delete everything in the database associated with this question type. Are you SURE you want to continue?',
  'deletequestioncheck' => 'Are you absolutely sure you want to delete \'{$a}\'?',
  'deletequestionscheck' => 'Are you absolutely sure you want to delete the following questions?<br /><br />{$a}',
  'deletingbehaviour' => 'Deleting question behaviour \'{$a}\'',
  'deletingqtype' => 'Deleting question type \'{$a}\'',
  'didnotmatchanyanswer' => '[Did not match any answer]',
  'disabled' => 'Disabled',
  'disterror' => 'The distribution {$a} caused problems',
  'donothing' => 'Don\'t copy or move files or change links.',
  'editcategories' => 'Edit categories',
  'editcategories_help' => 'Rather than keeping everything in one big list, questions may be arranged into categories and subcategories.

Each category has a context which determines where the questions in the category can be used:

* Activity context - Questions only available in the activity module
* Course context - Questions available in all activity modules in the course
* Course category context - Questions available in all activity modules and courses in the course category 
* System context - Questions available in all courses and activities on the site

Categories are also used for random questions, as questions are selected from a particular category.',
  'editcategories_link' => 'question/category',
  'editcategory' => 'Edit category',
  'editingcategory' => 'Editing a category',
  'editingquestion' => 'Editing a question',
  'editquestion' => 'Edit question',
  'editthiscategory' => 'Edit this category',
  'emptyxml' => 'Unkown error - empty imsmanifest.xml',
  'enabled' => 'Enabled',
  'erroraccessingcontext' => 'Cannot access context',
  'errordeletingquestionsfromcategory' => 'Error deleting questions from category {$a}.',
  'errorduringpost' => 'Error occurred during post-processing!',
  'errorduringpre' => 'Error occurred during pre-processing!',
  'errorduringproc' => 'Error occurred during processing!',
  'errorduringregrade' => 'Could not regrade question {$a->qid}, going to state {$a->stateid}.',
  'errorfilecannotbecopied' => 'Error: cannot copy file {$a}.',
  'errorfilecannotbemoved' => 'Error: cannot move file {$a}.',
  'errorfileschanged' => 'Error: files linked to from questions have changed since form was displayed.',
  'errormanualgradeoutofrange' => 'The grade {$a->grade} is not between 0 and {$a->maxgrade} for question {$a->name}. The score and comment have not been saved.',
  'errormovingquestions' => 'Error while moving questions with ids {$a}.',
  'errorpostprocess' => 'Error occurred during post-processing!',
  'errorpreprocess' => 'Error occurred during pre-processing!',
  'errorprocess' => 'Error occurred during processing!',
  'errorprocessingresponses' => 'An error occurred while processing your responses ({$a}). Click continue to return to the page you were on and try again.',
  'errorsavingcomment' => 'Error saving the comment for question {$a->name} in the database.',
  'errorupdatingattempt' => 'Error updating attempt {$a->id} in the database.',
  'exportcategory' => 'Export category',
  'exportcategory_help' => 'This setting determines the category from which the exported questions will be taken.

Certain import formats, such as GIFT and Moodle XML, permit category and context data to be included in the export file, enabling them to (optionally) be recreated on import. If required, the appropriate checkboxes should be ticked.',
  'exporterror' => 'Errors occur during exporting!',
  'exportfilename' => 'questions',
  'exportnameformat' => '%Y%m%d-%H%M',
  'exportquestions' => 'Export questions to file',
  'exportquestions_help' => 'This function enables the export of a complete category (and any subcategories) of questions to file. Please note that, depending on the file format selected, some question data and certain question types may not be exported.',
  'exportquestions_link' => 'question/export',
  'filecantmovefrom' => 'The questions files cannot be moved because you do not have permission to remove files from the place you are trying to move questions from.',
  'filecantmoveto' => 'The question files cannot be moved or copied becuase you do not have permission to add files to the place you are trying to move the questions to.',
  'fileformat' => 'File format',
  'filesareacourse' => 'the course files area',
  'filesareasite' => 'the site files area',
  'filestomove' => 'Move / copy files to {$a}?',
  'flagged' => 'Flagged',
  'flagthisquestion' => 'Flag this question',
  'formquestionnotinids' => 'Form contained question that is not in questionids',
  'fractionsnomax' => 'One of the answers should have a score of 100% so it is possible to get full marks for this question.',
  'getcategoryfromfile' => 'Get category from file',
  'getcontextfromfile' => 'Get context from file',
  'changepublishstatuscat' => '<a href="{$a->caturl}">Category "{$a->name}"</a> in course "{$a->coursename}" will have it\'s sharing status changed from <strong>{$a->changefrom} to {$a->changeto}</strong>.',
  'chooseqtypetoadd' => 'Choose a question type to add',
  'editquestions' => 'Edit questions',
  'ignorebroken' => 'Ignore broken links',
  'impossiblechar' => 'Impossible character {$a} detected as parenthesis character',
  'importcategory' => 'Import category',
  'importcategory_help' => 'This setting determines the category into which the imported questions will go.

Certain import formats, such as GIFT and Moodle XML, may include category and context data in the import file. To make use of this data, rather than the selected category, the appropriate checkboxes should be ticked. If categories specified in the import file do not exist, they will be created.',
  'importerror' => 'An error occurred during import processing',
  'importerrorquestion' => 'Error importing question',
  'importingquestions' => 'Importing {$a} questions from file',
  'importparseerror' => 'Error(s) found parsing the import file. No questions have been imported. To import any good questions try again setting \'Stop on error\' to \'No\'',
  'importquestions' => 'Import questions from file',
  'importquestions_help' => 'This function enables questions in a variety of formats to be imported via text file. Note that the file must use UTF-8 encoding.',
  'importquestions_link' => 'question/import',
  'importwrongfiletype' => 'The type of the file you selected ({$a->actualtype}) does not match the type expected by this import format ({$a->expectedtype}).',
  'invalidarg' => 'No valid arguments supplied or incorrect server configuration',
  'invalidcategoryidforparent' => 'Invalid category id for parent!',
  'invalidcategoryidtomove' => 'Invalid category id to move!',
  'invalidconfirm' => 'Confirmation string was incorrect',
  'invalidcontextinhasanyquestions' => 'Invalid context passed to question_context_has_any_questions.',
  'invalidpenalty' => 'Invalid penalty',
  'invalidwizardpage' => 'Incorrect or no wizard page specified!',
  'lastmodifiedby' => 'Last modified by',
  'linkedfiledoesntexist' => 'Linked file {$a} doesn\'t exist',
  'makechildof' => 'Make child of \'{$a}\'',
  'maketoplevelitem' => 'Move to top level',
  'matcherror' => 'Grades do not match grade options - question skipped',
  'matchgrades' => 'Match grades',
  'matchgradeserror' => 'Error if grade not listed',
  'matchgradesnearest' => 'Nearest grade if not listed',
  'matchgrades_help' => 'Imported grades must match one of the fixed list of valid grades - 100, 90, 80, 75, 70, 66.666, 60, 50, 40, 33.333, 30, 25, 20, 16.666, 14.2857, 12.5, 11.111, 10, 5, 0 (also negative values). If not, there are two options:

*  Error if grade not listed - If a question contains any grades not found in the list an error is displayed and that question will not be imported
* Nearest grade if not listed - If a grade is found that does not match a value in the list, the grade is changed to the closest matching value in the list ',
  'missingcourseorcmid' => 'Need to provide courseid or cmid to print_question.',
  'missingcourseorcmidtolink' => 'Need to provide courseid or cmid to get_question_edit_link.',
  'missingimportantcode' => 'This question type is missing important code: {$a}.',
  'missingoption' => 'The cloze question {$a} is missing its options',
  'modified' => 'Last saved',
  'move' => 'Move from {$a} and change links.',
  'movecategory' => 'Move category',
  'movedquestionsandcategories' => 'Moved questions and question categories from {$a->oldplace} to {$a->newplace}.',
  'movelinksonly' => 'Just change where links point to, do not move or copy files.',
  'moveq' => 'Move question(s)',
  'moveqtoanothercontext' => 'Move question to another context.',
  'moveto' => 'Move to >>',
  'movingcategory' => 'Moving category',
  'movingcategoryandfiles' => 'Are you sure you want to move category {$a->name} and all child categories to context for "{$a->contextto}"?<br /> We have detected {$a->urlcount} files linked from questions in {$a->fromareaname}, would you like to copy or move these to {$a->toareaname}?',
  'movingcategorynofiles' => 'Are you sure you want to move category "{$a->name}" and all child categories to context for "{$a->contextto}"?',
  'movingquestions' => 'Moving questions and any files',
  'movingquestionsandfiles' => 'Are you sure you want to move question(s) {$a->questions} to context for <strong>"{$a->tocontext}"</strong>?<br /> We have detected <strong>{$a->urlcount} files</strong> linked from these question(s) in {$a->fromareaname}, would you like to copy or move these to {$a->toareaname}?',
  'movingquestionsnofiles' => 'Are you sure you want to move question(s) {$a->questions} to context for <strong>"{$a->tocontext}"</strong>?<br /> There are <strong>no files</strong> linked from these question(s) in {$a->fromareaname}.',
  'needtochoosecat' => 'You need to choose a category to move this question to or press \'cancel\'.',
  'nocate' => 'No such category {$a}!',
  'nopermissionadd' => 'You don\'t have permission to add questions here.',
  'nopermissionmove' => 'You don\'t have permission to move questions from here. You must save the question in this category or save it as a new question.',
  'noprobs' => 'No problems found in your question database.',
  'noquestionsinfile' => 'There are no questions in the import file',
  'notenoughanswers' => 'This type of question requires at least {$a} answers',
  'notenoughdatatoeditaquestion' => 'Neither a question id, nor a category id and question type, was specified.',
  'notenoughdatatomovequestions' => 'You need to provide the question ids of questions you want to move.',
  'notflagged' => 'Not flagged',
  'novirtualquestiontype' => 'No virtual question type for question type {$a}',
  'numqas' => 'No. question attempts',
  'numquestions' => 'No. questions',
  'numquestionsandhidden' => '{$a->numquestions} (+{$a->numhidden} hidden)',
  'page-question-x' => 'Any question page',
  'page-question-edit' => 'Question editing page',
  'page-question-category' => 'Question category page',
  'page-question-import' => 'Question import page',
  'page-question-export' => 'Question export page',
  'parentcategory' => 'Parent category',
  'parentcategory_help' => 'The parent category is the one in which the new category will be placed. "Top" means that this category is not contained in any other category. Category contexts are shown in bold type. There must be at least one category in each context.',
  'parentcategory_link' => 'question/category',
  'parenthesisinproperclose' => 'Parenthesis before ** is not properly closed in {$a}**',
  'parenthesisinproperstart' => 'Parenthesis before ** is not properly started in {$a}**',
  'parsingquestions' => 'Parsing questions from import file.',
  'penaltyfactor' => 'Penalty factor',
  'penaltyfactor_help' => 'This setting determines what fraction of the achieved score is subtracted for each wrong response. It is only applicable if the quiz is run in adaptive mode.

The penalty factor should be a number between 0 and 1. A penalty factor of 1 means that the student has to get the answer right in his first response to get any credit for it at all. A penalty factor of 0 means the student can try as often as he likes and still get the full marks.',
  'permissionedit' => 'Edit this question',
  'permissionmove' => 'Move this question',
  'permissionsaveasnew' => 'Save this as a new question',
  'permissionto' => 'You have permission to :',
  'published' => 'shared',
  'qbehaviourdeletefiles' => 'All data associated with the question behaviour \'{$a->behaviour}\' has been deleted from the database. To complete the deletion (and to prevent the behaviour from re-installing itself), you should now delete this directory from your server: {$a->directory}',
  'qtypedeletefiles' => 'All data associated with the question type \'{$a->qtype}\' has been deleted from the database. To complete the deletion (and to prevent the question type from re-installing itself), you should now delete this directory from your server: {$a->directory}',
  'qtypeveryshort' => 'T',
  'questionaffected' => '<a href="{$a->qurl}">Question "{$a->name}" ({$a->qtype})</a> is in this question category but is also being used in <a href="{$a->qurl}">quiz "{$a->quizname}"</a> in another course "{$a->coursename}".',
  'questionbank' => 'Question bank',
  'questioncategory' => 'Question category',
  'questioncatsfor' => 'Question categories for \'{$a}\'',
  'questiondoesnotexist' => 'This question does not exist',
  'questionname' => 'Question name',
  'questionno' => 'Question {$a}',
  'questionsaveerror' => 'Errors occur during saving question - ({$a})',
  'questionsinuse' => '(* Questions marked by an asterisk are already in use in some quizzes. These question will not be deleted from these quizzes but only from the category list.)',
  'questionsmovedto' => 'Questions still in use moved to "{$a}" in the parent course category.',
  'questionsrescuedfrom' => 'Questions saved from context {$a}.',
  'questionsrescuedfrominfo' => 'These questions (some of which may be hidden) were saved when context {$a} was deleted because they are still used by some quizzes or other activities.',
  'questiontype' => 'Question type',
  'questionuse' => 'Use question in this activity',
  'questionvariant' => 'Question variant',
  'reviewresponse' => 'Review response',
  'saveflags' => 'Save the state of the flags',
  'selectacategory' => 'Select a category:',
  'selectaqtypefordescription' => 'Select a question type to see its description.',
  'selectcategoryabove' => 'Select a category above',
  'selectquestionsforbulk' => 'Select questions for bulk actions',
  'shareincontext' => 'Share in context for {$a}',
  'stoponerror' => 'Stop on error',
  'stoponerror_help' => 'This setting determines whether the import process stops when an error is detected, resulting in no questions being imported, or whether any questions containing errors are ignored and any valid questions are imported.',
  'tofilecategory' => 'Write category to file',
  'tofilecontext' => 'Write context to file',
  'uninstallbehaviour' => 'Uninstall this question behaviour.',
  'uninstallqtype' => 'Uninstall this question type.',
  'unknown' => 'Unknown',
  'unknownquestiontype' => 'Unknown question type: {$a}.',
  'unknowntolerance' => 'Unknown tolerance type {$a}',
  'unpublished' => 'unshared',
  'upgradeproblemcategoryloop' => 'Problem detected when upgrading question categories. There is a loop in the category tree. The affected category ids are {$a}.',
  'upgradeproblemcouldnotupdatecategory' => 'Could not update question category {$a->name} ({$a->id}).',
  'upgradeproblemunknowncategory' => 'Problem detected when upgrading question categories. Category {$a->id} refers to parent {$a->parent}, which does not exist. Parent changed to fix problem.',
  'wrongprefix' => 'Wrongly formatted nameprefix {$a}',
  'youmustselectaqtype' => 'You must select a question type.',
  'yourfileshoulddownload' => 'Your export file should start to download shortly. If not, please <a href="{$a}">click here</a>.',
  'action' => 'Action',
  'addanotherhint' => 'Add another hint',
  'answer' => 'Answer',
  'answersaved' => 'Answer saved',
  'attemptfinished' => 'Attempt finished',
  'attemptfinishedsubmitting' => 'Attempt finished submitting: ',
  'behaviourbeingused' => 'behaviour being used: {$a}',
  'category' => 'Category',
  'changeoptions' => 'Change options',
  'check' => 'Check',
  'clearwrongparts' => 'Clear incorrect responses',
  'closepreview' => 'Close preview',
  'combinedfeedback' => 'Combined feedback',
  'commented' => 'Commented: {$a}',
  'comment' => 'Comment',
  'commentormark' => 'Make comment or override mark',
  'comments' => 'Comments',
  'commentx' => 'Comment: {$a}',
  'complete' => 'Complete',
  'correct' => 'Correct',
  'correctfeedback' => 'For any correct response',
  'decimalplacesingrades' => 'Decimal places in grades',
  'defaultmark' => 'Default mark',
  'errorsavingflags' => 'Error saving the flag state.',
  'feedback' => 'Feedback',
  'fillincorrect' => 'Fill in correct responses',
  'generalfeedback' => 'General feedback',
  'generalfeedback_help' => 'General feedback is shown to the student after they have attempted the question. Unlike feedback, which depends on the question type and what response the student gave, the same general feedback text is shown to all students.

You can use the general feedback to give students some background to what knowledge the question was testing, or give them a link to more information they can use if they did not understand the questions.',
  'hidden' => 'Hidden',
  'hintn' => 'Hint {no}',
  'hinttext' => 'Hint text',
  'howquestionsbehave' => 'How questions behave',
  'howquestionsbehave_help' => 'Students can interact with the questions in the quiz in various different ways. For example, you may wish the students to enter an answer to each question and then submit the entire quiz, before anything is graded or they get any feedback. That would be \'Deferred feedback\' mode. Alternatively, you may wish for students to submit each question as they go along to get immediate feedback, and if they do not get it right immediately, have another try for fewer marks. That would be \'Interactive with multiple tries\' mode.',
  'importfromcoursefiles' => '... or choose a course file to import.',
  'importfromupload' => 'Select a file to upload ...',
  'includesubcategories' => 'Also show questions from sub-categories',
  'incorrect' => 'Incorrect',
  'incorrectfeedback' => 'For any incorrect response',
  'information' => 'Information',
  'invalidanswer' => 'Incomplete answer',
  'makecopy' => 'Make copy',
  'manualgradeoutofrange' => 'This grade is outside the valid range.',
  'manuallygraded' => 'Manually graded {$a->mark} with comment: {$a->comment}',
  'mark' => 'Mark',
  'markedoutof' => 'Marked out of',
  'markedoutofmax' => 'Marked out of {$a}',
  'markoutofmax' => 'Mark {$a->mark} out of {$a->max}',
  'marks' => 'Marks',
  'noresponse' => '[No response]',
  'notanswered' => 'Not answered',
  'notgraded' => 'Not graded',
  'notshown' => 'Not shown',
  'notyetanswered' => 'Not yet answered',
  'notyourpreview' => 'This preview does not belong to you',
  'options' => 'Options',
  'parent' => 'Parent',
  'partiallycorrect' => 'Partially correct',
  'partiallycorrectfeedback' => 'For any partially correct response',
  'penaltyforeachincorrecttry' => 'Penalty for each incorrect try',
  'penaltyforeachincorrecttry_help' => 'When you run your questions using the \'Interactive with multiple tries\' or \'Adaptive mode\' behaviour, so that the the student will have several tries to get the question right, then this option controls how much they are penalised for each incorrect try.

The penalty is a proportion of the total question grade, so if the question is worth three marks, and the penalty is 0.3333333, then the student will score 3 if they get the question right first time, 2 if they get it right second try, and 1 of they get it right on the third try.',
  'previewquestion' => 'Preview question: {$a}',
  'questionbehaviouradminsetting' => 'Question behaviour settings',
  'questionbehavioursdisabled' => 'Question behaviours to disable',
  'questionbehavioursdisabledexplained' => 'Enter a comma separated list of behaviours you do not want to appear in dropdown menu',
  'questionbehavioursorder' => 'Question behaviours order',
  'questionbehavioursorderexplained' => 'Enter a comma separated list of behaviours in the order you want them to appear in dropdown menu',
  'questionidmismatch' => 'Question ids mismatch',
  'questions' => 'Questions',
  'questionx' => 'Question {$a}',
  'questiontext' => 'Question text',
  'requiresgrading' => 'Requires grading',
  'responsehistory' => 'Response history',
  'restart' => 'Start again',
  'restartwiththeseoptions' => 'Start again with these options',
  'rightanswer' => 'Right answer',
  'saved' => 'Saved: {$a}',
  'settingsformultipletries' => 'Settings for multiple tries',
  'showhidden' => 'Also show old questions',
  'showmarkandmax' => 'Show mark and max',
  'showmaxmarkonly' => 'Show max mark only',
  'showquestiontext' => 'Show question text in the question list',
  'shown' => 'Shown',
  'shownumpartscorrect' => 'Show the number of correct responses',
  'shownumpartscorrectwhenfinished' => 'Show the number of correct responses once the question has finished',
  'specificfeedback' => 'Specific feedback',
  'started' => 'Started',
  'state' => 'State',
  'step' => 'Step',
  'submissionoutofsequence' => 'Access out of sequence. Please do not click the back button when working on quiz questions.',
  'submissionoutofsequencefriendlymessage' => 'You have entered data outside the normal sequence. This can occur if you use your browser\'s Back or Forward buttons; please don\'t use these during the test. It can also happen if you click on something while a page is loading. Click <strong>Continue</strong> to resume.',
  'submit' => 'Submit',
  'submitandfinish' => 'Submit and finish',
  'submitted' => 'Submit: {$a}',
  'unknownbehaviour' => 'Unknown behaviour: {$a}.',
  'unknownquestion' => 'Unknown question: {$a}.',
  'unknownquestioncatregory' => 'Unknown question category: {$a}.',
  'whethercorrect' => 'Whether correct',
  'withselected' => 'With selected',
  'xoutofmax' => '{$a->mark} out of {$a->max}',
  'yougotnright' => 'You have correctly selected {$a->num}.',
);