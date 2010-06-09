// this code is designed to be included by a php require(), at a point
// in the including document within enclosing
// <script type=text/javascript> ... </script>
//
// its purpose is to hoover up the state of the main form within the
// page that called the popup, so that the popup can stash it in its
// own form, entirely as hidden inputs, for resubmission to the main
// page

function hoover_form_state() 
{
   callform = this.opener.document.forms[0]

   numelements = callform.length

   for (i = 0; i< numelements;  i++) {

      // submit boxes confuse things;  their "value" is the value they
      // would submit with, but that doesn't mean that's part of the current
      // state of the form
   
      // similarly, because we've chosen to use the existence of a 
      // checkbox in the $_POST array as being indicative of it being
      // set (NB this probably ought to get fixed at some stage), we
      // need to filter out the ones that are unchecked
      // AEC Nov 09 To please Firefox 3.5.5 we need to check for Submit too
   
      if ((callform.elements[i].type != 'submit') && 
             (callform.elements[i].type != 'Submit') && 
          ! ((callform.elements[i].type == 'checkbox') &&
             (callform.elements[i].checked == false))) {
   
         this.document.write('<input type="hidden" name="')
         this.document.write(callform.elements[i].name)
         this.document.write('" value="')
         this.document.write(callform.elements[i].value)
         this.document.writeln('">')
      }
   
   }
}

function view_jobs_state_fix()
{   

  // fix up the various bits of state.
  // basically, if we're not doing the special update, then we want to
  // force view_jobs.php to re-filter the state of things
  if ((this.opener.document.forms[0].filter_special.value == '') ||
      (this.opener.document.forms[0].filter_special.value == 
       'nofilter_special')) {
     if ((this.opener.document.forms[0].filter_specialstate.value != 'nofilter_special') && 
         (this.opener.document.forms[0].filter_specialstate.value != '')) {
        this.document.write('<input type="hidden" name="filter_special" value="')
        this.document.write(this.opener.document.forms[0].fiter_specialstate.value)
        this.document.writeln('">')
     } else {
        this.document.writeln('<input type="hidden" name="filter" value="filter">')
     }
  }
}

function view_people_state_fix()
{

}

<?php
if (array_key_exists('windowid', $_GET))
   $windowid = $_GET['windowid'];
else
   $windowid = "";
?>