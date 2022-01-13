<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style type="text/css">
        <!--
        body, table {
            font-size: 13px;
        }

        table {
            table-layout: fixed;
            empty-cells: show;
            border-collapse: collapse;
            margin: 0 auto;
            border: 1px solid #cad9ea;
        }

        th {
            height: 22px;
            font-size: 13px;
            font-weight: bold;
            background-color: #CCCCCC;
            text-align: center;
        }

        td {
            height: 20px;
        }

        .tableTitle {
            font-size: 14px;
            font-weight: bold;
        }

    </style>
    <title>zuizen数据库结构</title>
</head>

<body>
<div style="margin:0 auto;width:880px; border:1px #006600 solid; font-size:12px; line-height:20px;">
    <div style="width:100%;height:30px; font-size:16px; font-weight:bold; text-align:center;">
        **网数据库结构<br/>
        <font style="font-size:14px; font-weight:normal;"><?php echo date("Y-m-d h:i:s"); ?></font>
    </div>
    <?php
    session_start();
    $dbconn = mysqli_connect("192.168.71.170", "dev_purchase", "yb123456",'information_schema');
    $sql = "SELECT * FROM tables where table_schema='yibai_purchase'";
    $result = mysqli_query($dbconn,$sql);
    while ($row = mysqli_fetch_array($result)) {
//        print_r($row);
        ?>
        <div style="margin:0 auto; width:100%; padding-top:10px;">
            <b class="tableTitle">表名： <?php echo $row["TABLE_NAME"] ?> </b> <br/>
            <?php echo $row["TABLE_COMMENT"] ?>
        </div>
        <table width="100%" border="1">
            <thead>
            <th width="70">序号</td>
            <th width="170">字段名</td>
            <th width="140">字段类型</td>
            <th width="80">允许为空</td>
            <th width="140">默认值</td>
            <th>备注</td>
            </thead>
            <?php
            $sql2 = "SELECT * FROM columns where table_name='" . $row["TABLE_NAME"] . "'";
            $result2 = mysqli_query($dbconn,$sql2);
            $num = 0;
            while ($row2 = mysqli_fetch_array($result2)) {
                $num = $num + 1;
                //print_r($row);
                ?>

                <tr>
                    <td align="center"><b><?php echo $num ?></b></td>
                    <td style="word-wrap:break-word;word-break:break-all;"><?php echo $row2["COLUMN_NAME"] ?></td>
                    <td style="word-wrap:break-word;word-break:break-all;"><?php echo $row2["COLUMN_TYPE"] ?></td>
                    <td align="center" style="word-wrap:break-word;word-break:break-all;"><?php echo $row2["IS_NULLABLE"] ?></td>
                    <td align="center" style="word-wrap:break-word;word-break:break-all;"><?php echo $row2["COLUMN_DEFAULT"] ?></td>
                    <td style="word-wrap:break-word;word-break:break-all;"><?php echo $row2["COLUMN_COMMENT"] ?></td>
                </tr>
                <?php
            }
            ?>
        </table>
        <?php
    }
    mysqli_close($dbconn);
    ?>

</div>
</body>
</html>