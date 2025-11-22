// Classe interna, carregada no OnLoad
var Interna = {

    tem_aba: false,

    // Método principal da classe, executa em todas as views
    init: function () {
        Form.desabilitaBotoesById("botao-excluir");
        Form.desabilitaBotoesById("botao-relatorio");

        // document.getElementById('tit_no_sistema').innerText = ' ';
        // document.getElementById('tit_no_menu').innerText = 'Gestor de Demandas Públicas';
        // document.getElementById('tit_no_transacao').innerText = ' ';

        $("#btnEnviar").bind('click', function() {
            Interna.abrirPopup('3')
        });

        $("#btnAbrirNotas").bind('click', function() {
            Interna.abrirPopupNotas();
        });

        $("#btnVoltar").click(function() {
            parent.$("#protecao_tela").hide();
            parent.Base.JanelaFlutuante.close();
        });

        $("#btnSalvarArquivo").click(function() {
            if (Interna.validaArquivos() == true) {
                Interna.salvaArquivos();
            }
        });

        $("#btnSalvarNota").click(function() {
            Interna.salvaNotas();
        });

        $("#btnAdicionarArquivo").click(function() {
            Interna.novoCampo();
        });

    },

    // Chama o evento quando estiver na action novo
    initNovoAction: function () {

    },

    // Chama o evento quando estiver na action novo
    initSelecionarAction: function () {

    },


    // Gera um novo documento
    novo: function () {
        document.location.href = baseUrlController + "/novo";
    },

    // Salva o registro
    salvar: function () {
        // Faz a validação do formulário
        var valida = Valida.formulario("form_base", true);

        if (valida[0]) {
            $("#form_base").attr({
                onsubmit: "return true",
                action: baseUrlController + "/salvar",
                target: ""
            }).trigger("submit");
        }

    },

    // Excluir o registro
    excluir: function () {

    },

    // Chama a tela de pesquisa
    telaPesquisa: function () {
        document.location.href = `${baseUrlController}/pesquisar`;
    },


    // Executa a tela de pesquisa
    pesquisar: function () {

        var dtIni = $("#dt_ini").val();
        var dtFim = $("#dt_fim").val();
        var msg_data = "A data inicial do período não pode ser maior que data final";

        var datasOk = true;

        if((dtIni.length > 0 && dtFim.length > 0) && (!Data.verificaIntervaloData(dtIni, dtFim))) {
            var datasOk = false;
        }

        if (datasOk) {
            $('#form_pesquisar').attr({
                onsubmit: 'return true',
                action: baseUrlController + '/pesquisar',
                target: ''
            }).submit();
        } else {
            Base.montaMensagemSistema(Array(msg_data), "ATENÇÃO", 4);
        }


    },


    // Seleciona um registro, recebendo a chave já criptografada
    selecionar: function (chave) {
        document.location.href = baseUrlController + "/selecionar/" + chave;
    },

    // Chama a tela de relatórios
    relatorio: function () {

    },

    // Volta para a listagem
    voltar: function () {
        document.location.href = paginaAnterior;
    },

    // Autocomplete com passagem de parâmentro
    initAutoCompleteAjax: function (no_campo) {

    },

    abrirPopup: function(tipo) {

        $("#janela-flutuante").removeClass("arquivoHeight");

            var texto = "Anexar Arquivo";

            // Adiciona um texto ao cabeçalho da pop up
            $("#janela-flutuante").addClass("arquivoHeight");
            $("#jfHeader").html("");
            $("#jfHeader").html("<span style='color:white;'>" + texto + "</span>");

            var seq_chamado = $('#seq_chamado').val();
            var ano_chamado = $('#ano_chamado').val();
            var no_arquivo = $('#no_arquivo').val();
            chave = "seq_chamado/" + seq_chamado + "/ano_chamado/" + ano_chamado + "/no_arquivo/" + no_arquivo;

            Base.JanelaFlutuante.load(baseUrlController + "/tela-arquivo/" + chave);

    },

    abrirPopupNotas: function() {

        $("#janela-flutuante").removeClass("notaHeight");

        var texto = "Notas";

        // Adiciona um texto ao cabeçalho da pop up
        $("#janela-flutuante").addClass("notaHeight");
        $("#jfHeader").html("");
        $("#jfHeader").html("<span style='color:white;'>" + texto + "</span>");

        var seq_chamado = $('#seq_chamado').val();
        var ano_chamado = $('#ano_chamado').val();

        chave = "seq_chamado/" + seq_chamado + "/ano_chamado/" + ano_chamado;

        Base.JanelaFlutuante.load(baseUrlController + "/tela-nota/" + chave);
    },

    validaArquivos: function() {
        var retorno = true;
        var arq_nome = $("#no_arquivo").val();

        var reg = /^[A-Za-z0-9- ]+$/;
        if (!reg.test(arq_nome)) {
            Base.montaMensagemSistema(Array('Caracter invalido! É permitido apenas letras sem acentos,"-" e números.'), "Atenção", 4);
            retorno = false;
        }
        var arq_file = $("#local_arquivo").val();
        var ext = arq_file.split(".");

        if (arq_nome == "") {
            Base.montaMensagemSistema(Array("Existe um campo de Título não preenchido"), "Atenção", 4);
            retorno = false;

        } else if (arq_file == "") {
            Base.montaMensagemSistema(Array("Existe um campo de Arquivo não preenchido"), "Atenção", 4);
            retorno = false;
        } else if (ext[1] != "bmp" && ext[1] != "jpg" && ext[1] != "jpeg" && ext[1] != "png" && ext[1] != "pdf") {
            Base.montaMensagemSistema(Array("Os arquivos de tipo " + ext[1] + " não são permitidos"), "Atenção", 4);
            retorno = false;
        }

        return retorno;
    },

    salvaArquivos: function() {

        var seq_chamado = parent.$("#seq_chamado").val();
        var ano_chamado = parent.$("#ano_chamado").val();
        var no_arquivo = $('#no_arquivo').val();
        let chave = "seq_chamado/" + seq_chamado + "/ano_chamado/" + ano_chamado + "/no_arquivo/" + no_arquivo;

        $("#form_popup_arquivo").attr({
            onsubmit: "return true",
            action: baseUrlController + '/tela-arquivo/' + chave,
            target: ""
        }).submit();

    },

    salvaNotas: function() {

        var seq_chamado = parent.$("#seq_chamado").val();
        var ano_chamado = parent.$("#ano_chamado").val();
        var no_arquivo = $('#no_arquivo').val();
        let chave = "seq_chamado/" + seq_chamado + "/ano_chamado/" + ano_chamado + "/insere_relato/1" + "/no_arquivo/" + no_arquivo;

        $("#form_popup_nota").attr({
            onsubmit: "return true",
            action: baseUrlController + '/tela-nota/' + chave,
            target: ""
        }).submit();

    },

    novoCampo: function() {
        // Quantidade de inputs na tela
        var i_ultimo = $("#corpo_arquivos").find(".nome_arquivo").size();
        var prox_id = i_ultimo + 1;

        linha = '<tr id="novos_campos">';
        linha += '<td>';
        linha += '<input type="text" name="no_arquivo[]" id="no_arquivo_' + (prox_id) + '" class="nome_arquivo stru" maxlength="50"></input>';
        linha += '</td>';
        linha += '<td>';
        linha += '<input name="arquivo[]" id="arquivo_' + (prox_id) + '" class="file arquivo" type="file" >';
        linha += '</td>';
        linha += '</tr>';

        $("#tb_arquivo").find("#corpo_arquivos").append(linha);

    },

    excluirCampo: function(obj, no_arquivo, extensao) {
        var seq_chamado = parent.$("#seq_chamado").val();
        var ano_chamado = parent.$("#ano_chamado").val();

        $.ajax({
            type: "GET",
            async: false,
            url: baseUrlController + "/excluir-arquivo-x/",
            data: { seq_chamado: seq_chamado,
                    ano_chamado: ano_chamado,
                    no_arquivo: no_arquivo,
                    extensao: extensao },
            success: function(retorno) {
                $(".arquivo_" + no_arquivo).hide();
                Base.montaMensagemSistema(Array("Documento Excluido Com Sucesso!"), "Sucesso", 2);
                parent.Base.JanelaFlutuante.close();
            }
        });
        return true;

    },

    // Valida alguns dados para cadastrar os dados
    valida: function () {

        // Instancia as variáveis de controle
        var mensagem = Array();

        // Chama a validação do sistema
        valida = Valida.formulario('form_pesquisar', true, mensagem);

        return valida;
    }


};
