import java.sql.*;
import java.util.Properties;

/**
 * This class demonstrates how to connect to MySQL and run some basic commands.
 * 
 * In order to use this, you have to download the Connector/J driver and add its
 * .jar file to your build path. You can find it here:
 * 
 * http://dev.mysql.com/downloads/connector/j/
 * 
 * You will see the following exception if it's not in your class path:
 * 
 * java.sql.SQLException: No suitable driver found for
 * jdbc:mysql://localhost:3306/
 * 
 * To add it to your class path: 1. Right click on your project 2. Go to Build
 * Path -> Add External Archives... 3. Select the file
 * mysql-connector-java-5.1.24-bin.jar NOTE: If you have a different version of
 * the .jar file, the name may be a little different.
 * 
 * The user name and password are both "root", which should be correct if you
 * followed the advice in the MySQL tutorial. If you want to use different
 * credentials, you can change them below.
 * 
 * You will get the following exception if the credentials are wrong:
 * 
 * java.sql.SQLException: Access denied for user 'userName'@'localhost' (using
 * password: YES)
 * 
 * You will instead get the following exception if MySQL isn't installed, isn't
 * running, or if your serverName or portNumber are wrong:
 * 
 * java.net.ConnectException: Connection refused
 */
public class DBDemo {

	/** The name of the MySQL account to use (or empty for anonymous) */
	private final String userName = "bigoli";

	/** The password for the MySQL account (or empty for anonymous) */
	private final String password = "pasta";

	/** The name of the computer running MySQL */
	private final String serverName = "localhost";

	/** The port of the MySQL server (default is 3306) */
	private final int portNumber = 3306;

	/**
	 * The name of the database we are testing with (this default is installed
	 * with MySQL)
	 */
	private final String dbName = "dive_table";

	/** The name of the table we are testing with */
	private final String tableName = "test_table";

	/**
	 * Get a new database connection
	 * 
	 * @return
	 * @throws SQLException
	 */
	public Connection getConnection() throws SQLException {
		Connection conn = null;
		Properties connectionProps = new Properties();
		connectionProps.put("user", this.userName);
		connectionProps.put("password", this.password);

		conn = DriverManager.getConnection("jdbc:mysql://" + this.serverName
				+ ":" + this.portNumber + "/" + this.dbName, connectionProps);

		return conn;
	}

	/**
	 * Run a SQL command which does not return a recordset:
	 * CREATE/INSERT/UPDATE/DELETE/DROP/etc.
	 * 
	 * @throws SQLException
	 *             If something goes wrong
	 */
	public boolean executeUpdate(Connection conn, String command)
			throws SQLException {
		Statement stmt = null;
		try {
			stmt = conn.createStatement();
			stmt.executeUpdate(command); // This will throw a SQLException if it
											// fails
			return true;
		} finally {

			// This will run whether we throw an exception or not
			if (stmt != null) {
				stmt.close();
			}
		}
	}
	//Select test
	public static void selectRecord(Connection conn, String sql) 
			throws SQLException{
		Statement stmt = null;
		try{
			stmt = conn.createStatement();
			System.out.println(sql);
			ResultSet rs = stmt.executeQuery(sql);
			
			//Extract data
			
			while(rs.next()){
				int id = rs.getInt("ID");
				String street = rs.getString("STREET");
				
				
				//Display/do stuff
				System.out.println(id +" "+street);
			}
			
		}finally{
			if(stmt !=null){
				stmt.close();
			}
		}
	}
	
	/**
	 * Prepared statement test
	 */
	public static void preparedSelect(Connection conn, String street)//show columns with value
			throws SQLException{
		System.out.println("Execute preparedSelect");
		PreparedStatement prestmt = null;
		try{
			prestmt = conn.prepareStatement("select * from test_table where street=?");
			prestmt.setString(1, street);
			
			
			System.out.println(street);
			ResultSet rs = prestmt.executeQuery();
			
			//Extract data
			
			while(rs.next()){
				int id = rs.getInt("ID");
				String road = rs.getString("STREET");
				
				
				//Display/do stuff
				System.out.println(id +" "+road);
			}
			
		}finally{
			if(prestmt !=null){
				prestmt.close();
			}
		}
	}

	/**
	 * Connect to MySQL and do some stuff.
	 */
	public void run() {

		// Connect to MySQL
		Connection conn = null;
		try {
			conn = this.getConnection();
			System.out.println("Connected to database");
		} catch (SQLException e) {
			System.out.println("ERROR: Could not connect to the database");
			e.printStackTrace();
			return;
		}

		// Create a table
		try {
			String createString = "CREATE TABLE " + this.tableName + " ( "
					+ "ID INTEGER NOT NULL, " + "NAME varchar(40) NOT NULL, "
					+ "STREET varchar(40) NOT NULL, "
					+ "CITY varchar(20) NOT NULL, "
					+ "STATE char(2) NOT NULL, " + "ZIP char(5), "
					+ "PRIMARY KEY (ID))";
			this.executeUpdate(conn, createString);
			System.out.println("Created a table");
		} catch (SQLException e) {
			System.out.println("ERROR: Could not create the table");
			e.printStackTrace();
			return;
		}
		// Insert to table
		try {
			String sql = "INSERT INTO " + this.tableName + " VALUES (" + "1"
					+ "," + "'Bob'" + "," + "'University'" + "," + "'Honolulu'"
					+ "," + "'HI'" + "," + "'96825'" + ")";
			this.executeUpdate(conn, sql);
			System.out.println("Inserted address");

		} catch (SQLException e) {
			System.out.println("Could not Insert to table");
			e.printStackTrace();

		}
		// Select from table
		try {
			String sql = "SELECT * FROM " + this.tableName;
			this.selectRecord(conn, sql);

		} catch (SQLException e) {
			System.out.println("can't select");
			e.printStackTrace();
		}
		
		//Select from table using prepared statement
		try{
			String street = "University";
			
			preparedSelect(conn,street);
		}catch(SQLException e){
			System.out.println("PreparedStatemnet error: ");
			e.printStackTrace();
			
		}
		

		// Drop the table
		try {
			String dropString = "DROP TABLE " + this.tableName;
			this.executeUpdate(conn, dropString);
			System.out.println("Dropped the table");
		} catch (SQLException e) {
			System.out.println("ERROR: Could not drop the table");
			e.printStackTrace();
			return;
		}
		
		//Close connection
		try {
			conn.close();
		} catch (SQLException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

	/**
	 * Connect to the DB and do some stuff
	 */
	public static void main(String[] args) {
		DBDemo app = new DBDemo();
		app.run();
	}
}
