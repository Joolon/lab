<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Home</title>
</head>
<body>


<form action="check" method="post">
    {{csrf_field()}}
    <p>User:<input name="username"></p>
    <p>PWD:<input name="password"></p>

    <input type="submit">

</form>

</body>
</html>