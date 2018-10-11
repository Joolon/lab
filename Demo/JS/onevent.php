<?php
/**
 * JS 替换 INPUT输入的值，控制输入数据格式
 * User: Jolon
 * Date: 2018/10/11
 * Time: 21:50
 */

?>

<script>
    // 自动替换输入框中的值

    // 在 input 标签中使用
    // onkeyup="value=value.replace(/[^\d]/g,'')"

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
