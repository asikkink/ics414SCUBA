//test file
/**DISCLAIMER: THIS IS A PROTOTYPE. CANNOT BE USED TO PLAN ACTUAL DIVES
 * 
 */
/**
 * Database Information
 * 
 * Table: bottom_time
 * Columns: pressureG (char), depth (int), time (int)
 * 
 * Table: surface_interval
 * Columns: init_pressure_group (char), start_time (int), end_time (int), final_pressure_group(char)
 * 
 * Table: surface_interval
 * Columns: init_pressure_group, start_time, end_time, final_pressure_group
 * 
 * @author Victor
 *
 */

public class test{
	
	public static void main(String[] args) {
	
		System.out.println("Hello world!!");
		
			//DBDemo app = new DBDemo();
			//app.run();
			
			DiveDB diveApp = new DiveDB();
			//Test: Find pressure group of depth/time 55/22
			//Answer H (60/23)
			String test = diveApp.getPressureGroup(55, 22);
			System.out.println(test);
			//Test get final pressure group
			test = diveApp.getFinalPressureGroup("F", 30);
			System.out.println(test);
			
			diveApp.closeConnection();
		
	}
	
}