<?php

use WHMCS\View\Menu\Item as MenuItem;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Remove caracteres que não são números
 */
function limparDocumento($documento)
{
    return preg_replace('/\D/', '', $documento);
}

/**
 * Valida CPF
 */
function validarCPF($cpf)
{
    $cpf = limparDocumento($cpf);

    if (strlen($cpf) != 11) {
        return false;
    }

    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        $d = 0;

        for ($c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }

        $d = ((10 * $d) % 11) % 10;

        if ($cpf[$c] != $d) {
            return false;
        }
    }

    return true;
}

/**
 * Valida CNPJ
 */
function validarCNPJ($cnpj)
{
    $cnpj = limparDocumento($cnpj);

    if (strlen($cnpj) != 14) {
        return false;
    }

    if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return false;
    }

    $peso1 = [5,4,3,2,9,8,7,6,5,4,3,2];
    $peso2 = [6,5,4,3,2,9,8,7,6,5,4,3,2];

    $soma = 0;

    for ($i = 0; $i < 12; $i++) {
        $soma += $cnpj[$i] * $peso1[$i];
    }

    $resto = $soma % 11;
    $digito1 = ($resto < 2) ? 0 : 11 - $resto;

    if ($cnpj[12] != $digito1) {
        return false;
    }

    $soma = 0;

    for ($i = 0; $i < 13; $i++) {
        $soma += $cnpj[$i] * $peso2[$i];
    }

    $resto = $soma % 11;
    $digito2 = ($resto < 2) ? 0 : 11 - $resto;

    if ($cnpj[13] != $digito2) {
        return false;
    }

    return true;
}

add_hook('ClientDetailsValidation', 1, function($vars) {

    /**
     * IMPORTANTE
     * Altere o índice abaixo caso o CPF/CNPJ
     * não seja o primeiro campo personalizado.
     */
    $campo = $vars['customfields'][1] ?? '';

    if (empty($campo)) {
        return [
            "Informe um CPF ou CNPJ."
        ];
    }

    $documento = limparDocumento($campo);

    if (strlen($documento) == 11) {

        if (!validarCPF($documento)) {
            return [
                "CPF inválido."
            ];
        }

    } elseif (strlen($documento) == 14) {

        if (!validarCNPJ($documento)) {
            return [
                "CNPJ inválido."
            ];
        }

    } else {

        return [
            "Informe um CPF ou CNPJ válido."
        ];

    }

});