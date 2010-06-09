<HTML xmlns="http://www.w3.org/1999/xhtml"> 
 <head>
  <title>
<?php global $titletext; echo "$titletext"; ?>&nbsp;
<?php global $yeartext; echo "$yeartext"; ?>
</title>
<style type="text/css">
table th { font-size: 11pt; font-weight: bold;  }
table td { font-size: 11pt; vertical-align: top; }
table tr    {  }
@media print {
   .noprint { display: none }
}


/* MJ */

div.control_area_even { overflow: auto; border-left: 1px solid #e9e9e9; border-right: 1px solid #e9e9e9; background: #e9e9e9; width: 100% }
div.control_area_odd {  overflow: auto; border-left: 1px solid #e9e9e9; border-right: 1px solid #e9e9e9; width: 100% }

div.rightbit { float: right; overflow: auto; }
div.leftbit  { float: left;  overflow: auto;}


/* MJ added the following.  CSS from Stephen Shorrock.*/
table.resTab {
    width:100%;
       border-collapse: collapse;
}

.resTab tr.odd {
     background: #D6DBE4;      vertical-align:top;
}

.resTab tr.even {
     background: #ffffff;      vertical-align:top;
}

.resTab td{

    padding:2px;
    border-left:dashed;
    border-width:thin;
}

.resTab th{

    font-weight: bold;
    padding:2px;
    border-left:solid;
    border-width:thin;

}


table.unit_table {
    width: 100%;
    border-collapse: collapse;
}



div.unit_table {
    width: 100%;
    page-break-inside: avoid;
}

table.unit_table th {
    font-weight: bold;
    font-size: 10pt;
    border: 1px solid black;
}

table.unit_table td {
    border: 1px solid silver;
    font-size: 10pt;
}


table.points_table {
    /*width: 50%;*/
    border-collapse: collapse;
}
table.points_table th {
    font-weight: bold;
    font-size: 10pt;
    border: 1px solid black;
    padding-right: 5px;
}

table.points_table td {
    border: 1px solid silver;
    /*padding-left: 5px;*/
    padding-right: 5px;
    font-size: 10pt;
}



h2.units_print {
    text-decoration: underline;
}


div.list_unit_table {
    border: 1px solid black;
    width: 30%;
    height: 500 px;
    float: left;
}



ul.unit_table {
        list-style: list-style-type;
    list-style-type: disc;
    list-style-image: none;
    list-style-position: inside;
    text-align: left;
    float: left;
}

table.unit_table th.ut_extra {
    font-weight: normal;
    color: black;
    font-size: 8 pt;
    vertical-align: bottom;
    border: solid 1px gray;
    background: silver;
    text-align: right;
    white-space: nowrap;
}

li.unit_table {
}

.numberstyle {
    text-align: right;
}

/* Background of empty timetable cells and faculty (stint) summary pages */
td.shaded {
    /* Caius blue! */
    background:   #CEF0D0
}

/* Operations by year box on the index page */
.opsbyyear {
    /*float: right;*/
    width: 25%;
}
/*.opsbyyear legend {
    font-weight: bold;
    border: 1px solid gray;
    background: white;
    padding-left: 1 em;
    padding-right: 1 em;
}
.opsbyyear fieldset {
    border: 1 px solid gray;
} */




</style>
