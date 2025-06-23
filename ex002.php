    <?php 
    
        /* organizando meus pensamentos antes de começar o código, preciso fazer uma variável em que seja colocada uma chave pix, e também validar se a mesma é correta, outra que possa ser inserido um valor em reais e então estruturar uma função que gere o código PIX, sempre necessário os IDs 00, 26-51, 52, 53, 58, 59, 60, 62 e 63 e depois, podendo usar biblioteca, gerar um QR Code (opcional).


        Organizando meus pensamentos novamente, após conseguir ter feito o código funcionar, agora preciso otimizá-lo. Algumas idéias que tive foram:
            1. Para validar o tipo de chave, montar um array associativo com os tipos de chaves e dentro do mesmo passar os padrões de validação de cada tipo de chave, dessa forma, consigo diminuir o número de ifs usados. -- Feito.
            2. Talvez juntar todas as validações em uma só função, deixando apenas para passar os parâmetros necessários. -- Meio concluído, e provavelmente irá ficar dessa forma.
            2.5. Foi agrupado as funções de validar nome e validar valor em apenas uma, estou revisando até onde é viável, e talvez separá-las de novo.
            3. Talvez juntar as funções de ConstruçãoPixSemCRC e crc16, já que uma complementa a outra, assim reduzindo uma função. -  descartada -
            4. Retirado a função do tamanhoCampo26, e implementado direto na função main, já que era usado apenas uma vez e a lógica é simples.
            5. Separada a função de validadorNomeValor para duas funções diferentes, validadorNome e validadorValor para que cada função tenha apenas um único propósito, ficando mais coerente.
            6. O campo de cidade, campo 60, foi mantido fixo após o comentário do Andre em dizer que não era necessário o usuário colocar a cidade, então foi mantido fixo com minha cidade.
            7. Retirado todas as variáveis de tamanho, já que não são mais necessárias, e implementado o tamanho direto na construção do código PIX, assim como sugerido pelo Andre.
            8. Construido as funções de validação de CPF, CNPJ, Telefone e Aleatória, separando cada uma em sua própria função, assim como sugerido pelo Andre, dessa forma ainda mantive a função de validacaoDeChaves, que agora chama as outras funções de validação, passando a chave e o tipo de chave como parâmetros.
    
        */

        function construcaoPixSemCRC($chave, $valor, $nome) {


            $copiaeColaSemCRC = "00020126" .(str_pad(strlen($chave)+22, 2, '0', STR_PAD_LEFT)). "0014BR.GOV.BCB.PIX01" . (str_pad(strlen($chave), 2, '0', STR_PAD_LEFT)) . "{$chave}52040000530398654" .(str_pad(strlen($valor), 2, '0', STR_PAD_LEFT)). "{$valor}5802BR59" .(str_pad(strlen($nome), 2, '0', STR_PAD_LEFT)). "{$nome}6009ARAUCARIA62070503***6304";

            return $copiaeColaSemCRC;

            /* Aqui apenas monta o código do pix sem o CRC para a função de CRC, recebendo todos os outros parâmetros necessários, como nome e o seu tamanho, chave e o seu tamanho, o tamanho do campo 26, o valor e o seu tamanho e o nome e seu tamanho, e o resto segue fixo.
            Quis manter da mesma forma, não queria acabar complicando ainda mais o código sem necessidade e o deixar confuso. */

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

         /* Monta o valor de CRC para hexadecimal e retorna $hex para encaixar na construção do código pix completo. 
         Trouxe está função para cima apenas por organização visual. */

      }

        function construcaoPix($chave, $valor, $nome, $hex) {

            $copiaeCola = "00020126" .(str_pad(strlen($chave)+22, 2, '0', STR_PAD_LEFT)). "0014BR.GOV.BCB.PIX01" . (str_pad(strlen($chave), 2, '0', STR_PAD_LEFT)) . "{$chave}52040000530398654" .(str_pad(strlen($valor), 2, '0', STR_PAD_LEFT)). "{$valor}5802BR59" .(str_pad(strlen($nome), 2, '0', STR_PAD_LEFT)). "{$nome}6009ARAUCARIA62070503***6304{$hex}";

            return $copiaeCola;

            /* Aqui apenas junta todos os paramêtros que o construcaoPixSemCRC coleta e repassa com o código CRC que a função crc16 retorna como $hex, assim devolvendo o código pix copia e cola completo e funcional. 
            Irei deixar as funções de copia e cola e crc separadas para caso de algum erro no copia e cola */

        }

        function validarTipoChave($tipo_chave) {

                $array = ['cpf', 'cnpj', 'email', 'telefone', 'aleatoria'];

                
                  if (in_array($tipo_chave, $array)) {
                         
                        return true;
                    }

                    echo "Tipo de chave PIX inválido: $tipo_chave\n";
                    exit("Encerrando o programa.\n");

                    //usando a função exit para encerrar o programa caso o usuário digite um tipo de chave inválido, fazendo o código encerrar.

            /* Essa função válida o tipo da chave pix que o usuário escolheu, é montado um array que contém os tipos corretos de chave PIX, e então usado um if para verificar se o que o usuário passou está dentro deste array. 
            Decidi manter essa função separada justamente por ela fazer uma validação diferente das outras. */
                    
        } 

        function validarCPF($chave){

            $chave = preg_replace('/[^0-9]/is', '', $chave);

            //retira tudo que não é de 0 a 9, deixando a chave apenas com números.

            if(strlen($chave) != 11 || preg_match('/(\d)\1{10}/', $chave)){

                return false;

            }

            //verifica se o cpf digitado tem mais ou menos do que 11 ou se é uma sequência de mesmo número.

            for ($tamanho = 9; $tamanho <11; $tamanho++){

                for($digito = 0, $contador = 0; $contador < $tamanho; $contador++){

                    $digito += $chave[$contador] * (($tamanho + 1) - $contador);

                }

                $digito = ((10 * $digito) % 11) % 10;
                if($chave[$contador] != $digito) {

                    return false;

                }

            }

            return true;

            /* Abre dois laços for, um ajusta o tamanho do cpf, já que iremos verficar primeiro os 9 primeiros digitos, e depois aumenta para 10, já que iremos verificar agora os 10 primeiros digitos, o segundo for faz todo o calculo para o digito verificador, no caso ele multiplica os valores de 10 até 2, descendo um a cada caracter, os soma e depois divide por 11 e pega o resto, se for menor que dois ele vir 0, se for igual ou maior vira 11 - o resto da dvisão, depois repete mas começando do 11. */

            /* usado como referência o código de Rafael Neri (<script src="https://gist.github.com/rafael-neri/ab3e58803a08cb4def059fce4e3c0e40.js"></script>) */

        }

        function validarCNPJ($chave){

            $chave = preg_replace('/[^0-9]/is', '', $chave);

            //retira tudo que não é de 0 a 9, deixando a chave apenas com números.

            if(strlen($chave) != 14 || preg_match('/(\d)\1{13}/', $chave)){

                return false;

            }

            //verifica se a chave tem mais ou menos do que 14 digitos ou se é uma sequência de mesmo número.

            for($tamanho = 12; $tamanho < 14; $tamanho++){

                if($tamanho == 12){

                    $digito = 5;

                } else{

                    $digito = 6;

                }

                //primeiro for verifica o tamanho para decidir o digito verificador.

                for($contador = 0, $soma=0; $contador < $tamanho; $contador++){

                    $soma += $chave[$contador] * $digito;
                    if($digito == 2){

                        $digito = 9;

                    }else{
                        
                        $digito--;

                    }

                }

                /* segundo for faz o calculo do digito verificador, jogando o resultado da soma e multiplicação na $soma e então verifica o digito, se for igual a dois, o digito vira nove, se for diferente de 2, ele se decrementa. */

                $digito = $soma % 11;
                if($chave[$tamanho] != ($digito < 2 ? 0 : 11 - $digito)){

                    return false;

                }

                /* aqui o digito recebe o resto da divisão da soma por 11, e então verifica se a chave digitada tem o mesmo digito verificador, ela usa um operador ternário (?) para fazer um if resumido, se o digito for menor que 2, o resultado é 0, se não é 11 - o resto. */

            }

            return true;
            //retorna verdadeiro caso tudo ocorra certo.

            /* usado como referência o código de guisehn (<script src="https://gist.github.com/guisehn/3276302.js"></script>) */

        }

        function ajustarTelefone($chave){

            if(validarCPF($chave) || validarCNPJ($chave)){

                echo "Chave PIX inválida: Telefone não pode ser CPF ou CNPJ.\n";
                exit("Encerrando o programa.\n");

                //Novamente usando a função exit para encerrar o programa caso o usuário digite uma chave inválida, fazendo o código encerrar.

            }

            if (!str_starts_with($chave, '+55')){

                    $chave = '+55' . $chave;

            }
            return $chave;

            //verifica se o telefone começa com +55 e se não, coloca o +55.

        }

        function validarTelefone($chave){

            if(preg_match("/^\+[0-9]{13}$/", $chave)){

                $array = ['11', '12', '13', '14', '15', '16', '17', '18', '19', '21', '22', '24', '27', '28', '31', '32', '33', '34', '35', '36', '37', '38', '41', '42', '43', '44', '45', '46', '47', '48', '49', '51', '53', '54', '55', '62', '63', '64', '65', '66', '67', '68', '69', '71', '73', '74', '75', '77', '79', '81', '82', '83', '84', '85', '86', '87', '88', '89', '92', '96', '97', '98', '99'];
    
                $ddd = substr($chave, 3, 2);
    
                if (in_array($ddd, $array)){
    
                    return true;
    
                }
                return false;


            }
            return false;

            /* verifica se o telefone começa com + e tem 14 digitos, verifica se não é um cpf e depois verifica se não é um cnpj. */ 


            //verifica se o ddd está correto dentro dos ddds do Brasil.


        }

        function validarAleatoria($chave){

            if(preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $chave)){

                return true;

            }

            return false;

            /* verifica o formato da chave aleatoria, contendo caracteres alfa numericos, com a primera parte tem 8 caracteres seguido de um hifen, depois 4 caracteres e um hifen, novamente 4 caracteres e um hifen, novamente e no final 12 caracteres, dessa forma também já impõe que precisa de exatamente 36 caracteres. */

        }

        function validacaoDeChaves($chave, $tipo_chave) {

            $validadores = ['cpf' => 
            function($chave){ return validarCPF($chave);
            } , 'cnpj' => 
            function($chave){ return validarCNPJ($chave);
            } , 'email' => 
            function($chave){ return filter_var($chave, FILTER_VALIDATE_EMAIL);
            } , 'telefone' => 
            function($chave){ return validarTelefone($chave);
            } , 'aleatoria' => 
            function($chave){ return validarAleatoria($chave);
            }];

            if(array_key_exists($tipo_chave, $validadores)){

                if($validadores[$tipo_chave]($chave)) {
                    return true;
                } else {

                   echo "Chave PIX inválida: $tipo_chave, $chave\n"; 
                   exit("Encerrando o programa.\n");

                   //Novamente usando a função exit para encerrar o programa caso o usuário digite uma chave inválida, fazendo o código encerrar.

                }

              }  

            /* Função usada para validar se a chave passada foi digitada corretamente dependendo do seu tipo, com algumas validações simples, com as validações passadas em um array associativo e depois feito um if para ver se a chave digitada pelo usuário está nos padrões do array. 
            Trocada a função de validarChavePix para a nova validacaoDeChaves, retirado muito dos if's e implementados em um array associativo, assim como o Andre explicou na Daily no dia 17/06/2025. 
            Mudança no dia 23/06/25: agora essa função chama outras funções para fazer a validação da chave, correspondendo seu tipo. */
        
         }

         function validarNome($nome){

            $validadorNome = preg_match("/^[A-Z\s]+$/", $nome) && strlen($nome) <= 25 && !empty($nome);

            if (!$validadorNome) {

                echo "Nome inválido: $nome\n";
                exit("Encerrando o programa.\n");

                //Novamente usando a função exit para encerrar o programa caso o usuário digite um nome inválido, fazendo o código encerrar.

            }

            return true;

            /* Função recriada para validar apenas o nome, separado de valor. 
            Verifica se o usuário digitou algo diferente de letras, se contém mais de 25 caractéres e se está vazio. */

         }

         function validarValor($valor){

            $validadorValor = is_numeric($valor) && $valor > 0 && strlen($valor) <= 13;

            if(!$validadorValor) {

                echo "Valor inválido: $valor\n";
                exit("Encerrando o programa.\n");

                //Novamente usando a função exit para encerrar o programa caso o usuário digite um valor inválido, fazendo o código encerrar.

            }

            return true;

            /* Função recriada para validar apenas o valor, separado do nome. 
            Verifica se o usuário digitou apenas números, ou números com ponto para os centavos, se o valor está acima de zero e se contém mais de 13 digitos. */

         }
    
        function main(){

            echo "\t|--> Gerador de PIX copia e cola <--|\n";
            echo "Digite os dados abaixo para gerar o código PIX:\n";

            echo "Digite o nome do recebedor (máximo 25 caracteres, se for muito longo, use abreviação):\n";
            $nome = trim(fgets(STDIN));
            $nome = strtoupper($nome);
            $nome = str_replace(' ', '', $nome);
            validarNome($nome);

            echo "Digite o valor a ser transferido (máximo 13 dígitos):\n";
            $valor = trim(fgets(STDIN));
            $valor = str_replace(' ','', $valor);
            validarValor($valor);

            echo "Digite o tipo de chave PIX (cpf, cnpj, email, telefone, aleatoria):\n";
            $tipo_chave = trim(fgets(STDIN));
            $tipo_chave = str_replace(' ', '', $tipo_chave);
            $tipo_chave = strtolower($tipo_chave);
            validarTipoChave($tipo_chave);

            echo "Digite a chave PIX:\n";
            $chave = trim(fgets(STDIN));
            $chave = str_replace(' ', '', $chave);
            if ($tipo_chave == 'telefone'){

                $chave = ajustarTelefone($chave);

            }
            validacaoDeChaves($chave, $tipo_chave);

            $copiaeColaSemCRC = construcaoPixSemCRC($chave, $valor, $nome);

            $hex = crc16($copiaeColaSemCRC);

            $copiaeCola = construcaoPix($chave, $valor, $nome, $hex);

            echo "Código PIX gerado com sucesso: \n$copiaeCola\n";

            /* Apenas fiz uns espaçamentos entre algumas linhas de código para deixar visualmente mais agradável, retirada a função tamanhoCampo26 e implementada a lógica direto na função main por ser utilizada apenas uma vez no código e ser uma lógica simples, adicionado alguns detalhes quando aberto no cmd, mas pouca coisa.
            As variaveis de tamanho foram complementamente removidas após o Andre me dar sua opiniao sobre o codigo e ter me mostrado que não era necessario variaveis para isso. */

        }
        
        main();


    ?>