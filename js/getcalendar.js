/**
 * Generate calendar <table>
 * (modified from https://gist.github.com/madeadi/455b2f8e9c134c5f92a5)
 * 
 * @param integer year
 * @param integer month
 * @param integer loop the number of months to generate
 * @returns {String} calendar table string
 */

function getWeekNumber(date) {
	var oneJan = new Date(date.getFullYear(), 0, 1);
	var numOfDays = Math.floor((date - oneJan) / 86400000);
	return (Math.ceil((date.getDay() + 1 + numOfDays) / 7));
}

function getCalendar(year, month, loop){
	//Determing if Feb has 28 or 29 days in it.  
	var totalFeb = 28;
	if (month === 1) {
		if ((year % 100 !== 0) && (year % 4 === 0) || (year % 400 === 0)) {
			totalFeb = 29;
		} else {
			totalFeb = 28;
		}
	}
	
	// setting up array
	var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
	var totalDays = [31, totalFeb, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	
	
	var isLoop = true;
	var content = "";
	
	var prevMonth = month-1;
	var prevYear = year;
	if(prevMonth < 1){
		prevMonth = 12;
		prevYear = year -1;
	}
	
	var nextMonth = month+1;
	var nextYear = year;
	if(nextMonth > 12){
		nextMonth = 1;
		nextYear = year+1;
	}
	
	var week;
	month = month-1;
	for(i=0; i<loop; i++){
		var thedate = new Date(year, month, 1);        
		
		
		content += "<div class='glm-calendar "+thedate.getFullYear()+"-"+(thedate.getMonth()+1)+" " + thedate.getFullYear() + " " + (thedate.getMonth()+1) + "' data-month="+(thedate.getMonth()+1)+">" +
				"<table class='month month-"+thedate.getFullYear()+"-"+(thedate.getMonth()+1)+" "+ (thedate.getMonth()+1) +
				"' data-month="+(thedate.getMonth()+1)+" data-year="+thedate.getFullYear()+">" +
				"<caption><div>" + 
//					"<a class='reload-calendar prev-month' data-month= "+prevMonth+" data-year= "+prevYear+"><</a>" + 
				monthNames[thedate.getMonth()]+ " " + thedate.getFullYear() + 
//					"<a class='reload-calendar next-month' data-month="+nextMonth+" data-year="+nextYear+" >></a>" +
				"</div></caption>" +
				"<tbody>"+
				"<tr class='daynames'>" +
				"<td>Mon</td><td>Tue</td><td>Wed</td><td>Thu</td><td>Fri</td><td>Sat</td><td>Sun</td>" +
				"</tr>";
		
		//preparing the loop to render the dates in a month
		isLoop = true;
		week = getWeekNumber(thedate);
		var first = thedate.getDay();
		//first day of the month starts on Monday instead on Sunday
		if(first === 0){
			first = 7;
		}
		
		// initiate the day with negative value so that the loop can render properly
		var day = (1-first)+1;
		while(isLoop){
			content +=  "<tr class='week week-"+week+"' data-week="+week+" data-year="+thedate.getFullYear()+"  data-month="+ (thedate.getMonth()+1) +">";
			

			// loop for 7 days
			for(j=1; j<=7; j++){
				content += "<td onmouseover='showProgTooltip(event)' onmouseout='hideProgTooltip(event)' onclick='editProgramme(event)' class='";
				content += (day >= 1 ? ("day day-"+thedate.getFullYear()+"-"+(thedate.getMonth()+1)+"-"+day) : ""); 
				content += "' " +
						"  data-date="+day+" data-month="+(thedate.getMonth()+1)+" data-year="+thedate.getFullYear()+" >"+
						(day >= 1 ? day : "") +"</td>";
				if(day === totalDays[thedate.getMonth()]){
					isLoop = false;
					break;
				} else {
					day++;
				}
			}
			content += "</tr>";
			week++;
		}
		
		content +=  "</tbody>" + "</table>" + "</div>";        
		month++;
	}
	
	return content;
}