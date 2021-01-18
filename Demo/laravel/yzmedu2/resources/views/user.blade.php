<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>User</title>
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @if (Auth::check())
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ url('/login') }}">Login</a>
                        <a href="{{ url('/register') }}">Register</a>
                    @endif
                </div>
            @endif

            <div class="content">

                <table width="500px" border="1" align="center">
                    <th>ID</th>
                    <th>主行名称</th>
                    <th>主行代码</th>
                @foreach($list as $key => $value)

                    <tr>
                        <td>{{$value->id}}</td>
                        <td>{{$value->master_bank_name}}</td>
                        <td>{{$value->bank_code}}</td>
                    </tr>


                    @endforeach

                </table>
            </div>
        </div>
    </body>
</html>
