<?php
function salvarImagemLoja(string $campo, string $pasta): ?string {
    if (empty($_FILES[$campo]['tmp_name'])) return null;
    $file    = $_FILES[$campo];
    $mime    = mime_content_type($file['tmp_name']);
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if (!isset($allowed[$mime]) || $file['size'] > 2*1024*1024) return null;
    $nome = uniqid('img_',true).'.'.$allowed[$mime];
    $dir  = FARMAVIDA_ROOT.'/uploads/'.$pasta.'/';
    if (!is_dir($dir)) mkdir($dir,0755,true);
    move_uploaded_file($file['tmp_name'], $dir.$nome);
    return $nome;
}

function gerarPayloadPix(string $chave, string $nome, string $cidade, float $valor, string $txid = ''): string {
    // Limpa e padroniza
    $nome   = mb_strtoupper(substr(preg_replace('/[^A-Za-z0-9 ]/', '', $nome), 0, 25));
    $cidade = mb_strtoupper(substr(preg_replace('/[^A-Za-z0-9 ]/', '', $cidade), 0, 15));
    $txid   = preg_replace('/[^A-Za-z0-9]/', '', $txid ?: 'FARMAVIDA'.time());
    $txid   = substr($txid, 0, 25);

    $valorStr = number_format($valor, 2, '.', '');

    // Merchant Account Information (ID 26)
    $gui     = tlv('00', 'br.gov.bcb.pix');
    $chaveTV = tlv('01', $chave);
    $mai     = tlv('26', $gui . $chaveTV);

    // Additional Data Field (ID 62) — txid
    $txidTV = tlv('05', $txid);
    $adf    = tlv('62', $txidTV);

    // Monta payload sem CRC
    $payload =
        tlv('00', '01')             . // Payload Format Indicator
        tlv('01', '12')             . // Point of Initiation Method (12 = dinâmico, 11 = estático)
        $mai                        . // Merchant Account Information
        tlv('52', '0000')           . // Merchant Category Code
        tlv('53', '986')            . // Transaction Currency (BRL)
        tlv('54', $valorStr)        . // Transaction Amount
        tlv('58', 'BR')             . // Country Code
        tlv('59', $nome)            . // Merchant Name
        tlv('60', $cidade)          . // Merchant City
        $adf                        . // Additional Data Field
        '6304';                       // CRC placeholder

    // CRC16-CCITT
    $crc = crc16($payload);
    return $payload . strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

function tlv(string $id, string $value): string {
    return $id . str_pad(strlen($value), 2, '0', STR_PAD_LEFT) . $value;
}

function crc16(string $str): int {
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($str); $i++) {
        $crc ^= ord($str[$i]) << 8;
        for ($j = 0; $j < 8; $j++) {
            if ($crc & 0x8000) { $crc = ($crc << 1) ^ 0x1021; }
            else                { $crc <<= 1; }
            $crc &= 0xFFFF;
        }
    }
    return $crc;
}
