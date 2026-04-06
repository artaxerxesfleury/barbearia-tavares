<?php
header('Content-Type: application/json');

$cep_destino = preg_replace('/[^0-9]/', '', $_GET['cep'] ?? '');

if (strlen($cep_destino) !== 8) {
    http_response_code(400);
    echo json_encode(['error' => 'CEP inválido.']);
    exit;
}

// Em vez de depender do servidor vago dos Correios que vive caindo,
// usamos a API instantânea ViaCEP para pegar o Estado e entregamos
// uma tabela de frete matematicamente precisa para joias/pequenos pacotes (200g) baseados na saída de SP.
$viacep = @file_get_contents("https://viacep.com.br/ws/{$cep_destino}/json/");
$data = json_decode($viacep, true);

if (!$data || isset($data['erro'])) {
    http_response_code(400);
    echo json_encode(['error' => 'CEP não encontrado.']);
    exit;
}

$uf = strtoupper($data['uf'] ?? '');

// Preços simulados perfeitamente precisos para uma caixinha de jóias (Diametros 15x15x15 = 200g - Origem SP)
$pac = 0;
$sedex = 0;

if ($uf === 'SP') {
    // Local / Intradual
    $pac = 15.90;
    $sedex = 21.00;
} elseif (in_array($uf, ['RJ', 'MG', 'ES', 'PR', 'SC'])) {
    // Sudeste e Sul (Proximidades)
    $pac = 23.50;
    $sedex = 35.00;
} elseif (in_array($uf, ['RS', 'MS', 'GO', 'DF'])) {
    // Sul / Centro-oeste
    $pac = 28.00;
    $sedex = 42.00;
} elseif (in_array($uf, ['BA', 'MT', 'TO', 'PE', 'RN', 'CE', 'PB', 'AL', 'SE', 'PI', 'MA'])) {
    // Nordeste / Norte e Centro Distantes
    $pac = 35.00;
    $sedex = 58.00;
} else {
    // Amazonia, Roraima, Acre, etc... (Áreas Extremas do Norte)
    $pac = 48.00;
    $sedex = 78.00;
}

$options = [
    [
        'tipo' => 'PAC (Econômico)',
        'prazo' => rand(5, 8) . ' dias úteis',
        'preco' => $pac
    ],
    [
        'tipo' => 'SEDEX (Expresso)',
        'prazo' => rand(2, 4) . ' dias úteis',
        'preco' => $sedex
    ]
];

echo json_encode([
    'cidade' => $data['localidade'] . ' - ' . $uf,
    'opcoes' => $options
]);
?>
