<?php
/**
 * Header do tema.
 *
 * @package casadocheesecake
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body id="topo" <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#conteudo"><?php esc_html_e( 'Pular para o conteúdo', 'casadocheesecake' ); ?></a>
<?php get_template_part( 'template-parts/site-header' ); ?>
<main id="conteudo">
