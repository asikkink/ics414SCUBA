//test file
/**
 * Database Information
 * 
 * Table: bottom_time
 * Columns: pressureG (char), depth (int), time (int)
 * 
 * Table: surface_interval
 * Columns: init_pressure_group (char), start_time (int), end_time (int), final_pressure_group(char)
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
			String h = diveApp.getPressureGroup(55, 22);
			System.out.println(h);
			
			diveApp.closeConnection();
		
	}
	
}