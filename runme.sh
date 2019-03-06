#####################################
# This runs attraction volatility
# Change the line below to say
# 	if [ 1 -eq 1 ]
# to get it to run
#####################################
if [ 1 -eq 1 ]
  then
      echo "**********************"
      echo "* Running volatility *"
      echo "**********************"
      echo
	  rm master_step1.csv 2>/dev/null
	  for x in `ls AK[0-9]*[0-9].csv` 
	  do
		  echo "Adding name to ${x}"
		  awk -f add_name.awk ${x} >> master_step1.csv
      done

      echo "Sorting"
      sort -u -o master_step1.csv master_step1.csv
      
      echo "Computing volatility"
      php -f volatility.php -- < master_step1.csv > master_step2ratio.csv
      
      echo "Look at master_step2ratio.csv in Excel."
fi
  
  
############################################  
# this runs difference in posted vs actual
# Change the line below to say
# 	if [ 1 -eq 1 ]
# to get it to run
############################################
if [ 0 -eq 1 ]
  then
  	echo "****************************"
  	echo "* Running Posted vs Actual *"
  	echo "****************************"
  	echo
  	MASTER_ACT_VS_POSTED="master_act_vs_posted.csv"
  	echo "PARK,ATTR,YYYY,MM,DD,HH,mm,Actual,Nearest Posted, abs_diff,diff[act-posted],before/after" > "${MASTER_ACT_VS_POSTED}"
	
  	for x in `ls AK[0-9]*[0-9].csv MK[0-9]*[0-9].csv HS[0-9]*[0-9].csv EP[0-9]*[0-9].csv`
	   do
	     echo $x
	     php -f actual_vs_posted.php -- -i ${x} >> "${MASTER_ACT_VS_POSTED}"
	   done
fi