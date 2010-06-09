<?php echo $timetable_message; ?>
<p>
If the list of duties for <?php echo $yeartext; ?> is correct then please send confirmation to <?php echo $officename; ?> by pressing this button.
</p>
<p>
<button type="Submit" name="send_confirmation" 
   value="ConfirmDuties<?php echo $yearval; ?>">
Confirm to <?php echo $officename; ?> that this List of my Duties is OK</button>
<?php if (isset($duty_confirm_ack)) { echo $duty_confirm_ack; }; ?>
</p>
<br />
<br />
<hr />
<p>
If there is a problem with your list of duties for <?php echo $yeartext; ?> please send a message to <?php echo $officename; ?> using the box below.
</p>
<p>
<textarea name="email_text" rows="10" cols="65">
</textarea>
</p>
<p>
<button type="Submit" name="send_message" 
   value="SendMessage<?php echo $yearval; ?>">
Send the above message to <?php echo $officename; ?></button>
<?php if (isset($duty_message_ack)) { echo $duty_message_ack; }; ?>
</p>
