import java.util.*;
import java.util.Date;// �����
import java.text.*;

public class DateStudy{


	public static void main(String[] args){
	
		Date date = new Date();// ���ݵ�ǰʱ������ʼ��һ�����ڶ���
		
		
		System.out.println(date.toString());// ��ӡ����
		System.out.println(date.getTime());// ��ȡ���ڵĺ�����
		System.out.println(date.toString());// ��ӡΪ�����ڡ��·ݵ�����
		
		
		Date date2 = new Date(1539395553);// ����ָ���ĺ����� ��ʼ��һ�����ڶ���
		System.out.println(date2.toString());// ��ӡ����
		
		
		System.out.println("��ǰʱ��Ϊ��"+getNowTime());
		
	}
	
	
	public static String getNowTime(){
		
		Date date = new Date();// ���ݵ�ǰʱ������ʼ��һ�����ڶ���
		SimpleDateFormat date_ft = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
		
		return date_ft.format(date);
		
	}

}