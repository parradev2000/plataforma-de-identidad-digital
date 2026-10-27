<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            /* margin: auto;
            padding: 50px; */
        }
        table {
            margin: auto;
            border-collapse:collapse;
            width: 100%;
        }
        table, td, th {
            border: 1px solid black;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Auditoría</h1>
    <table>
        <tr>
            <th>Id</th>
            <th>Usuario</th>
            <th>Nombres</th>
            <th>Accion</th>
            <th>Fecha</th>
        </tr>
        <?php $var =0 ?>
        @foreach($auditoria as $auditoria)
            <tr>
                <td>{{ $var+=1 }}</td>
                <td>{{ $auditoria['usuario'] }}</td>
                <td>{{ $auditoria['nombres'] }}</td>
                <td>{{ $auditoria['accion'] }}</td>
                <td>{{ date('d-m-Y H:i:s', strtotime($auditoria['created_at'])) }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
</html>
