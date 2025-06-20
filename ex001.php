    <?php 
    
        /* organizando meus pensamentos antes de começar o código, preciso fazer uma variável em que seja colocada uma chave pix, e também validar se a mesma é correta, outra que possa ser inserido um valor em reais e então estruturar uma função que gere o código PIX, sempre necessário os IDs 00, 26-51, 52, 53, 58, 59, 60, 62 e 63 e depois, podendo usar biblioteca, gerar um QR Code(opcional).
    
        */

        function construcaoPixSemCRC($tamanhoCampo, $tamanhoChave, $chave, $valorTamanho, $valor, $nomeTamanho, $nome, $tamanhoCidade, $cidade) {


            $copiaeColaSemCRC = "00020126{$tamanhoCampo}0014BR.GOV.BCB.PIX01{$tamanhoChave}{$chave}52040000530398654{$valorTamanho}{$valor}5802BR59{$nomeTamanho}{$nome}60{$tamanhoCidade}{$cidade}62070503***6304";

            return $copiaeColaSemCRC;

            /* Função que monta a estrutura do código copia e cola, antes de colocar o CRC para deixar o copia e cola funcional, requer os parâmetros descritos acima. */ 


        }

        function construcaoPix($tamanhoCampo, $tamanhoChave, $chave, $valorTamanho, $valor, $nomeTamanho, $nome, $tamanhoCidade, $cidade, $hex) {

            $copiaeCola = "00020126{$tamanhoCampo}0014BR.GOV.BCB.PIX01{$tamanhoChave}{$chave}52040000530398654{$valorTamanho}{$valor}5802BR59{$nomeTamanho}{$nome}60{$tamanhoCidade}{$cidade}62070503***6304{$hex}";

            return $copiaeCola;

            /* Função que monta a estrutura do código copia e cola, já com o CRC para deixar o copia e cola funcional, requer os parâmetros descritos acima. */

        }

        function tamanhoCampo26($chave){

            $tamanhoChave = strlen($chave);
            $tamanhoCampo = $tamanhoChave + 22;

            return $tamanhoCampo;

            /* Função que apenas monta o tamanho do campo26, pegando o tamanho da chave e adicionando 22, que no caso é o BR.GOV.BCB.PIX mais o id dele e o tamanho descrito em dois digitos cada, mais o id e o tamanho da chave descrito em dois digitos cada. */

        }

        function crc16($copiaeColaSemCRC) {
         function charCodeAt($copiaeColaSemCRC, $i) {
            return ord(substr($copiaeColaSemCRC, $i, 1));
         }
            $crc = 0xFFFF;
            $strlen = strlen($copiaeColaSemCRC);
            for($c = 0; $c < $strlen; $c++) {
                 $crc ^= charCodeAt($copiaeColaSemCRC, $c) << 8;

            for($i = 0; $i < 8; $i++) {
                  if($crc & 0x8000) {
                     $crc = ($crc << 1) ^ 0x1021;
                  } else {
                     $crc = $crc << 1;
                  }
            }
         }
         $hex = $crc & 0xFFFF;
         $hex = dechex($hex);
         $hex = strtoupper($hex);
         $hex = str_pad($hex, 4, '0', STR_PAD_LEFT);

         return $hex;

         /* Função que gera o CRC passado via email do Andre, basicamente transforma toda o codigo do pix copia e cola sem CRC em bytes, jogando para hecadecimal e retornando o mesmo na variável hex em 4 digitos e letra maiuscula. */

      }

        function validarTipoChave($tipo_chave) {

                $array = ['cpf', 'cnpj', 'email', 'telefone', 'aleatoria'];

                
                  if (in_array($tipo_chave, $array)) {
                         
                        return true;
                    }

                    echo "Tipo de chave PIX inválido: $tipo_chave\n";
                    return false;
        }

        /* Essa função válida o tipo da chave pix que o usuário escolheu, é montado um array que contém os tipos corretos de chave PIX, e então usado um if para verificar se o que o usuário passou está dentro deste array. */


        function validarChavePix($chave, $tipo_chave) {

            /* Função para verificar se a chave escolhida do usuário, montado um if para cada tipo válido de chave PIX. */
          
             $padrao_num = "/[0-9]+/";
             // Verifica se a chave é numérica.

            if ($tipo_chave == 'cpf') {
                
                if(strlen($chave) == 11 && $chave != null) {
                    if (!preg_match($padrao_num, $chave)) {
                        echo "CPF inválida: $chave\n";
                        return false;
                    }
                } else {
                    echo "CPF inválida: $chave\n";
                    return false;

                }
                return true;

                // Verifica se a chave é um CPF válido, que deve ter 11 dígitos numéricos.

            } elseif ($tipo_chave == 'cnpj') {

                if(strlen($chave) == 14 && $chave != null) {
                    if (!preg_match($padrao_num, $chave)) {
                        
                        echo "CNPJ inválida: $chave\n";
                        return false;
                    }
                } else {
                    
                    echo "CNPJ inválida: $chave\n";
                    return false;

                }

                return true;

                // Verifica se a chave é um CNPJ válido, que deve ter 14 dígitos numéricos.

            } elseif ($tipo_chave == 'email') {

                if (!filter_var($chave, FILTER_VALIDATE_EMAIL)) {
                    
                    echo "Email inválido: $chave\n";
                    return false;

                }
                
                return true;

                // Verifica se a chave é um email válido, usando a função filter_var com o filtro FILTER_VALIDATE_EMAIL.

            } elseif ($tipo_chave == 'telefone') {
                $padrao_tel = "/^\+[0-9]{12,13}$/";
                if (strlen($chave) == 13 || strlen($chave) == 14 && $chave != null) {

                    if (!preg_match($padrao_tel, $chave)) {

                        echo "Telefone inválido: $chave\n";
                        return false;
                    }

                } else {

                    echo "Telefone inválido: $chave\n";
                    return false;
                    
                }

                return true;

                // Verifica se a chave é um telefone válido, que deve ter 12 ou 13 dígitos numéricos, começando com o sinal de mais (+) e seguido pelo código do país (55 para o Brasil).

            } elseif ($tipo_chave == 'aleatoria') {

                if (strlen($chave) != 36) {

                    echo "Chave aleatória inválida: $chave\n";
                    return false;

                }

            } else {

                echo "Tipo de chave PIX inválido: $tipo_chave\n";
                return false;
    
            }

            return true;

            // Verifica se a chave é uma chave aleatória válida, que deve ter 36 caracteres (formato UUID).

        }
    
        function validarValor($valor) {

                if($valor <=0){

                    echo "Valor inválido: $valor\n";
                    return false;

                } elseif (!is_numeric($valor)){

                    echo "Valor deve ser numérico: $valor\n";
                    return false;

                } elseif (strlen($valor) > 13){

                    echo "Valor deve ter no máximo 13 dígitos: $valor\n";
                    return false;

                }

                return true;

                /* Função para validar o valor inserido pelo usuário, verifica se o valor está abaixo de zero, se o valor é numérico e se contém mais de 13 digitos. */

        }

        function validarNome($nome) {
           $padrao_nome = "/^[A-Z\s]+$/";
            if (!preg_match($padrao_nome, $nome)) {

                echo "Nome inválido: $nome\n";
                return false;

            }elseif (empty($nome)) {

                echo "Nome não pode ser vazio.\n";
                return false;

            } else if (strlen($nome) > 25) {

                echo "Nome deve ter no máximo 25 caracteres: $nome\n";
                echo "Use abreviação se necessário.\n";
                return false;
            }

            return true;

            /* Função para validar o Nome, verifica se o nome contém apenas letras, se o nome está vazio e se contém mais de 25 caractéres. */
        }

        function validarCidade($cidade) {
            $padrao_cidade = "/^[A-Z\s]+$/";
            if(!preg_match($padrao_cidade, $cidade)){

                echo "Cidade inválida: $cidade\n";
                return false;

            }elseif (empty($cidade)) {

                echo "Cidade não pode ser vazia.\n";
                return false;

            } else if (strlen($cidade) > 15) {

                echo "Cidade deve ter no máximo 15 caracteres: $cidade\n";
                return false;

            }
            return true;

            /* Função para validar a Cidade, verifica se a cidade contém apenas letras, se a cidade está vazia e se contém mais de 15 caractéres. */

        }
        function main(){

            $nome;
            $valor;
            $tipo_chave;
            $chave;
            $cidade;
            $crc;

            echo "Gerador de PIX copia e cola\n";
            echo "Digite os dados abaixo para gerar o código PIX:\n";

            echo "Digite o nome do recebedor (máximo 25 caracteres, se for muito longo, use abreviação):\n";
            $nome = trim(fgets(STDIN));
            $nome = strtoupper($nome);
            $nome = str_replace(' ', '', $nome);
            validarNome($nome);
            $nomeTamanho = strlen($nome);
            $nomeTamanho = str_pad($nomeTamanho, 2, '0', STR_PAD_LEFT);

            echo "Digite o valor a ser transferido (máximo 13 dígitos):\n";
            $valor = trim(fgets(STDIN));
            validarValor($valor);
            $valorTamanho = strlen($valor);
            $valorTamanho = str_pad($valorTamanho, 2, '0', STR_PAD_LEFT);

            echo "Digite o tipo de chave PIX (cpf, cnpj, email, telefone, aleatoria):\n";
            $tipo_chave = trim(fgets(STDIN));
            $tipo_chave = strtolower($tipo_chave);
            validarTipoChave($tipo_chave);

            echo "Digite a chave PIX:\n";
            $chave = trim(fgets(STDIN));
            if($tipo_chave == 'telefone'){

                if (!str_starts_with($chave, '+55')){

                    $chave = '+55' . $chave;

                }

                //verifica se a chave é um telefone, e se não começa com +55, adiciona o código do país para o Brasil.

            }
            validarChavePix($chave, $tipo_chave);

            echo "Digite a cidade do recebedor (máximo 15 caracteres):\n";
            $cidade = trim(fgets(STDIN));
            validarCidade($cidade);
            $cidade = strtoupper($cidade);
            $cidadeTamanho = strlen($cidade);
            $cidadeTamanho = str_pad($cidadeTamanho, 2, '0', STR_PAD_LEFT);


            $tamanhoChave = strlen($chave);
            $tamanhoChave = str_pad($tamanhoChave, 2, '0', STR_PAD_LEFT);

            $tamanhoCampo = tamanhoCampo26($chave);
            $tamanhoCampo = str_pad($tamanhoCampo, 2, '0', STR_PAD_LEFT);

            $copiaeColaSemCRC = construcaoPixSemCRC($tamanhoCampo, $tamanhoChave, $chave, $valorTamanho, $valor, $nomeTamanho, $nome, $cidadeTamanho, $cidade);

            $hex = crc16($copiaeColaSemCRC);

            $copiaeCola = construcaoPix($tamanhoCampo, $tamanhoChave, $chave, $valorTamanho, $valor, $nomeTamanho, $nome, $cidadeTamanho, $cidade, $hex);

            echo "Código PIX gerado com sucesso: $copiaeCola\n";

        }
        
        main();


    ?>
