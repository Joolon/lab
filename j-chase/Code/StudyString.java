public class StudyString{

	public StudyString(){
		
	}
	
	public static void main(String []args){
		
		StringBuffer str = new StringBuffer("Now Time:");
		
		str.append("-2018-09-29");
		// str.insert()
		
		str.append(" 19:51:15");
		System.out.println(str.length());
		
		str.insert(9,"year/month/day");
		
		str.reverse();
		System.out.println(str);
		System.out.println(str.reverse());
		
		
		System.out.println(str);
	
	}


}