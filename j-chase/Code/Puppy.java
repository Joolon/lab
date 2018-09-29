public class Puppy{
	
	double puppyAge;/*// 声明变量的同时要确定 变量数据类型*/
	
	public Puppy(String name){
		System.out.println("this is :"+ name);
	}

	public void setAge(int age){
		this.puppyAge = age;
	}
	
	public double getAge(){
		System.out.println("The puppy age is "+ puppyAge);
		
		return puppyAge;
		
	}
	
	public static void main(String []args){
		Puppy myPuppy = new Puppy("tony");
		
		myPuppy.setAge(2);

		myPuppy.getAge();
		
		
		String nowString = new String("ACBD");
		// String nowString = "ACBD";
		nowString.append("www");
		System.out.println(nowString);
		
		StringBuffer sBuffer = new StringBuffer("http://");
		sBuffer.append("www");
		sBuffer.append(".runoob");
		sBuffer.append(".com");
		System.out.println(sBuffer);  
		
		
		System.out.println("The puppyAge value is:"+ myPuppy.puppyAge);
		
	}

}