<?php
// Lint de sintaxe de todos os .php do tema (roda dentro do PHP-WASM do WordPress Playground).
// Uso: node tools/wp.mjs lint   (ou npx @wp-playground/cli php --mount <tema>:/theme ... tools/php-lint.php)
$root = '/theme';
$bad = 0; $n = 0;
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $file ) {
	if ( substr( $file->getFilename(), -4 ) !== '.php' ) { continue; }
	$n++;
	try {
		token_get_all( file_get_contents( $file->getPathname() ), TOKEN_PARSE );
	} catch ( ParseError $e ) {
		$bad++;
		echo 'ERRO ' . str_replace( $root . '/', '', $file->getPathname() ) . ':' . $e->getLine() . ' ' . $e->getMessage() . "\n";
	}
}
echo "$n arquivos, $bad com erro de sintaxe\n";
