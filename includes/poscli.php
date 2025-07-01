<?php

require '../config/conexao.php';

$cpf_aluno = substr(trim($_REQUEST['cpf']), 0, 9);

$pdo = conectar();
$query_poscli = $pdo->prepare("select * from (
		SELECT
			C5_CLIENTE as CLIENTE,
			rtrim(A1_CGC) as CPF,
			rtrim(isnull(E1_BAIXA,'')) as DATABAIXA,
			C5_EMISSAO as DATAPEDIDO,
			C5_FILIAL as FILIAL,
			C5_NOTA,
			A1_LOJA as LOJA,
			rtrim(A1_NOME) as NOME,
			isnull(E1_NUM,'') as NUM,
			C5_NUM as NUMPEDIDO,
			isnull(E1_PARCELA,C5_XPARCEL) as PARCELA,
			isnull(E1_PREFIXO,'') as PREFIXO,
			isnull(E1_SALDO,C5_PARC1) as SALDO,
			isnull(E1_SITUACA,'') as SITUACAO,
			case
				when E1_SALDO = 0 then 'B'
				when E1_SALDO > 0 and convert(date,getdate()) <= E1_VENCREA  then 'A'
				when E1_SALDO > 0 and convert(date,getdate()) > E1_VENCREA  then 'V'
				else 'PNF'
			end as 'STATUS',
			rtrim(isnull(E1_TIPO,'')) as TIPO,
			isnull(E1_VALOR,C5_PARC1) as VALOR,
			C6_VALOR as VALOR_PEDIDO,
			isnull(E1_VENCREA,'') as VENCIEMENTOREAL,
			isnull(E1_VENCTO,C5_DATA1) as VENCIMENTO,
			rtrim(C5_XNUMPRO) as XNUMPRO,
			(
				select top 1 ltrim(rtrim(F2_NFELETR)) as NOTA
				from dbo.SF2010 F2 
				where F2_FILIAL = C5_FILIAL 
				AND F2_CLIENTE = C5_CLIENTE 
				AND F2_LOJA = C5_LOJACLI 
				AND F2_DOC = C5_NOTA 
				AND F2_SERIE = C5_SERIE
				AND F2.D_E_L_E_T_=''
			) as NOTA
		FROM (
			select 
				C5_XNUMPRO,
				C5_CLIENTE,
				C5_LOJACLI,
				C5_FILIAL,
				C5_NUM,
				E1_BAIXA,
				C5_EMISSAO,
				E1_NUM,
				E1_PARCELA,
				C5_XPARCEL,
				E1_PREFIXO,
				E1_SALDO,
				C5_PARC1,
				E1_SITUACA,
				E1_VENCREA,
				E1_TIPO,
				E1_VALOR,
				E1_VENCTO,
				C5_DATA1,
				C5_NOTA,
				C5_SERIE,
				C5_YPEDFIS
			from dbo.SC5010 C5 with(nolock)  
			LEFT JOIN dbo.SE1010 SE1 WITH (NOLOCK) ON (C5_FILIAL=E1_FILIAL AND C5_NOTA=E1_NUM AND C5_SERIE=E1_SERIE AND C5.D_E_L_E_T_=' ') 
				AND E1_YNUMPRO = C5_XNUMPRO
				AND SE1.E1_TIPO NOT IN ('NCC') 
				AND SE1.D_E_L_E_T_= ' ' 
			WHERE E1_YNUMPRO is not null or (
				C5.C5_NOTA = ' ' and
				C5.D_E_L_E_T_= ' ' and
				C5.C5_TIPO = 'N'
			)
			union all
			select
				E1_YNUMPRO,
				E1_CLIENTE,
				E1_LOJA,
				E1_FILIAL,
				C5_NUM,
				E1_BAIXA,
				C5_EMISSAO,
				E1_NUM,
				E1_PARCELA,
				C5_XPARCEL,
				E1_PREFIXO,
				E1_SALDO,
				C5_PARC1,
				E1_SITUACA,
				E1_VENCREA,
				E1_TIPO,
				E1_VALOR,
				E1_VENCTO,
				C5_DATA1,
				C5_NOTA,
				C5_SERIE,
				'N'
			FROM clsql01.DADOSERP.dbo.SE1010 E1 with(nolock)  
			LEFT JOIN clsql01.DADOSERP.dbo.SC5010 C5 WITH (NOLOCK) ON (C5_FILIAL=E1_FILIAL AND C5_NOTA=E1_NUM AND C5_SERIE=E1_SERIE AND C5.D_E_L_E_T_=' ') 
				AND E1_YNUMPRO = C5_XNUMPRO
			where E1_TIPO NOT IN ('NCC','CC','CD','RA') 
			and E1.D_E_L_E_T_= ' ' 
			and E1_YNUMPRO is not null
			and C5_NOTA is null
		) as C5
		LEFT JOIN clsql01.DADOSERP.dbo.SA1010 A1 ON (A1_FILIAL=' ' AND A1_COD=C5_CLIENTE AND A1_LOJA=C5_LOJACLI AND A1.D_E_L_E_T_=' ') 
		LEFT JOIN (
			select C6_FILIAL, C6_NUM, SUM(C6_VALOR) as C6_VALOR 
			from clsql01.DADOSERP.dbo.SC6010
			where D_E_L_E_T_=' '
			group by C6_FILIAL, C6_NUM
		) C6 ON (C5_FILIAL=C6_FILIAL AND C5_NUM=C6_NUM ) 
		where C5.C5_XNUMPRO = '$cpf_aluno' or isnull(C5.C5_CLIENTE,'') like '$cpf_aluno'
	) vw
	order by NUMPEDIDO");
$query_poscli->execute();

if ($query_poscli->rowCount() == 0) {
    echo "<div class='alert alert-warning'><pre>Não há dados para o cliente informado</pre></div>";
} else {
    foreach ($query_poscli as $r) {
        $arrayFiltrado = array_filter($r, function ($key) {
            return is_string($key);
        }, ARRAY_FILTER_USE_KEY);

        echo "<pre>";
        print_r($arrayFiltrado);
        echo "</pre>";
    }
}
