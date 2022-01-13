<?php
include_once PDA_TPL.'head.php';
?>

<link rel="stylesheet" href="<?php echo PDA_CSS?>login/login.css">

<div class="login">
    <form class="from-login" action="<?php echo MyUrl('c=Index&m=loginCheck'); ?>" method="post">
<!--        <div style="width: 110%;height: 20px;background-color: #FFFFFF">-->
<!--            用户登入-->
<!--        </div>-->
<!--        <br/>-->

        <table >
            <tr>
                <td >

                    <div style="width: 100%;height: 20px;background-color: #FFFFFF;margin-bottom: 15%;">
                        <div style="float:left;width: 10px;height: 10px;background-color:#666666;margin-left: 5px;margin-top: 5px; "></div>
                        <div style="float:left;margin-left: 5%;margin-top: 2px; ">用户登入</div>
                        <div style="float:right;margin-right: 2%;font-size: 18px; ">&times;</div>

                    </div>
                </td>
            </tr>
            <tr class="show-login">
                <td ><label>账户：</label>
                    <input type="text" name="username" id="username" onkeypress="nextInput('password')"
                           placeholder="用户名或手机号"/>
                </td>
            </tr>
            <tr  class="show-login">
                <td  ><label>密码：</label>
                    <input type="password" name="password" id="password" onkeypress="nextInput('username')" style="width: 45%"/>
                    <img src="<?php echo PDA_IMAGE;?>eye-open.png" width=25" height="27" id="show_password"
                         style="background-color: #fff;vertical-align: top;margin-left: -3px;margin-top: 1px;padding: 2px;"/>
                </td>
            </tr>

            <tr class="show-sc-login" style="display: none;">
                <td><label>扫码：</label>
                    <input type="password" name="scan_code" id="scan_code" placeholder="请扫码"/>
                </td>
            </tr>
            <tr>
                <td colspan="1" align="center">
                    <div  style="margin-top: 8%;margin-left:8%;text-align: center">
                        <button  type="button" class="doLogin">登入</button>
                        <button type="reset" style="margin-left: 15%;">重置</button>
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="1" align="center">
                    <div  style="margin-top: 5%;margin-left:8%;text-align: center">
                        <button type="button" class="showSCLogin" style="width: 40%;background-color: #c7ca2b;">
                            <font color="#cd5c5c">扫码登录</font>
                        </button>
                        <button type="button" class="showLogin" style="width: 40%;display: none;background-color: #bfca31;">
                            <font color="#cd5c5c">密码登录</font>
                        </button>
                        <br/>
                        <span class="tishi" style="font-size: 8px;display: none;">条码产生地址:报表查询->生成账号密码条形码。</span>
                        <input class="loginType" type="hidden" value="1"/>
                    </div>
                </td>
            </tr>
            <tr style="height: 10px;">
                <td >
                    &nbsp;
                </td>
            </tr>
        </table>
    </form>
</div>

<script language="javascript">
    $(function () {
        // 开始登录验证
        $(".doLogin").click(function () {
            var username = $("#username").val();
            var password = $("#password").val();
            var scan_code = $("#scan_code").val();
            var loginType = $(".loginType").val();

            if(loginType == 1){
                if(username == ''){
                    webToast("请输入账户名","middle",1000);
                    $("#username").focus();
                    return false;
                }
                if(password == ''){
                    webToast("请输入登录密码","middle",1000);
                    $("#password").focus();
                    return false;
                }
            }else if(loginType == 2){
                if(scan_code == ''){
                    webToast("请扫码登录","middle",1000);
                    $("#scan_code").focus();
                    return false;
                }
            }
            $.post('<?php echo MyUrl('c=Index&m=loginCheck');?>',{username:username,password:password,scan_code:scan_code,loginType:loginType},
                function(re_data){
                    var data = jQuery.parseJSON(re_data);
                    if(data.code == '0X0000'){
                        openUrl(url_self+'?c=Index&m=index');
                    }else{
                        if(data.msg){
                            webToast(data.msg,"top",2000);
                        }else{
                            webToast('抱歉,登录失败!请核对用户信息',"top",2000);
                        }
                    }

                }
            );
        });

        // 扫码登录
        $(".showSCLogin").click(function(){
            // 隐藏 密码登录
            $(".show-login").hide();

            // 展示 扫码登录
            $(".show-sc-login").show();
            $(".showScanCode").show();
            $("#scan_code").focus();

            $(".showLogin").show();
            $(this).hide();
            $(".loginType").val(2);
            $(".tishi").show();
        });

        // 密码登录
        $(".showLogin").click(function(){
            // 隐藏 扫码登录
            $(".show-sc-login").hide();

            // 展示 密码登录
            $(".show-login").show();
            $(".showLogin").show();

            $(".showSCLogin").show();
            $(this).hide();
            $(".loginType").val(1);
            $(".tishi").hide();
        });

        $("#username").focus();

    });

    $("#show_password").click(function(){
        $(this).attr('src','<?php echo PDA_IMAGE;?>eye-close.png');
        show_password();
        window.setTimeout(hide_password,600);
    });
    function show_password(){
        $("#password").attr('type','text');
    }

    function hide_password(){
        $("#password").attr('type','password');
        $("#password").focus();

        $("#show_password").attr('src','<?php echo PDA_IMAGE;?>eye-open.png');
    }
</script>

<?php

include_once PDA_TPL.'boot.php';

?>


