<?php
####################################################################################################
#
# Usage:
#
#		php -f volatility.php -- < attracton.csv > attraction_volatility.csv
#
#
#
#
# Assumes:
#		Data files are sorted and unique before processing
#		CSV format
#		Redirected to stdin
#		Input fields:
#			attraction_name,date,datetime,SPOSTMIN,SACTMIN,year,month
##################################################################################################
		
#######################
# FUNCTIONS GO HERE 
#######################

############################
# Global variables go here
############################
//keep track of the last row seen
$prev_date="";
$prev_datetime="";
$prev_posted_wait="";
$prev_attr="";
$prev_year="";
$prev_month="";

//simple count of volatility
$curr_volatility_sum=0;
$curr_volatility_count=0;

//how many seconds can elapse between valid samples
$MAX_SECONDS_BETWEEN_SAMPLES = (15 * 60);



#######################
# MAIN CODE GOES HERE
#######################

////////////////////////////////////////
//parse the command line options here
////////////////////////////////////////
$options = getopt("");

foreach($options as $opt => $value)
{
	//echo "opt=[$opt]\n";
	if ($opt == "<something>")
		;
	if ($opt == "<somethingelse>")
		;
} // foreach

///////////////////////
// generate a header
///////////////////////
echo "Attraction,Date,Average Volatility,Volatility Sum,Volatility Samples,Year,Month\n";

//////////////////////////////
// read stdin line by line
//////////////////////////////
$file = fopen('php://stdin', 'r');
while (($line = fgetcsv($file)) !== FALSE) 
{ 
   ///////////////////////////////////////////////
   // Skip this row if we didn't get enough data
   ///////////////////////////////////////////////
   if (count($line) < 4)
   {
	   //echo "// Invalid Row. Not enough columns.\n";
	   continue;
   } // if
   
   ///////////////////////
   // parse the data
   ///////////////////////
   $curr_attr=$line[0];
   $curr_date=$line[1];
   $curr_datetime=strtotime($line[2]);
   $curr_posted_wait=$line[3];
   $curr_year=$line[5];
   
   if ($curr_year < 2000)
	   $curr_year+=2000;
   
   $curr_month=$line[6];
   
   // print out what we have
   //echo "// date=[$curr_date] datetime=[$curr_datetime] posted=[$curr_posted_wait]\n";

   ///////////////////////////////////////////////
   // Skip this row if we didn't get enough data,
   // or if the data indicates the ride was closed
   ///////////////////////////////////////////////
   if (is_numeric($curr_posted_wait)==false || $curr_posted_wait < 0 || $curr_posted_wait > 500)
   {
	   //echo "// Invalid row.\n";
	   continue;
   }
   
   //////////////////////////////////////
   // If this is the first row, save it
   // and go to the next line.
   //////////////////////////////////////
   if ($prev_date == "")
   {
	   $prev_date = $curr_date;
	   $prev_datetime = $curr_datetime;
	   $prev_posted_wait = $curr_posted_wait;
	   $prev_attr = $curr_attr;
	   $prev_year = $curr_year;
	   $prev_month = $curr_month;
	   
	   continue;
   } // if $prev_date
   
   /////////////////////////////////////////////////////
   // If this is a new date, then process the old date
   // before continuing.
   /////////////////////////////////////////////////////
   if ($curr_date != $prev_date || $curr_attr != $prev_attr)
   {
	   // output
	   echo $curr_attr . "," . $prev_date . ",";
	   
	   if ($curr_volatility_count != 0)
		   echo $curr_volatility_sum / $curr_volatility_count . "," . $curr_volatility_sum . "," . $curr_volatility_count . "," . $prev_year . "," . $prev_month . "\n";
	   else
		   echo "0," . $curr_volatility_sum . "," . $curr_volatility_count . "," . $prev_year . "," . $prev_month . "\n";
	   
	   // reset counters
	   $curr_volatility_sum=0;
	   $curr_volatility_count=0;
	   
	   $prev_date = $curr_date;
	   $prev_datetime = $curr_datetime;
	   $prev_posted_wait = $curr_posted_wait;
	   $prev_attr = $curr_attr;
	   $prev_year = $curr_year;
	   $prev_month = $curr_month;
	   
	   continue;
   } // if this is a new date
   
   ////////////////////////////////////////////////////////////////////////////////
   // error check here. make sure the current datetime is later than the previous
   ////////////////////////////////////////////////////////////////////////////////
   if ($curr_datetime < $prev_datetime)
   {
	   echo "WARNING: CURRENT DATETIME IS EARLIER THAN PREVIOUS. Sort the data by time before running this.\n";
	   echo "p_date=[$prev_date] datetime=[$prev_datetime] posted=[$prev_posted_wait]\n";
	   echo "c_date=[$curr_date] datetime=[$curr_datetime] posted=[$curr_posted_wait]\n";
	   exit(1);
   }
   
   ///////////////////////////////////////////////////////////////////////
   // calculate the difference between this posted wait and the last one
   ///////////////////////////////////////////////////////////////////////
   $elapsed = $curr_datetime - $prev_datetime;
   
   
   //////////////////////////////////////////////////////////////////////////////////////////
   // If the current timestamp is within 15 minutes of the last timestamp, then process it
   //////////////////////////////////////////////////////////////////////////////////////////
   if ($elapsed <= $MAX_SECONDS_BETWEEN_SAMPLES && $elapsed > 0)
   {	   
	   $diff = abs($curr_posted_wait - $prev_posted_wait);
	   $curr_volatility_sum += $diff;
	   $curr_volatility_count++;
   } // if within time limit
   
   
   ////////////////////////////////////
   // Save this line as the previous line
   ////////////////////////////////////
   $prev_date = $curr_date;
   $prev_datetime = $curr_datetime;
   $prev_posted_wait = $curr_posted_wait;
   $prev_attr = $curr_attr;
   $prev_year = $curr_year;
   $prev_month = $curr_month;
} // while we're reading in lines


////////////////////////////////////////////////
// At this point we've read in the entire file
////////////////////////////////////////////////


// print out the last date
echo $curr_attr . "," . $prev_date . ",";
	   
if ($curr_volatility_count != 0)
	 echo $curr_volatility_sum / $curr_volatility_count . "," . $curr_volatility_sum . "," . $curr_volatility_count . "," . $prev_year . "," . $prev_month . "\n";
else
     echo "0," . $curr_volatility_sum . "," . $curr_volatility_count . "," . $prev_year . "," . $prev_month . "\n";

fclose($file);
?>