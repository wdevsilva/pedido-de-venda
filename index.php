<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pedido de Venda</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="js/jquery.min.js"></script>
    <style>
        .form-section {
            margin-top: 2rem;
        }

        .loading {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row form-section">
            <div class="col-md-6 mb-4">
                <form id="formNsu" class="p-3 bg-warning rounded">
                    <label for="nsu" class="form-label">NSU ou CPF do Aluno</label>
                    <input type="text" name="nsu" id="nsu" class="form-control" placeholder="Digite a NSU ou CPF">
                    <div class="d-grid mt-3">
                        <button type="button" class="btn btn-primary" id="btnNsu">Consultar</button>
                    </div>
                </form>
            </div>

            <div class="col-md-6 mb-4">
                <form id="formCpf" class="p-3 bg-warning rounded">
                    <label for="cpf" class="form-label">CPF do Aluno ou XNUMPRO</label>
                    <input type="text" name="cpf" id="cpf" class="form-control" placeholder="Digite o CPF do Aluno">
                    <div class="d-grid mt-3">
                        <button type="button" class="btn btn-primary" id="btnCpf">Consultar Poscli</button>
                    </div>
                </form>
            </div>
        </div>
        <hr>
        <div id="result" class="mt-4"></div>
    </div>

    <script>
        $(document).ready(function() {

            function realizarConsulta(btnId, inputId, endpoint, mensagemErro) {
                const $btn = $(btnId);
                const valor = $(inputId).val().trim();

                if (valor === '') {
                    alert(mensagemErro);
                    return;
                }

                $btn.prop('disabled', true);
                $('#result').html('<div class="loading"><img src="images/loading.gif" alt="Carregando..."></div>');

                $.ajax({
                    type: "GET",
                    url: `includes/${endpoint}.php?${inputId.substring(1)}=${encodeURIComponent(valor)}`,
                    success: function(data) {
                        $('#result').html(data);
                    },
                    error: function() {
                        $('#result').html('<div class="alert alert-danger">Erro ao consultar. Tente novamente.</div>');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            }

            $('#btnNsu').on('click', function() {
                realizarConsulta('#btnNsu', '#nsu', 'consulta', 'Por favor, informe a NSU ou CPF do aluno.');
            });

            $('#btnCpf').on('click', function() {
                realizarConsulta('#btnCpf', '#cpf', 'poscli', 'Por favor, informe o CPF do aluno.');
            });

        });
    </script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>