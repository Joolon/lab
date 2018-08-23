<html>
<title>
    jquery::test1
</title>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
jQuery的使用
</head>
<script type="text/javascript" src="../Public/js/jquery-2.1.1.min.js"></script>
<script type="text/javascript">
    <!--页面加载完后执行-->
    $(document).ready(function(){
        $("p").click(function(){
            $(this).hide();
        });
        /*
        还可以使用 p.test 或 p#test 表示class=test或id=test的<p>元素
         */

        $("#button1").click(function(){
            $(".test").hide();
        });

        $("#button2").click(function(){
            $("#test").hide();
        });


        $("#button3").click(function(){
            $("#div1").fadeOut();
            $("#div2").fadeOut('slow');
            $("#div3").fadeOut(3000);
        });

        $("#button4").click(function(){
            $("#h4").html("W3School");
            $("#p4").html("W3School");
        });

        $("#button5-1").click(function(){
            $(".b5").append("<b>追加的p标签内容</b>");
        });
        $("#button5-2").click(function(){
            $(".ol5").append("<li>新增的li</li>")
        });



    });


</script>
<body>
<br/><hr/>
<p title="根据标签名选定">点击隐藏当前的元素</p>
<label class="test">one根据class选定所有相同的元素</label>
<label class="test">two根据class选定所有相同的元素</label>
<button type="button" id="button1">点击隐藏</button>
<br/>
<br/>
<label id="test">根据id选定所有相同的元素</label>
<button type="button" id="button2">点击隐藏</button>
<br/>
<br/>
<button type="button" id="button3">三个巨型淡出</button>
<div id="div1" style="background-color: red;width: 40px;height: 40px;"></div>
<div id="div2" style="background-color: green;width: 40px;height: 40px;"></div>
<div id="div3" style="background-color: blue;width: 40px;height: 40px;"></div>
<br/>
<br/>
<h3 id="h4">h3</h3>
<p id="p4">p</p>
<button type="button" id="button4">改变html的内容</button>
<br/>
<br/>
<b class="b5">追加文本。。</b>
<b class="b5">追加文本。。</b>
<ol class="ol5">
    <li>追加文本。。</li>
    <li>追加文本。。</li>
</ol>
<button type="button" id="button5-1">追加文本</button>
<button type="button" id="button5-2">追加Item</button>
<br/>
<br/>
<hr size="10"/>
<p> $(this)     	当前 HTML 元素<br/>
    $("p") 	        所有 < p> 元素<br/>
    $("p.intro") 	所有 class="intro" 的 < p> 元素<br/>
    $(".intro") 	所有 class="intro" 的元素<br/>
    $("#intro") 	id="intro" 的元素<br/>
    $("ul li:first") 	每个 < ul> 的第一个 < li> 元素<br/>
    $("[href$='.jpg']") 	所有带有以 ".jpg" 结尾的属性值的 href 属性<br/>
    $("div#intro .head") 	id="intro" 的 < div> 元素中的所有 class="head" 的元素<br/>
</p>

<hr size="10"/>




<br/><br/><br/><br/><br/>




</body>
</html>