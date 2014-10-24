//test file
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;


public class test{
	public static void main(String[] args) {
		Connection conn = null;

	
		System.out.println("Hello world!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!11");
		try{
			
			Class.forName("com.mysql.jdbc.Driver").newInstance();
			
		}catch (Exception ex){
			System.out.println("Broken");
			
		}
		try{
			conn =
					DriverManager.getConnection("jdbc:mysql://localhost/test?" + "user=root&password=password");
			System.out.println("It works");
		}catch(SQLException ex){
			System.out.println("SQLException: " + ex.getMessage());
		    System.out.println("SQLState: " + ex.getSQLState());
		    System.out.println("VendorError: " + ex.getErrorCode());
			System.out.println("Still doesnt work");
		}
		
		
	}
	
}