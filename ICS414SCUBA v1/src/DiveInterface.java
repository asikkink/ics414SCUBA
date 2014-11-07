/**DISCLAIMER: THIS IS A PROTOTYPE. CANNOT BE USED TO PLAN ACTUAL DIVES
 * 
 */
import javax.swing.JFrame;
import javax.swing.JPanel;
import javax.swing.JComboBox;
import javax.swing.JButton;
import javax.swing.JLabel;
import javax.swing.JList;
import java.awt.BorderLayout;
import java.awt.event.ActionListener;
import java.awt.event.ActionEvent;


public class DiveInterface {
	public DiveInterface(){
		JFrame guiFrame = new JFrame();
		guiFrame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
		guiFrame.setTitle("Example GUI");
		guiFrame.setSize(1000, 700);
		guiFrame.setLocationRelativeTo(null);
		
		//Placeholder arrays
		Integer[] select_depth = {35,40,50,60,70,80,90,100};
		int[] select_time = new int[90];
		//filling start time options
		for(int start = 0; start<select_time.length; start++){
			select_time[start] = start+3;
		}
		
		//Create combo box
		final JPanel comboPanel = new JPanel();
		JLabel comboLbl = new JLabel("Depth:");
		JComboBox depths = new JComboBox(select_depth);
		comboPanel.add(comboLbl);
		comboPanel.add(depths);
		
		guiFrame.add(comboPanel, BorderLayout.NORTH);
		guiFrame.setVisible(true);
		
	}

	public static void main(String[] args) {
		new DiveInterface();
	}
	
}
