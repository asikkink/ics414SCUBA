import static org.junit.Assert.*;

import java.sql.ResultSet;
import java.sql.SQLException;


import org.junit.Test;


public class DiveDBTest {

	@Test
	public void testGetPressureGroup() throws SQLException{
		DiveDB dive = new DiveDB();
		final String d = "depth";
		final String t = "time";
		int depth = 55;
		int time = 22;

		System.out.println(dive.getBottomTimeRow(depth, time));
		assertEquals("Pressure Group Letter", "H", dive.getPressureGroup(depth, time));
		
		dive.closeConnection();
	}

}
