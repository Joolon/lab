<!doctype html>
<html>
<head>
<meta charset="UTF-8" />
<title><?php echo $Title; ?> - <?php echo $Powered; ?></title>
<link rel="stylesheet" href="./css/install.css?v=9.0" />
</head>
<body>
<div class="wrap">
  <?php require './templates/header.php';?>
  <section class="section">
    <div class="step">
      <ul>
        <li class="current"><em>1</em>检测环境</li>
        <li><em>2</em>创建数据</li>
        <li><em>3</em>完成安装</li>
      </ul>
    </div>
    <div class="server">
      <table width="100%">
        <tr>
          <td class="td1">环境检测</td>
          <td class="td1" width="25%">推荐配置</td>
          <td class="td1" width="25%">当前状态</td>
          <td class="td1" width="25%">最低要求</td>
        </tr>
        <tr>
          <td>操作系统</td>
          <td>类UNIX</td>
          <td><span class="correct_span">&radic;</span> <?php echo $os; ?></td>
          <td>不限制</td>
        </tr>
        <tr>
          <td>PHP版本</td>
          <td>>5.3.x</td>
          <td><span class="correct_span">&radic;</span> <?php echo $phpv; ?></td>
          <td>5.2.0</td>
        </tr>
        <tr>
          <td>Mysql版本（client）</td>
          <td>>5.x.x</td>
          <td><?php echo $mysql; ?></td>
          <td>4.2</td>
        </tr>
        <tr>
          <td>附件上传</td>
          <td>>2M</td>
          <td><?php echo $uploadSize; ?></td>
          <td>不限制</td>
        </tr>
        <tr>
          <td>session</td>
          <td>开启</td>
          <td><?php echo $session; ?></td>
          <td>开启</td>
        </tr>
      </table>
      <table width="100%">
        <tr>
          <td class="td1">目录、文件权限检查</td>
          <td class="td1" width="25%">写入</td>
          <td class="td1" width="25%">读取</td>
        </tr>
      <?php
      foreach($folder as $dir){
           $Testdir = SITEDIR.$dir;
           dir_create($Testdir);
           if(TestWrite($Testdir)){
               $w = '<span class="correct_span">&radic;</span>可写 ';
           }else{
               $w = '<span class="correct_span error_span">&radic;</span>不可写 ';
               $err++;
           }
           if(is_readable($Testdir)){
               $r = '<span class="correct_span">&radic;</span>可读' ;
           }else{
               $r = '<span class="correct_span error_span">&radic;</span>不可读';
               $err++;
           }
      ?>
        <tr>
          <td><?php echo $dir; ?></td>
          <td><?php echo $w; ?></td>
          <td><?php echo $r; ?></td>
        </tr>
<?php
}
?>   
      </table>
    </div>
    <div class="bottom tac">
      <a href="<?php echo $_SERVER['PHP_SELF']; ?>?step=2" class="btn">重新检测</a>
      <a href="<?php echo $_SERVER['PHP_SELF']; ?>?step=3" class="btn">下一步</a>
    </div>
  </section>
</div>
<?php require './templates/footer.php';?>
</body>
</html>