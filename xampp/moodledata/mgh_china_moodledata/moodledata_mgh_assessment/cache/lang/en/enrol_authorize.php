<?php $this->cache['en']['enrol_authorize'] = array (
  'authorize:config' => 'Configure Authorize.Net enroll instances',
  'authorize:manage' => 'Manage enrolled users',
  'authorize:unenrol' => 'Unenroll users from course',
  'authorize:unenrolself' => 'Unenroll self from the course',
  'authorize:managepayments' => 'Manage payments',
  'authorize:uploadcsv' => 'Upload CSV file',
  'adminacceptccs' => 'Which credit card types will be accepted?',
  'adminaccepts' => 'Select payment methods allowed and their types',
  'anauthcode' => 'Obtain authcode',
  'anauthcodedesc' => 'If a user\'s credit card cannot be captured on the internet directly, obtain authorization code over the phone from customer\'s bank.',
  'adminauthorizeccapture' => 'Order review and scheduled-capture settings',
  'adminauthorizeemail' => 'Email sending settings',
  'adminauthorizesettings' => 'Authorize.Net merchant account settings',
  'adminauthorizewide' => 'General settings',
  'anavs' => 'Address Verification System',
  'anavsdesc' => 'Check this if you have activated Address Verification System (AVS) in your Authorize.Net merchant account. This demands address fields like street, state, country and zip when user fills out payment form.',
  'adminconfighttps' => 'Please ensure that you have "<a href="{$a->url}">turned loginhttps ON</a>" to use this plugin<br />in Admin >> Variables >> Security >> HTTP security.',
  'adminconfighttpsgo' => 'Go to the <a href="{$a->url}">secure page</a> to configure this plugin.',
  'admincronsetup' => 'The cron.php maintenance script has not been run for at least 24 hours.<br />Cron must be enabled if you want to use scheduled-capture feature.<br /><b>Enable</b> \'Authorize.Net plugin\' and <b>setup cron</b> properly; or <b>uncheck an_review</b> again.<br />If you disable scheduled-capture, transactions will be cancelled unless you review them within 30 days.<br />Check <b>an_review</b> and enter <b>\'0\' to an_capture_day</b> field<br />if you want to <b>manually</b> accept/deny payments within 30 days.',
  'anemailexpired' => 'Expiry notification',
  'anemailexpireddesc' => 'This is useful for \'manual-capture\'. Admins are notified the specified amount of days prior to pending orders expiring.',
  'adminemailexpiredsort' => 'When the number of pending orders expiring are sent to the teachers via email, which one is important?',
  'adminemailexpiredsortcount' => 'Order count',
  'adminemailexpiredsortsum' => 'Total amount',
  'anemailexpiredteacher' => 'Expiry notification - Teacher',
  'anemailexpiredteacherdesc' => 'If you have enabled manual-capture (see above) and teachers can manage the payments, they may also notified about pending orders expiring. This will send an email to each course teachers about the count of the pending orders to expire.',
  'adminemailexpsetting' => '(0=disable sending email, default=2, max=5)<br />(Manual capture settings for sending email: cron=enabled, an_review=checked, an_capture_day=0, an_emailexpired=1-5)',
  'adminhelpcapturetitle' => 'Scheduled-capture day',
  'adminhelpreviewtitle' => 'Order review',
  'adminneworder' => 'Dear Admin,

  You have received a new pending order:

   Order ID: {$a->orderid}
   Transaction ID: {$a->transid}
   User: {$a->user}
   Course: {$a->course}
   Amount: {$a->amount}

   SCHEDULED-CAPTURE ENABLED?: {$a->acstatus}

  If the scheduled-capture is active, the credit card is to be captured on {$a->captureon}
  and then the user is to be enrolled to course; otherwise it will be expired
  on {$a->expireon} and cannot be captured after this day.

  You can also accept/deny the payment to enroll the student immediately following this link:
  {$a->url}',
  'adminnewordersubject' => '{$a->course}: New pending order: {$a->orderid}',
  'adminpendingorders' => 'You have disabled scheduled-capture feature.<br />Total {$a->count} transactions with the status of \'Authorized/Pending capture\' are to be cancelled unless you check them.<br />To accept/deny payments, go to <a href=\'{$a->url}\'>Payment Management</a> page.',
  'anreview' => 'Review',
  'anreviewdesc' => 'Review order before processing the credit card.',
  'adminteachermanagepay' => 'Teachers can manage the payments of the course.',
  'allpendingorders' => 'All pending orders',
  'amount' => 'Amount',
  'anlogin' => 'Authorize.Net: Login name',
  'anpassword' => 'Authorize.Net: Password',
  'anreferer' => 'Referer',
  'anrefererdesc' => 'Define the URL referer if you have set up this in your Authorize.Net merchant account. This will send a line "Referer: URL" embedded in the web request.',
  'antestmode' => 'Test mode',
  'antestmodedesc' => 'Run transactions in test mode only (no money will be drawn)',
  'antrankey' => 'Authorize.Net: Transaction key',
  'approvedreview' => 'Approved review',
  'authcaptured' => 'Authorized / Captured',
  'authcode' => 'Authorization code',
  'authorizedpendingcapture' => 'Authorized / Pending capture',
  'authorizeerror' => 'Authorize.Net error: {$a}',
  'avsa' => 'Address (street) matches, postal code does not',
  'avsb' => 'Address information not provided',
  'avse' => 'Address Verification System error',
  'avsg' => 'Non-U.S. card issuing bank',
  'avsn' => 'No match on address (street) nor postal code',
  'avsp' => 'Address Verification System not applicable',
  'avsr' => 'Retry - system unavailable or timed out',
  'avsresult' => 'AVS result: {$a}',
  'avss' => 'Service not supported by issuer',
  'avsu' => 'Address information is unavailable',
  'avsw' => '9 digit postal code matches, address (street) does not',
  'avsx' => 'Address (street) and 9 digit postal code match',
  'avsy' => 'Address (street) and 5 digit postal code match',
  'avsz' => '5 digit postal code matches, address (street) does not',
  'canbecredit' => 'Can be refunded to {$a->upto}',
  'cancelled' => 'Cancelled',
  'capture' => 'Capture',
  'capturedpendingsettle' => 'Captured / Pending settlement',
  'capturedsettled' => 'Captured / Settled',
  'captureyes' => 'The credit card will be captured and the student will be enrolled to the course. Are you sure?',
  'ccexpire' => 'Expiry date',
  'ccexpired' => 'The credit card has expired',
  'ccinvalid' => 'Invalid card number',
  'cclastfour' => 'CC last four',
  'ccno' => 'Credit card number',
  'cctype' => 'Credit card type',
  'ccvv' => 'Card verification',
  'ccvvhelp' => 'Look at the back of card (last 3 digits)',
  'costdefaultdesc' => '<strong>In course settings, enter -1</strong> to use this default cost to course cost field.',
  'cutofftime' => 'Cut-off time',
  'cutofftimedesc' => 'Transaction cut-off time. When the last transaction is picked up for settlement?',
  'dataentered' => 'Data entered',
  'delete' => 'Destroy',
  'description' => 'The Authorize.Net module allows you to set up paid courses via payment providers. Two ways to set the course cost (1) a site-wide cost as a default for the whole site or (2) a course setting that you can set for each course individually. The course cost overrides the site cost.',
  'echeckabacode' => 'Bank ABA number',
  'echeckaccnum' => 'Bank account number',
  'echeckacctype' => 'Bank account type',
  'echeckbankname' => 'Bank name',
  'echeckbusinesschecking' => 'Business checking',
  'echeckfirslasttname' => 'Bank account owner',
  'echeckchecking' => 'Checking',
  'echecksavings' => 'Savings',
  'enrolname' => 'Authorize.Net payment gateway',
  'expired' => 'Expired',
  'haveauthcode' => 'I have already an authorization code',
  'howmuch' => 'How much?',
  'httpsrequired' => 'We are sorry to inform you that your request cannot be processed now. This site\'s configuration couldn\'t be set up correctly.<br /><br />Please don\'t enter your credit card number unless you see a yellow lock at the bottom of the browser. If the symbol appears, it means the page encrypts all data sent between client and server. So the information during the transaction between the two computers is protected, hence your credit card number cannot be captured over the internet.',
  'choosemethod' => 'If you know the enrolment key of the cource, please enter it below;<br />Otherwise you need to pay for this course.',
  'chooseone' => 'Fill one or both of the following two fields. The password isn\'t shown.',
  'invalidaba' => 'Invalid ABA number',
  'invalidaccnum' => 'Invalid account number',
  'invalidacctype' => 'Invalid account type',
  'isbusinesschecking' => 'Is business checking?',
  'logindesc' => 'This option must be ON. <br /><br />Please ensure that you have turned <a href="{$a->url}">loginhttps ON</a> in Admin >> Variables >> Security.<br /><br />Turning this on will make Moodle use a secure https connection just for the login and payment pages.',
  'logininfo' => 'When configuring your Authorize.Net account, the login name is required and you must enter <strong>either</strong> the transaction key <strong>or</strong> the password in the appropriate box. We recommend you enter the transaction key due to security precautions.',
  'methodcc' => 'Credit card',
  'methodccdesc' => 'Select credit card and accepted types below',
  'methodecheck' => 'eCheck (ACH)',
  'methodecheckdesc' => 'Select eCheck and accepted types below',
  'missingaba' => 'Missing ABA number',
  'missingaddress' => 'Missing address',
  'missingbankname' => 'Missing bank name',
  'missingcc' => 'Missing card number',
  'missingccauthcode' => 'Missing authorization code',
  'missingccexpiremonth' => 'Missing expiration month',
  'missingccexpireyear' => 'Missing expiration year',
  'missingcctype' => 'Missing card type',
  'missingcvv' => 'Missing verification number',
  'missingzip' => 'Missing postal code',
  'mypaymentsonly' => 'Show my payments only',
  'nameoncard' => 'Name on card',
  'new' => 'New',
  'noreturns' => 'No returns!',
  'notsettled' => 'Not settled',
  'orderdetails' => 'Order details',
  'orderid' => 'OrderID',
  'paymentmanagement' => 'Payment management',
  'paymentmethod' => 'Payment method',
  'paymentpending' => 'Your payment is pending for this course with this order number {$a->orderid}.  See <a href=\'{$a->url}\'>Order Details</a>.',
  'pendingecheckemail' => 'Dear manager,

There are {$a->count} pending echecks now and you have to upload a csv file to get the users enrolled.

Click the link and read the help file on the page seen:
{$a->url}',
  'pendingechecksubject' => '{$a->course}: Pending eChecks({$a->count})',
  'pendingordersemail' => 'Dear admin,

{$a->pending} transactions for course "{$a->course}" will expire unless you accept payment within {$a->days} days.

This is a warning message, because you didn\'t enable scheduled-capture.
It means you have to accept or deny payments manually.

To accept/deny pending payments go to:
{$a->url}

To enable scheduled-capture, it means you will not receive any warning emails anymore, go to:

{$a->enrolurl}',
  'pendingordersemailteacher' => 'Dear teacher,

{$a->pending} transactions costed {$a->currency} {$a->sumcost} for course "{$a->course}"
will expire unless you accept payment with in {$a->days} days.

You have to accept or deny payments manually because of the admin hasn\'t enabled the scheduled-capture.

{$a->url}',
  'pendingorderssubject' => 'WARNING: {$a->course}, {$a->pending} order(s) will expire within {$a->days} day(s).',
  'pluginname' => 'Authorize.Net',
  'reason11' => 'A duplicate transaction has been submitted.',
  'reason13' => 'The merchant Login ID is invalid or the account is inactive.',
  'reason16' => 'The transaction was not found.',
  'reason17' => 'The merchant does not accept this type of credit card.',
  'reason245' => 'This eCheck type is not allowed when using the payment gateway hosted payment form.',
  'reason246' => 'This eCheck type is not allowed.',
  'reason27' => 'The transaction resulted in an AVS mismatch. The address provided does not match billing address of cardholder.',
  'reason28' => 'The merchant does not accept this type of credit card.',
  'reason30' => 'The configuration with the processor is invalid. Call merchant service provider.',
  'reason39' => 'The supplied currency code is either invalid, not supported, not allowed for this merchant or doesn\'t have an exchange rate.',
  'reason43' => 'The merchant was incorrectly set up at the processor. Call your merchant service provider.',
  'reason44' => 'This transaction has been declined. Card code filter error!',
  'reason45' => 'This transaction has been declined. Card code / AVS filter error!',
  'reason47' => 'The amount requested for settlement may not be greater than the original amount authorized.',
  'reason5' => 'A valid amount is required.',
  'reason50' => 'This transaction is awaiting settlement and cannot be refunded.',
  'reason51' => 'The sum of all credits against this transaction is greater than the original transaction amount.',
  'reason54' => 'The referenced transaction does not meet the criteria for issuing a credit.',
  'reason55' => 'The sum of credits against the referenced transaction would exceed the original debit amount.',
  'reason56' => 'This merchant accepts eCheck (ACH) transactions only; no credit card transactions are accepted.',
  'refund' => 'Refund',
  'refunded' => 'Refunded',
  'returns' => 'Returns',
  'ancaptureday' => 'Capture day',
  'ancapturedaydesc' => 'Capture the credit card automatically unless a teacher or administrator review the order within the specified days. CRON MUST BE ENABLED.<br />(0 days means it will disable scheduled-capture, also means teacher or admin review order manually. Transaction will be cancelled if you disable scheduled-capture or unless you review it within 30 days.)',
  'reviewfailed' => 'Review failed',
  'reviewnotify' => 'Your payment will be reviewed. Expect an email within a few days from your teacher.',
  'sendpaymentbutton' => 'Send payment',
  'settled' => 'Settled',
  'settlementdate' => 'Settlement date',
  'shopper' => 'Shopper',
  'subvoidyes' => 'The transaction refunded ({$a->transid}) is going to be cancelled and this will cause crediting {$a->amount} to your account. Are you sure?',
  'tested' => 'Tested',
  'testmode' => '[TEST MODE]',
  'testwarning' => 'Capturing/Voiding/Refunding seems working in test mode, but no record was updated or inserted in database.',
  'transid' => 'TransactionID',
  'underreview' => 'Under review',
  'unenrolstudent' => 'Unenroll student?',
  'uploadcsv' => 'Upload a CSV file',
  'usingccmethod' => 'Enroll using <a href="{$a->url}"><strong>Credit Card</strong></a>',
  'usingecheckmethod' => 'Enroll using <a href="{$a->url}"><strong>eCheck</strong></a>',
  'verifyaccount' => 'Verify your Authorize.Net merchant account',
  'verifyaccountresult' => '<b>Verification result:</b> {$a}',
  'void' => 'Void',
  'voidyes' => 'The transaction will be cancelled. Are you sure?',
  'welcometocoursesemail' => 'Dear {$a->name},

Thanks for your payments. You have enrolled these courses:

{$a->courses}

You may view your payment details or edit your profile:
 {$a->paymenturl}
 {$a->profileurl}',
  'youcantdo' => 'You can\'t do this action: {$a->action}',
  'zipcode' => 'Zip code',
  'cost' => 'Cost',
  'currency' => 'Currency',
  'enrolperiod' => 'Enrolment duration',
  'enrolstartdate' => 'Start date',
  'enrolenddate' => 'End date',
  'enrolenddaterror' => 'Enrolment end date cannot be earlier than start date',
  'status' => 'Allow Autorize.Net enrolments',
  'nocost' => 'There is no cost associated with enrolling in this course via Authorize.Net!',
  'firstnameoncard' => 'Firstname on card',
  'lastnameoncard' => 'Lastname on card',
  'expiremonth' => 'Expiry month',
  'expireyear' => 'Expiry year',
  'cccity' => 'City',
  'ccstate' => 'State',
  'unenrolselfconfirm' => 'Do you really want to unenroll yourself from course "{$a}"?',
);