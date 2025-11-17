<?php

// Define que a resposta será em formato JSON
header('Content-Type: application/json');

// --- 1. Definição do Mapa de Deusas ---
$mapaDeusas = [
    'A' => 'Afrodite',
    'B' => 'Atena',
    'C' => 'Deméter',
    'D' => 'Ártemis',
    'E' => 'Perséfone',
    'F' => 'Hera',
    'G' => 'Héstia'
];

// --- 2. Receber os dados do JS ---
// file_get_contents('php://input') lê os dados brutos (JSON) enviados pelo fetch
$jsonPayload = file_get_contents('php://input');
$data = json_decode($jsonPayload, true);

// Verifica se os dados chegaram
if (empty($data['respostas']) || !is_array($data['respostas'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhuma resposta recebida.']);
    exit;
}

$respostasLetras = $data['respostas']; // Ex: ['A', 'C', 'A', 'F', 'G', 'A', 'B']

// --- 3. Contagem e Lógica Principal ---
// Conta quantas vezes cada letra apareceu
$contagemLetras = array_count_values($respostasLetras); 
// Resultado ex: ['A' => 3, 'C' => 1, 'F' => 1, 'G' => 1, 'B' => 1]

// Mapeia a contagem de letras para deusas
$contagemDeusas = [];
foreach ($contagemLetras as $letra => $count) {
    if (isset($mapaDeusas[$letra])) {
        $nomeDeusa = $mapaDeusas[$letra];
        $contagemDeusas[$nomeDeusa] = $count;
    }
}
// Resultado ex: ['Afrodite' => 3, 'Deméter' => 1, 'Hera' => 1, 'Héstia' => 1, 'Atena' => 1]


// --- 4. Encontrar Dominante e Secundária (Lógica Corrigida) ---
// Ordena o array pela contagem, do maior para o menor
arsort($contagemDeusas);

// Pega as chaves (nomes das deusas) em ordem
$chavesOrdenadas = array_keys($contagemDeusas);

// O primeiro item é sempre o dominante
$dominante = $chavesOrdenadas[0];

// Se houver um segundo item, ele é o secundário.
// Se não (isset($chavesOrdenadas[1]) == false), definimos a secundária como a chave "Pura".
$secundaria = $chavesOrdenadas[1] ?? "Pura"; 

// --- 5. Buscar o Texto ---

// Verifica se o arquivo textos.json existe
if (!file_exists('textos.json')) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro: Arquivo de textos não encontrado.']);
    exit;
}

// Lê o conteúdo do arquivo JSON
$jsonTextos = file_get_contents('textos.json');
// Decodifica o JSON para um array PHP
$textos = json_decode($jsonTextos, true);

// Mensagem de fallback caso a combinação não seja encontrada no JSON
$textoResultado = "Erro: Combinação de texto não encontrada para ($dominante / $secundaria). Verifique o textos.json."; 

// Busca o texto no array decodificado
if (isset($textos[$dominante][$secundaria])) {
    $textoResultado = $textos[$dominante][$secundaria];
}

// --- 6. Devolver o Resultado para o JS ---
echo json_encode([
    'sucesso' => true,
    'dominante' => $dominante,
    'secundaria' => $secundaria, // Retorna "Pura" ou o nome da deusa
    'texto' => $textoResultado
]);

?>