<?php
/*
 * Main layout file to send email templates
 * This file will be used in every email for frontend part
 * For change/modify the header and footer of all email just need to change this file only
 * 
 * IMPORTANT: All message files can be edited from frontend/emails/html[view file]
 * 
 */
?>

<body style="margin:0px; padding:0; background:#fff; color:#323232;font-family: 'Lato', sans-serif; box-sizing: border-box;">
		<div style="max-width:600px; width:100%; background-position:center bottom; padding:80px 25px 150px;  box-sizing: border-box;">		
				<div style="width:100%; margin:0 auto; text-align:center;  box-sizing: border-box;" >
					<?php echo $message;?>
					<p style="color:#323232;font-family: 'Lato', sans-serif; font-size:18px;"><b style="font-size:20px; font-weight:700">Thanks,</b><br> Team Workchew</p>
				</div>
			</div>				
	</body>

