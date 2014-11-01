import java.sql.*;
import java.util.Properties;

public class DiveDB {
	private final String userName = "bigoli";
	private final String password = "pasta";
	private final String serverName = "localhost";
	private final int portNumber = 3306;
	private final String dbName = "dive_table";
	private static Connection conn = null;

	/**
	 * Constructor
	 * 
	 * @return
	 * @throws SQLException
	 */
	public DiveDB() {
		try {
			conn = this.getConnection();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

	// Create connection to database
	public Connection getConnection() throws SQLException {
		Connection conn = null;
		Properties connectionProps = new Properties();
		connectionProps.put("user", this.userName);
		connectionProps.put("password", this.password);

		conn = DriverManager.getConnection("jdbc:mysql://" + this.serverName
				+ ":" + this.portNumber + "/" + this.dbName, connectionProps);

		return conn;
	}

	// Close connection to database
	public void closeConnection() {
		try {
			conn.close();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}

	}

	/**
	 * Get pressure group by depth and time
	 */
	public String getPressureGroup(int depth, int time) {
		String pGroup = "";
		// select the first number that is both greater or equal to depth and
		// time
		PreparedStatement prestmt = null;

		try {
			prestmt = conn
					.prepareStatement("select * from bottom_time where depth>=? and time>=?");
			prestmt.setInt(1, depth);
			prestmt.setInt(2, time);

			ResultSet rs = prestmt.executeQuery();
			if (rs.next()) {

				System.out.println(rs.getRow() + " " + rs.getInt("depth") + " "
						+ rs.getInt("time"));
				pGroup = rs.getString("pressureG");
				return pGroup;
			}

		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} finally {
			if (prestmt != null) {
				try {
					prestmt.close();
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
		}
		return "none";
	}

	/**
	 * Get Result set
	 * 
	 * For testing purposes View the entire row of the getPressureGroup function
	 */
	public static String getBottomTimeRow(int depth, int time) {
		String row = "current depth: " + depth + " current time: " + time + " ";
		PreparedStatement prestmt = null;
		try {
			prestmt = conn
					.prepareStatement("select * from bottom_time where depth>=? and time>=?");
			prestmt.setInt(1, depth);
			prestmt.setInt(2, time);

			ResultSet rs = prestmt.executeQuery();
			if (rs.next()) {

				row += "db depth: " + rs.getInt("depth") + " db time: "
						+ rs.getInt("time") + " pressure group: "+ rs.getString("PressureG");
				return row;
			}else{
				row += "Could not find";
			}

		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} finally {
			if (prestmt != null) {
				try {
					prestmt.close();
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
		}
		return row;
	}
	/**
	 * Gets the new pressure group during the surface interval
	 * Takes in initial pressure group and time spent out of water
	 */
	public String getFinalPressureGroup(String initial_pressure_group, int surface_time){
		String final_pressure_group = "";
		// select the first number that is both greater or equal to depth and
		// time
		PreparedStatement prestmt = null;

		try {
			prestmt = conn
					.prepareStatement("select * from surface_interval where init_pressure_group=? and start_time<=? and end_time>=?");
			prestmt.setString(1, initial_pressure_group);
			prestmt.setInt(2, surface_time);
			prestmt.setInt(3, surface_time);

			ResultSet rs = prestmt.executeQuery();
			if (rs.next()) {

				System.out.println(rs.getRow() + " " + rs.getInt("start_time") + " "
						+ rs.getInt("end_time"));
				final_pressure_group = rs.getString("final_pressure_group");
				return final_pressure_group;
			}

		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		} finally {
			if (prestmt != null) {
				try {
					prestmt.close();
				} catch (SQLException e) {
					// TODO Auto-generated catch block
					e.printStackTrace();
				}
			}
		}
		return "none";
	}
	

}
