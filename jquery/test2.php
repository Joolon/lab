<html>
<title>
    jquery::test2
</title>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    jQuery的使用
</head>
<script type="text/javascript" src="../Public/js/jquery-2.1.1.min.js"></script>
<script type="text/javascript">

    $(document).ready(function(){
        $("#btn1").click(function(){
            $("#p1").css({"background-color":"red","font-size":"200%"});
        });

        $("#btn2").click(function(){
            alert("Background color = "+$("#p2").css("background-color"));
        });






    });
</script>
<body>
<p id="p1">背景色</p>
<button type="button" id="btn1">改变CSS的属性</button>
<br/><br/>
<p id="p2" style="background-color: red">CSS颜色</p>
<button id="btn2">获取CSS属性值</button>













</body>
</html>