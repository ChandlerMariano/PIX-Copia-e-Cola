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
    
        */

        function construcaoPixSemCRC($tamanhoCampo, $tamanhoChave, $chave, $valorTamanho, $valor, $nomeTamanho, $nome) {


            $copiaeColaSemCRC = "00020126{$tamanhoCampo}0014BR.GOV.BCB.PIX01{$tamanhoChave}{$chave}52040000530398654{$valorTamanho}{$valor}5802BR59{$nomeTamanho}{$nome}6009ARAUCARIA62070503***6304";

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

        function construcaoPix($tamanhoCampo, $tamanhoChave, $chave, $valorTamanho, $valor, $nomeTamanho, $nome, $hex) {

            $copiaeCola = "00020126{$tamanhoCampo}0014BR.GOV.BCB.PIX01{$tamanhoChave}{$chave}52040000530398654{$valorTamanho}{$valor}5802BR59{$nomeTamanho}{$nome}6009ARAUCARIA62070503***6304{$hex}";

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

        function validacaoDeChaves($chave, $tipo_chave) {

            $validadores = ['cpf' => 
            function($chave){ return is_numeric($chave) && strlen($chave) == 11;
            } , 'cnpj' => 
            function($chave){ return is_numeric($chave) && strlen($chave) == 14;
            } , 'email' => 
            function($chave){ return filter_var($chave, FILTER_VALIDATE_EMAIL);
            } , 'telefone' => 
            function($chave){ return preg_match("/^\+[0-9]{12,13}$/", $chave) && (strlen($chave) == 13 || strlen($chave) == 14);
            } , 'aleatoria' => 
            function($chave){ return strlen($chave) == 36;
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
            Trocada a função de validarChavePix para a nova validacaoDeChaves, retirado muito dos if's e implementados em um array associativo, assim como o Andre explicou na Daily no dia 17/06/2025. */
        
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
            $nomeTamanho = strlen($nome);
            $nomeTamanho = str_pad($nomeTamanho, 2, '0', STR_PAD_LEFT);

            echo "Digite o valor a ser transferido (máximo 13 dígitos):\n";
            $valor = trim(fgets(STDIN));
            $valor = str_replace(' ','', $valor);
            validarValor($valor);
            $valorTamanho = strlen($valor);
            $valorTamanho = str_pad($valorTamanho, 2, '0', STR_PAD_LEFT);

            echo "Digite o tipo de chave PIX (cpf, cnpj, email, telefone, aleatoria):\n";
            $tipo_chave = trim(fgets(STDIN));
            $tipo_chave = str_replace(' ', '', $tipo_chave);
            $tipo_chave = strtolower($tipo_chave);
            validarTipoChave($tipo_chave);

            echo "Digite a chave PIX:\n";
            $chave = trim(fgets(STDIN));
            if($tipo_chave == 'telefone'){

                if (!str_starts_with($chave, '+55')){

                    $chave = '+55' . $chave;

                }

            }
            $chave = str_replace(' ', '', $chave);
            validacaoDeChaves($chave, $tipo_chave);

            $tamanhoChave = strlen($chave);
            $tamanhoChave = str_pad($tamanhoChave, 2, '0', STR_PAD_LEFT);

            $tamanhoCampo = $tamanhoChave + 22;
            $tamanhoCampo = str_pad($tamanhoCampo, 2, '0', STR_PAD_LEFT);

            $copiaeColaSemCRC = construcaoPixSemCRC($tamanhoCampo, $tamanhoChave, $chave, $valorTamanho, $valor, $nomeTamanho, $nome);

            $hex = crc16($copiaeColaSemCRC);

            $copiaeCola = construcaoPix($tamanhoCampo, $tamanhoChave, $chave, $valorTamanho, $valor, $nomeTamanho, $nome, $hex);

            echo "Código PIX gerado com sucesso: \n$copiaeCola\n";

            /* Apenas fiz uns espaçamentos entre algumas linhas de código para deixar visualmente mais agradável, retirada a função tamanhoCampo26 e implementada a lógica direto na função main por ser utilizada apenas uma vez no código e ser uma lógica simples, adicionado alguns detalhes quando aberto no cmd, mas pouca coisa. */

        }
        
        main();


    ?>
