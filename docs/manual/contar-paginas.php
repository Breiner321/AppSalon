<?php

/**
 * Cuenta las páginas de un PDF buscando los objetos "/Type /Page" (sin contar "/Pages").
 * Uso: php docs/manual/contar-paginas.php docs/manual/Manual-de-usuario-AppSalon.pdf
 * Sale con código 1 si el manual pasa de 5 páginas (límite de la entrega).
 */
$archivo = $argv[1] ?? __DIR__.'/Manual-de-usuario-AppSalon.pdf';
$paginas = preg_match_all('#/Type\s*/Page(?!s)#', (string) file_get_contents($archivo));

echo basename($archivo).": {$paginas} páginas".PHP_EOL;
exit($paginas > 5 ? 1 : 0);
