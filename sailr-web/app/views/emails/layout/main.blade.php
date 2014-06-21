@if(!isset($isPartial))
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width"/>

    @include('emails.layout.css.ink')

    <style type="text/css">

        /* Your custom styles go here */

    </style>
</head>
@endif

<body>
<table class="body">
    <tr>
        <td class="center" align="center" valign="top">
            <center>

                @yield('content')

            </center>
        </td>
    </tr>
</table>

@if(!isset($isPartial))
</body>
</html>
@endif