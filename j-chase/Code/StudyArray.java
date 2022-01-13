import java.util.*;

public class StudyArray{
	public StudyArray(){
		
	}
	
	public static void main(String []args){	
		int num_size = 10;
		
		double[] myList = new double[num_size];// 创建数组 必须制定大小，且只能存储该大小的元素
		for(int i = 0;i < num_size;i ++){
			myList[i] = i * i;
		}
		
		// System.out.println("The array list is by for: ");// 打印数组元素
		// for(int i = 0; i< myList.length;i ++){
			// System.out.println(myList[i] + " ");
		// }
		// System.out.println("The array list is by for each: ");
		// for(double element:myList){
			// System.out.println(element + " ");
		// }
		
		double total = arraySum(myList);
		System.out.println("Total is:"+total);
		
		
		double max = myList[0];//数组 MAX VALUE
		for(int i = 0 ;i < myList.length;i ++){
			if(myList[i] > max ) max = myList[i];
		}
		System.out.println("The max is:"+max);
		
		printArray(myList);
		
		
		
		double[] scoreList = new double[10];
		
		// 多维数组：最高维限制其能保存数据的最长的长度，然后再为其每个数组元素单独分配空间
		String ss[][] = new String[2][];
		ss[0] = new String[2];
		ss[1] = new String[3];
		
		ss[0][0] = new String("00");
		ss[0][1] = new String("01");
		ss[1][0] = new String("10");
		
		System.out.println("\n");
		System.out.println(ss[1][0]);
		
		System.out.println(Arrays.toString(myList));//将数组打印成字符串
		
	}
	
	
	public static void printArray(double[] array) {
		for (int i = 0; i < array.length; i++) {
			System.out.print("\t" +array[i]);
		}
	}
	
	
	public static double arraySum(double[] array) {
		
		double total = 0;//数组求和
		for(int j = 0;j < array.length;j ++){
			total += array[j];
		}
		
		return total;
	}


}