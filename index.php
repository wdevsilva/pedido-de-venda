<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedido de Venda</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/jquery.min.js"></script>
</head>

<body style="background-color: #ccc;">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-warning" style="margin-top: 2%;">
            <form action="#" class="form-control" style="width: 800px; margin: 0 auto;">
                <input type="text" name="nsu" id="nsu" class="form-control" placeholder="Digite a NSU, cpf aluno">
                <br>
                <center><button type="button" class="btn btn-primary" id="enviar">Consultar</button></center>
            </form>
            <form action="#" class="form-control" style="width: 800px; margin: 0 auto;">
                <input type="text" name="cpf" id="cpf" class="form-control" placeholder="Digite o cpf do aluno">
                <br>
                <center><button type="button" class="btn btn-primary" id="poscli">Consultar Poscli</button></center>
            </form>
        </nav>
        <hr>
        <nav class="navbar navbar-expand-lg navbar-light" style="margin-top: 2%;">
            <div id="result"></div>
        </nav>
    </div>
    <script>
        $(document).ready(function() {
            $("#enviar").click(function() {

                $("#enviar").prop('disabled', true);

                var dados = document.getElementById('nsu').value;

                if(dados == ''){
                    alert('Favor informe a nsu, cpf do aluno');
                    $("#enviar").prop('disabled', false);
                    return false;
                }

                $.ajax({
                    type: "GET",
                    url: "includes/consulta.php?nsu=" + dados,
                    beforeSend: function() {
                        $('#result').fadeIn();
                        document.getElementById("result").innerHTML = '';
                        document.getElementById("result").innerHTML = '<p style="margin-left: 50%;"><img src="images/loading.gif"></p>';
                    },
                    success: function(data) {
                        $("#enviar").prop('disabled', false);

                        $('#result').html(data);
                    }
                });
            });
            
            $("#poscli").click(function() {

                $("#poscli").prop('disabled', true);

                var dados = document.getElementById('cpf').value;

                if(dados == ''){
                    alert('Favor informe o cpf do aluno');
                    $("#poscli").prop('disabled', false);
                    return false;
                }

                $.ajax({
                    type: "GET",
                    url: "includes/poscli.php?cpf=" + dados,
                    beforeSend: function() {
                        $('#result').fadeIn();
                        document.getElementById("result").innerHTML = '';
                        document.getElementById("result").innerHTML = '<p style="margin-left: 50%;"><img src="images/loading.gif"></p>';
                    },
                    success: function(data) {
                        $("#poscli").prop('disabled', false);

                        $('#result').html(data);
                    }
                });
            });
        });
    </script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>