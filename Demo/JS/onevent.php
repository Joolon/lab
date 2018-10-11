<?php
/**
 * JS 替换 INPUT输入的值，控制输入数据格式
 * User: Jolon
 * Date: 2018/10/11
 * Time: 21:50
 */

?>

<input type="text" onkeyup="this.value=this.value.replace(/[^\u4e00-\u9fa5]/g,'')" title="只能输入中文">
<input type="text" onkeyup="this.value=this.value.replace(/[^\d]/g,'')" title="只能输入数字(小数点也不能输入)">
<input type="text" onkeyup="this.value=this.value.replace(/[^\d.]/g, '')" title="只能输入数字(小数点可以输入)">
<input type="text" onkeyup="this.value=this.value.replace(/[^\d.-]/g, '')" title="只能输入数字(小数点可以输入，可以输入负数)">
<input type="text" onkeyup="this.value=this.value.replace(/[^a-zA-Z]/g,'')" title="只能输入英文">
<input type="text" onKeyUp="this.value=this.value.replace(/[^\d|chun]/g,'')" title="只能输入数字和英文">
<input type="text" onkeyup="this.value=this.value.replace(/[^\w\.\/]/ig,'')" title="只能输入字母和数字">

<script>
    // 自动替换输入框中的值

    // 在 input 标签中使用
    // onkeyup="this.value=this.value.replace(/[^\d]/g,'')"

    /* 自动替换掉 输入框中的非数字 字符 */
    $(document).on("keyup",".int_input",function(){
        $(this).val($(this).val().replace(/[^\d]/g,''));
    });

    /* 自动替换掉 输入框中的非数字和小数点 字符 */
    $(document).on("keyup",".float_int_input",function(){
        $(this).val($(this).val().replace(/[^\d.]/g, ''));
    });


    /* 自动替换掉 输入框中的非数字和小数点 字符 */
    $(document).on("keyup",".float_int_input",function(){
        $(this).val($(this).val().replace(/[^\d.-]/g, ''));// - 允许负数
    });







</script>
