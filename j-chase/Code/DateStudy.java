import java.util.*;
import java.util.Date;// 导入包
import java.text.*;

public class DateStudy{


	public static void main(String[] args){
	
		Date date = new Date();// 根据当前时间来初始化一个日期对象
		
		
		System.out.println(date.toString());// 打印日期
		System.out.println(date.getTime());// 获取日期的毫秒数
		System.out.println(date.toString());// 打印为带星期、月份的日期
		
		
		Date date2 = new Date(1539395553);// 根据指定的毫秒数 初始化一个日期对象
		System.out.println(date2.toString());// 打印日期
		
		
		System.out.println("当前时间为："+getNowTime());
		
	}
	
	
	public static String getNowTime(){
		
		Date date = new Date();// 根据当前时间来初始化一个日期对象
		SimpleDateFormat date_ft = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
		
		return date_ft.format(date);
		
	}

}