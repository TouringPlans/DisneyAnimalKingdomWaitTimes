# Disney's Animal Kingdom
## Attraction posted and actual wait times


This repository has files containing posted and actual wait times from attractions at Walt Disney World's Animal Kingdom theme park.

Each attraction's data is in a separate, comma-delimited file:
	
	AK01.csv = It's Tough to Be a Bug
	AK07.csv = Kilimanjaro Safaris (CC)
	AK11.csv = Expedition Everest (CC)
	AK17.csv = The Boneyard
	AK14.csv = Kali River Rapids (CC)
	AK18.csv = Dinosaur
	AK20.csv = Primeval Whirl (CC)
	AK23.csv = TriceraTop Spin
	AK25.csv = Expedition Everest Single Rider Line
	AK78.csv = Adventurers Outpost Meet & Greet
	AK85.csv = Na'Vi River Journey (CC)
	AK86.csv = Avatar:Flight of Passage (CC)

Files marked with (CC) are included in TouringPlans' crowd calendar for the Animal Kingdom. 

### Data File Format

Each file has the same format:
	
	date,datetime,SPOSTMIN,SACTMIN
	02/22/2019,2019-02-22 11:40:00,30,
	02/22/2019,2019-02-22 11:40:00,,18
	02/22/2019,2019-02-22 11:45:00,25,

where:
	
	__date__ is the calendar date on which that row of data was collected
	
	**datetime** is the date, hours, minutes, and seconds at which that row of data was collected
	
	All dates and times are expressed as US Eastern.
	
	**SPOSTMIN** is the posted wait time outside the attraction, in minutes. Posted wait times are collected from Disney's My Disney Experience app, from users in the parks, and from staff.
	A posted wait of -999 indicates the ride is unexpectedly offline.
	
	**SACTMIN** is the amount of time spent in line by actual user, in minutes, to ride that attraction. The **datetime** field indicates the time at which the user began their wait in line.
	
	In the three-row example above, the first row indicates that a ride's posted wait time was 30 minutes at 11:40 a.m. Eastern on 2/22/2019. The second row indicates that a a user got in line at
	11:40 a.m. Eastern on 2/22/2019, and waited 18 minutes to board the attraction. The third row shows that the attraction's posted wait time changed to 25 minutes at 11:45 a.m. Eastern on the same day.  
	
### Processing the data

	The PHP script actual_vs_posted.php does two things:
		
		1) Tries to match each actual wait time with the closest observed posted wait time, immediately before and after the actual wait time was collected. The posted wait times must be within a
		   10-minute window on either side of the actual wait time observation **datetime** stamp.  
		
		2) For each actual wait time that is matched, print out the before and after posted wait times along with the actual wait time, in a format that allows for easier processing as an Excel pivot table
		
	To run the file AK07.csv through the PHP script, do this (in Linux):
		
		php -f actual_vs_posted.php -- -i AK07.csv > AK07_act_vs_posted.csv
		
	Then view the file AK07_act_vs_posted.csv in Excel.
	
	You can run all of the Animal Kingdom files using the bash script 'runme.sh' too.
	
## Attraction Volatility

Another way of determining the accuracy of the park's wait times is by comparing how much they change in a short amount of time. To do this, we need to make one relatively safe assumption:
	
	1) If Person A enters the same line for the same ride before Person B, person A will board the ride before or with Person B. (That is, person B will not pass person A in line.)

The PHP script volatility.php computes this volatility. I'll write more about that shortly.