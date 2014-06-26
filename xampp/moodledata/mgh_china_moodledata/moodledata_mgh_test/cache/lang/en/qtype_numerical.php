<?php $this->cache['en']['qtype_numerical'] = array (
  'acceptederror' => 'Accepted error',
  'addmoreanswerblanks' => 'Blanks for {no} More Answers',
  'addmoreunitblanks' => 'Blanks for {no} More Units',
  'answermustbenumberorstar' => 'The answer must be a number, for example -1.234 or 3e8, or \'*\'.',
  'answerno' => 'Answer {$a}',
  'decfractionofquestiongrade' => 'as a fraction (0-1) of the question grade',
  'decfractionofresponsegrade' => 'as a fraction (0-1) of the response grade',
  'decimalformat' => 'decimals',
  'editableunittext' => 'the text input element',
  'errornomultiplier' => 'You must specify a multiplier for this unit.',
  'errorrepeatedunit' => 'You cannot have two units with the same name.',
  'geometric' => 'Geometric',
  'invalidnumber' => 'You must enter a valid number.',
  'invalidnumbernounit' => 'You must enter a valid number. Do not include a unit in your response.',
  'invalidnumericanswer' => 'One of the answers you entered was not a valid number.',
  'invalidnumerictolerance' => 'One of the tolerances you entered was not a valid number.',
  'leftexample' => 'on the left, for example $1.00 or £1.00',
  'multiplier' => 'Multiplier',
  'noneditableunittext' => 'NON editable text of Unit No1',
  'nonvalidcharactersinnumber' => 'NON valid characters in number',
  'notenoughanswers' => 'You must enter at least one answer.',
  'nounitdisplay' => 'No unit grading',
  'numericalmultiplier' => 'Multiplier',
  'numericalmultiplier_help' => 'The multiplier is the factor by which the correct numerical response will be multiplied.

The first unit (Unit 1) has a default multiplier of 1. Thus if the correct numerical response is 5500 and you set W as unit at Unit 1 which has 1 as default multiplier, the correct response is 5500 W.

If you add the unit kW with a multiplier of 0.001, this will add a correct response of 5.5 kW. This means that the answers 5500W or 5.5kW would be marked correct.

Note that the accepted error is also multiplied, so an allowed error of 100W would become an error of 0.1kW.',
  'manynumerical' => 'Units are optional. If a unit is entered, it is used to convert the reponse to Unit 1 before grading.',
  'nominal' => 'Nominal',
  'onlynumerical' => 'Units are not used at all. Only the numerical value is graded.',
  'oneunitshown' => 'Unit 1 is automatically displayed beside the answer box.',
  'pleaseenterananswer' => 'Please enter an answer.',
  'pleaseenteranswerwithoutthousandssep' => 'Please enter your answer without using the thousand separator ({$a}).',
  'pluginname' => 'Numerical',
  'pluginname_help' => 'From the student perspective, a numerical question looks just like a short-answer question. The difference is that numerical answers are allowed to have an accepted error. This allows a fixed range of answers to be evaluated as one answer. For example, if the answer is 10 with an accepted error of 2, then any number between 8 and 12 will be accepted as correct. ',
  'pluginname_link' => 'question/type/numerical',
  'pluginnameadding' => 'Adding a Numerical question',
  'pluginnameediting' => 'Editing a Numerical question',
  'pluginnamesummary' => 'Allows a numerical response, possibly with units, that is graded by comparing against various model answers, possibly with tolerances.',
  'relative' => 'Relative',
  'rightexample' => 'on the right, for example 1.00cm or 1.00km',
  'selectunits' => 'Select units',
  'selectunit' => 'Select one unit',
  'studentunitanswer' => 'Units are input using',
  'tolerancetype' => 'Tolerance type',
  'unit' => 'Unit',
  'unitappliedpenalty' => 'These marks include a penalty of {$a} for bad unit.',
  'unitchoice' => 'a multiple choice selection',
  'unitedit' => 'Edit unit',
  'unitgraded' => 'The unit must be given, and will be graded.',
  'unithandling' => 'Unit handling',
  'unithdr' => 'Unit {$a}',
  'unitincorrect' => 'You did not give the correct unit.',
  'unitmandatory' => 'Mandatory',
  'unitmandatory_help' => '

* The response will be graded using the unit written.

* The unit penalty will be applied if the unit field is empty

',
  'unitnotselected' => 'You must select a unit.',
  'unitonerequired' => 'You must enter at least one unit',
  'unitoptional' => 'Optional unit',
  'unitoptional_help' => '
* If the unit field is not empty, the response will be graded using this unit.

* If the unit is badly written or unknown, the response will be considered as non valid.
',
  'unitpenalty' => 'Unit penalty',
  'unitpenalty_help' => 'The penalty is applied if

* the wrong unit name is entered into the unit input, or
* a unit is entered into the value input box',
  'unitposition' => 'Units go',
  'unitselect' => 'a drop-down menu',
  'validnumberformats' => 'Valid number formats',
  'validnumberformats_help' => '
* regular numbers 13500.67, 13 500.67, 13500,67 or 13 500,67

* if you use , as thousand separator *always* put the decimal . as in
 13,500.67 : 13,500.

* for exponent form, say 1.350067 * 10<sup>4</sup>, use
 1.350067 E4 : 1.350067 E04 ',
  'validnumbers' => '13500.67, 13 500.67, 13,500.67, 13500,67, 13 500,67, 1.350067 E4 or 1.350067 E04',
);