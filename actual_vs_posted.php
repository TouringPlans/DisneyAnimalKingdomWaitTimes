<?php
####################################################################################################
#
#
# Associates an actual wait time with the posted wait times immediately before and after it, as
# long as those posted wait times appear within $MAX_SECONDS_BETWEEN_SAMPLES (see below).
#
#
# Usage:
#
#		php -f actual_vs_posted.php -- -i inputfile.csv > attraction_out.csv
#
#
# Options:
#		-i : name of input file to read
#
# Assumes:
#		CSV format
#		Input fields:
#			date,datetime,SPOSTMIN,SACTMIN
#
#
# Author: len@touringplans.com
#
##################################################################################################
		
#######################
# FUNCTIONS GO HERE 
#######################


############################
# Global variables go here
############################
$input_filename="";

$posted_waits=array();
$actual_waits=array();


//////////////////////////////////////////////////////
// How many seconds can elapse between valid samples
// There are 60 seconds in a minute, so use (10 * 60)
// if you want a 10-minute window
//
// Make this a command-line option if you want to try
// different values here. (See the next graf for
// command-line parsing.)
//////////////////////////////////////////////////////
$MAX_SECONDS_BETWEEN_SAMPLES = (10 * 60);


#######################
# MAIN CODE GOES HERE
#######################

////////////////////////////////////////
//parse the command line options here
////////////////////////////////////////
$options = getopt("i:");
foreach($options as $opt => $value)
{
	//echo "opt=[$opt]\n";
	if ($opt == "i")
		$input_filename=$options[$opt];
	if ($opt == "ttttttttt")
		;
} // foreach




///////////////////////////////////////////////
// Make sure we can read the file given
///////////////////////////////////////////////
$file = fopen($input_filename, 'r');
if (! $file)
{
	echo "Can't open [" . $input_filename . "] for reading. Exiting.\n";
	exit(1);
} // if


//////////////////////////////////////////////////////////
// Parse the filename and extract the park and attraction
// By convention, filenames are XX###.csv, where XX is 
// a 2-letter abbreviation for the park, and ### is a 2-
// or 3-digit number representing the attraction.
//////////////////////////////////////////////////////////
$basenm = basename($input_filename);
$park=substr($basenm,0,2);
$dot=strpos($basenm,".");
$attr=substr($basenm,2,$dot-2);


///////////////////////////////////////////////////////////////////
// Read each line of the file, figure out whether it's an actual
// or posted wait time, and decide whether to save it
///////////////////////////////////////////////////////////////////
while (($line = fgetcsv($file)) !== FALSE) 
{
   //print_r($line);
  
   ///////////////////////////////////////////////
   // Skip this row if we didn't get enough data
   ///////////////////////////////////////////////
   if (count($line) < 4)
	   continue;
   
   ////////////////////////////////////////////////////////////////
   // parse the data, converting the datetime to a UNIX timestamp
   ////////////////////////////////////////////////////////////////
   $curr_date=$line[0];
   $curr_datetime=strtotime($line[1]);
   $curr_posted_wait=$line[2];
   $curr_actual_wait=$line[3];
  
   ///////////////////////////////////////////////
   // Check if this is a posted or actual wait
   // and determine whether it's something we 
   // should save.
   ///////////////////////////////////////////////
   if (is_numeric($curr_posted_wait)==false || $curr_posted_wait < 0 || $curr_posted_wait > 500)
	   $valid_posted=false;
   else
	   $valid_posted=true;
   
   if (is_numeric($curr_actual_wait)==true && $curr_actual_wait > 0 && $curr_actual_wait < 500)
	   $valid_actual=true;
   else
	   $valid_actual=false;
   
   
   /////////////////////////////////////
   // Save it if we need to
   /////////////////////////////////////
   if ($valid_posted)
	   $posted_waits[$curr_datetime]=$curr_posted_wait;
   
   if ($valid_actual)
	   $actual_waits[$curr_datetime]=$curr_actual_wait;
} // while we're reading in lines


///////////////////////////////////////////////////////////////////////////
// At this point we're done reading in all the data. Close the input file
// and process it
///////////////////////////////////////////////////////////////////////////
fclose($file);

/////////////////////////////////////////////////////
// Now run through the posteds and see what we see
/////////////////////////////////////////////////////
//echo "We have " . count($actual_waits) . " actual wait times.\n";


///////////////////////
//generate a header
///////////////////////
//echo "PARK,ATTR,YYYY,MM,DD,HH,mm,Actual,Nearest Posted, abs_diff\n";


///////////////////////////////////////////////////////
// for each actual, see what the nearest posteds are
///////////////////////////////////////////////////////
foreach($actual_waits as $tstamp => $act)
{
	//printf("Actual wait of %d at %s\n", $act, $tstamp);
	
	///////////////////////////////////////////////////////////////////
	// convert the timestamp to a year, month, day, hour, and minute
	///////////////////////////////////////////////////////////////////
	$str = strftime("%Y,%m,%d,%H,%M", $tstamp);
			
	////////////////////////////////////////////////////////////
	// Look backward to find the closest matching posted wait
	////////////////////////////////////////////////////////////
	$bFound=false;
	for($t = $tstamp; $t>= $tstamp-$MAX_SECONDS_BETWEEN_SAMPLES && $bFound==false;$t--)
	{
		if (key_exists($t, $posted_waits))
		{
			////////////////////////////////////////////////////////////////////////////////
			// get the difference between posted and actual
			// Negative means you waited less than posted. Positive means you waited more.
			////////////////////////////////////////////////////////////////////////////////
			$diff=$act - $posted_waits[$t];
			
			printf("%s,%s,%s,%d,%d,%d,%d,before\n", $park,$attr, $str, $act,$posted_waits[$t], abs($act - $posted_waits[$t]), $diff);
			$bFound=true;
		} // if
	} // for for $t = $tstamp
	
	///////////////////////////////////////////////////////////////
	// Look forward to find the closest matching posted wait
	///////////////////////////////////////////////////////////////
	$bFound=false;
	for($t = $tstamp+1; $t<= $tstamp+$MAX_SECONDS_BETWEEN_SAMPLES && $bFound==false;$t++)
	{
		if (key_exists($t, $posted_waits))
		{
			////////////////////////////////////////////////////////////////////////////////
			// get the difference between posted and actual
			// Negative means you waited less than posted. Positive means you waited more.
			////////////////////////////////////////////////////////////////////////////////
			$diff=$act - $posted_waits[$t];
			
			printf("%s,%s,%s,%d,%d,%d,%d,after\n", $park,$attr,$str, $act,$posted_waits[$t], abs($act - $posted_waits[$t]), $diff);
			$bFound=true;
		} // if
	} // for $t = $tstamp+1
} // foreach


/////////////////////////////
// We're done. Exit cleanly.
/////////////////////////////
exit(0);
?>