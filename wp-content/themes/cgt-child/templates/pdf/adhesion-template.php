<?php
/**
 * Template HTML pour la génération du PDF d’adhésion.
 *
 * Les données du formulaire sont accessibles dans le tableau $data.
 *
 * @var array $data
 */

defined( 'ABSPATH' ) || exit;

$get_value = static function( $key, $default = '' ) use ( $data ) {
	return ! empty( $data[ $key ] ) ? esc_html( $data[ $key ] ) : $default;
};

$formatted_address = trim( sprintf( '%s %s %s', $data['adresse'] ?? '', $data['code_postal'] ?? '', $data['ville'] ?? '' ) );
$formatted_company_address = trim( sprintf( '%s %s %s', $data['entreprise_adresse'] ?? '', $data['entreprise_code_postal'] ?? '', $data['entreprise_ville'] ?? '' ) );
$submitted_on = ! empty( $data['date_soumission'] ) ? mysql2date( 'd/m/Y', $data['date_soumission'] ) : date_i18n( 'd/m/Y' );
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title><?php esc_html_e( 'Fiche d’adhésion – CGT', 'cgt' ); ?></title>
	<style>
		@page {
			size: A4;
			margin: 14mm 16mm;
		}
		* {
			box-sizing: border-box;
		}
	body {
		margin: 0;
		padding: 0;
		font-family: "DejaVu Sans", "Inter", "Helvetica Neue", Arial, sans-serif;
		font-size: 10pt;
		color: #1f2937;
		line-height: 1.45;
	}
	.wrapper {
			display: flex;
			flex-direction: column;
			min-height: 100vh;
		}
		header {
			text-align: center;
			padding-bottom: 14px;
			margin-bottom: 12px;
			border-bottom: 3px solid #d00000;
		}
		header h1 {
			margin: 0;
			font-size: 18pt;
			text-transform: uppercase;
			letter-spacing: 0.08em;
			color: #d00000;
		}
		header p {
			margin: 6px 0 0;
			font-size: 9.5pt;
			color: #4b5563;
		}
		.badge {
			display: inline-flex;
			margin-top: 8px;
			padding: 4px 9px;
			border-radius: 999px;
			background: rgba(208, 0, 0, 0.12);
			color: #d00000;
			font-weight: 600;
			font-size: 8.5pt;
			letter-spacing: 0.05em;
		}
		h2 {
			font-size: 10.5pt;
			margin: 16px 0 8px;
			text-transform: uppercase;
			letter-spacing: 0.14em;
			color: #d00000;
		}
		.section {
			border: 1px solid rgba(17, 17, 17, 0.12);
			border-radius: 10px;
			padding: 14px 16px;
			background: rgba(229, 231, 235, 0.2);
		}
		.info-table {
			width: 100%;
			border-collapse: collapse;
		}
		.info-table tr + tr {
			border-top: 6px solid transparent;
		}
		.info-table th {
			text-align: left;
			font-size: 7.6pt;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 0.05em;
			color: #5b6474;
			padding: 0 6px 2px 0;
			width: 22%;
			vertical-align: top;
		}
		.info-table td {
			padding: 5px 8px;
			border: 1px solid rgba(17, 17, 17, 0.18);
			border-radius: 6px;
			background: #ffffff;
			font-size: 9.4pt;
			font-weight: 500;
			color: #1f2937;
			vertical-align: middle;
		}
		.info-table td.value-spacer {
			border: none;
			background: transparent;
			padding: 0;
		}
		.signature {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 14px;
			margin-top: 10px;
		}
		.signature .value {
			height: 30px;
		}
		footer {
			margin-top: auto;
			text-align: center;
			font-size: 8.5pt;
			color: #475569;
			padding-top: 10px;
			border-top: 1px solid rgba(17, 17, 17, 0.1);
		}
	</style>
</head>
<body>
	<div class="wrapper">
		<header>
			<h1><?php esc_html_e( 'Fédération des Sociétés d’Études – CGT', 'cgt' ); ?></h1>
			<p><?php esc_html_e( 'Case 421 – 263 rue de Paris – 93514 Montreuil Cedex', 'cgt' ); ?><br><?php esc_html_e( 'Tél : 01 55 82 89 41', 'cgt' ); ?></p>
			<span class="badge"><?php echo esc_html( sprintf( __( 'Soumis le %s', 'cgt' ), $submitted_on ) ); ?></span>
		</header>

		<h2><?php esc_html_e( 'Informations personnelles', 'cgt' ); ?></h2>
		<div class="section">
			<table class="info-table">
				<tr>
					<th><?php esc_html_e( 'Nom', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'nom', '—' ); ?></td>
					<th><?php esc_html_e( 'Prénom', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'prenom', '—' ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Sexe', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'sexe', '—' ); ?></td>
					<th><?php esc_html_e( 'Date de naissance', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'date_naissance', '—' ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Nationalité', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'nationalite', '—' ); ?></td>
					<th><?php esc_html_e( 'Téléphone', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'tel', '—' ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Email', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'email', '—' ); ?></td>
					<th><?php esc_html_e( 'Statut', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'statut', '—' ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Catégorie', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'categorie', '—' ); ?></td>
					<th><?php esc_html_e( 'Adresse postale', 'cgt' ); ?></th>
					<td><?php echo ! empty( $formatted_address ) ? esc_html( $formatted_address ) : '—'; ?></td>
				</tr>
			</table>
		</div>

		<h2><?php esc_html_e( 'Informations professionnelles', 'cgt' ); ?></h2>
		<div class="section">
			<table class="info-table">
				<tr>
					<th><?php esc_html_e( 'Entreprise', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'entreprise_nom', '—' ); ?></td>
					<th><?php esc_html_e( 'SIRET', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'entreprise_siret', '—' ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Secteur', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'secteur', '—' ); ?></td>
					<th><?php esc_html_e( 'Téléphone entreprise', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'entreprise_tel', '—' ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Email entreprise', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'entreprise_email', '—' ); ?></td>
					<th><?php esc_html_e( 'Adresse de l’entreprise', 'cgt' ); ?></th>
					<td><?php echo ! empty( $formatted_company_address ) ? esc_html( $formatted_company_address ) : '—'; ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Union locale', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'union_locale', '—' ); ?></td>
					<th><?php esc_html_e( 'Union départementale', 'cgt' ); ?></th>
					<td><?php echo $get_value( 'union_departementale', '—' ); ?></td>
				</tr>
			</table>
		</div>

		<h2><?php esc_html_e( 'Signature', 'cgt' ); ?></h2>
		<div class="section">
			<div class="signature">
				<div class="field">
					<div class="label"><?php esc_html_e( 'Fait à', 'cgt' ); ?></div>
					<div class="value">&nbsp;</div>
				</div>
				<div class="field">
					<div class="label"><?php esc_html_e( 'Date', 'cgt' ); ?></div>
					<div class="value">&nbsp;</div>
				</div>
			</div>
		</div>

		<footer>
			<p><?php esc_html_e( 'Ce document est généré automatiquement à partir des données fournies par l’adhérent.', 'cgt' ); ?><br><?php esc_html_e( '© Fédération des Sociétés d’Études – CGT', 'cgt' ); ?></p>
		</footer>
	</div>
</body>
</html>
