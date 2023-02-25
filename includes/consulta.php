<?php

require '../config/conexao.php';

$dados = explode(',', $_REQUEST['nsu']);

$nsu = $dados[0];
$cpf_aluno = $dados[1];

if($nsu == '' || $cpf_aluno == ''){
    echo "<div class='alert alert-warning'>Favor informe o nsu e o cpf do aluno, separaods por virgula.</div>";
    exit;
}

$pdo = conectar();
$query_nsu = $pdo->prepare("SELECT order_id,cpf_aluno FROM tbl_rede_transacao WHERE nsu = '$nsu'");
$query_nsu->execute();

if ($query_nsu->rowCount() == 0) {
    
    $filtro = "cpf = '$cpf_aluno'";
} else {
    $result_nsu = $query_nsu->fetch(PDO::FETCH_OBJ);

    $filtro = "cpf  = '$result_nsu->cpf_aluno' AND order_id = '$result_nsu->order_id'";
}

$query_preproc_cadastro_pf = $pdo->prepare("SELECT id,order_id, CAST(data_cadastro AS DATE) AS data_cadastro, nome FROM tbl_ecommerce_preproc_cadastro_pf WHERE $filtro");
$query_preproc_cadastro_pf->execute();
$result_preproc_cadastro_pf = $query_preproc_cadastro_pf->fetch(PDO::FETCH_OBJ);

if($query_preproc_cadastro_pf->rowCount() == 0){
    echo "<div class='alert alert-danger'>Desculpe, não há registro de venda encontrado para os dados informados!</div>";
    exit;
}

$query_preproc_matricula = $pdo->prepare("SELECT situacao_matricula, nome_produto_loja,valor FROM tbl_ecommerce_preproc_matricula WHERE id_pedido_loja = '$result_preproc_cadastro_pf->order_id'");
$query_preproc_matricula->execute();
$result_preproc_matricula = $query_preproc_matricula->fetch(PDO::FETCH_OBJ);

if ($result_preproc_matricula->situacao_matricula == "ENVIADO PARA A SALESFORCE COM SUCESSO") {

    $query_salesforce_oportunidade_preproc = $pdo->prepare("SELECT id_oportunidade FROM tbl_salesforce_oportunidade_preproc WHERE id_preproc_cadastro_pf = '$result_preproc_cadastro_pf->id'");
    $query_salesforce_oportunidade_preproc->execute();
    $result_salesforce_oportunidade_preproc = $query_salesforce_oportunidade_preproc->fetch(PDO::FETCH_OBJ);

    $query_salesforce_oportunidade = $pdo->prepare("SELECT id, id_salesforce FROM tbl_salesforce_oportunidade WHERE id = '$result_salesforce_oportunidade_preproc->id_oportunidade'");
    $query_salesforce_oportunidade->execute();
    $result_salesforce_oportunidade = $query_salesforce_oportunidade->fetch(PDO::FETCH_OBJ);

    //VERIFICAR LOG SALESFORCE
    $query_salesforce_log = $pdo->prepare("SELECT postfields, result FROM tbl_salesforce_log WHERE cast(data_cadastro as date) >= '$result_preproc_cadastro_pf->data_cadastro' AND postfields LIKE '%$result_salesforce_oportunidade->id_salesforce%';");
    $query_salesforce_log->execute();
    $result_salesforce_log = $query_salesforce_log->fetch(PDO::FETCH_OBJ);

    $dados_salesforce_log = json_decode($result_salesforce_log->result, true);

    if ($dados_salesforce_log['msg'] == "Contrato atualizado com sucesso!") {

        //VERIFICAR LOG SGE
        $query_sge_log = $pdo->prepare("SELECT postfields, result FROM tbl_sge_log WHERE postfields LIKE '%$result_salesforce_oportunidade->id_salesforce%'");
        $query_sge_log->execute();
        $result_sge_log = $query_sge_log->fetch(PDO::FETCH_OBJ);

        $dados_sge_postfileds_log = json_decode($result_sge_log->postfields, true);
        $dados_sge_result_log = json_decode($result_sge_log->result, true);       


        if ($dados_sge_result_log['StatusCode'] == 200) {
            $xnumpro = $dados_sge_result_log['Matricula']['XNumPro'];

            //VERIFICA PEDIDO DE VENDAS NO PROTHEUS
            $query_pedido_protheus = $pdo->prepare("SELECT C5_XNUMPRO,C5_XPARCEL,C5_XQTDPAR,D_E_L_E_T_,C5_YSERIE,C5_YPEDFIS,C5_FILIAL,C5_NUM,C5_CLIENTE,C5_CONDPAG,C5_EMISSAO,C5_FECENT,
            C5_PARC1,C5_DATA1,C5_NOTA,C5_SERIE,C5_MENNOTA,C5_MENPAD,R_E_C_N_O_,C5_YFORMAP,C5_YMATRIC,C5_YFAT,C5_YSLDPED,C5_YDTRESI
            FROM CLSQL01.DADOSERP.dbo.SC5010
            WHERE C5_XNUMPRO = '$xnumpro'");
            $query_pedido_protheus->execute();
            $result_pedido_protheus = $query_pedido_protheus->fetch(PDO::FETCH_OBJ);           

            if ($query_pedido_protheus->rowCount() != 0) {
                echo "<div class='alert alert-success'><b><h4>Já existe pedido de venda para o Xnumpro $xnumpro</h4></b> <br>
                <b>Aluno(a):</b> ".$result_preproc_cadastro_pf->nome."<br>
                <b>Data Matricula:</b> ".$dados_sge_postfileds_log['DataMatricula']."<br>
                <b>Produto:</b> ".$result_preproc_matricula->nome_produto_loja."<br>
                <b>Valor Produto:</b> ".$result_preproc_matricula->valor."<br>
                <b>Resp Finan:</b> ".$dados_sge_postfileds_log['Contrato']['NomeRespFin']."<br>
                <b>Valor Total:</b>". $dados_sge_postfileds_log['Contrato']['ValorTotal']."<br>
                <b>Valor Total Pg Cartão:</b>". $dados_sge_postfileds_log['Contrato']['DadosCartao']['ValorTotalCartao']."<br>
                <b>Id Oportunidade:</b> ".$dados_sge_postfileds_log['Contrato']['IdOportunidade']."<br>                
                </div>";
            } else {
                echo "<div class='alert alert-danger'>Não existe pedido de venda para o Xnumpro <b>$xnumpro</b>, favor solicitar a equipe do Protheus</div>";
                echo "<div class='alert alert-warning'>
                    <b>RA:</b> $dados_sge_postfileds_log[RA] <br>
                    <b>XNumPro:</b>: $xnumpro <br>
                    <b>Coligada:</b>: $dados_sge_postfileds_log[CodColigada]
                    <br>
                    <b>Postfildes</b><br>
                    <pre>$result_sge_log->postfields</pre>
                    <b>Result</b><br>                    
                    <pre>$result_sge_log->result</pre>
                </div>";
            }
        } else {
            echo "<div class='alert alert-danger'><pre>$dados_sge_result_log[Message]</pre></div>";
        }

        // echo '<pre>';
        // print_r($dados_sge_result_log);
        // echo '</pre>';
    } else {
        echo '<pre>';
        print_r($result_salesforce_log);
        echo '</pre>';
    }
} else {
    echo "<div class='alert alert-warning'>$result_preproc_matricula->situacao_matricula</div>";
}