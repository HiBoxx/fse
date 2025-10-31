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
		.info-rows {
			display: flex;
			flex-direction: column;
			gap: 12px;
		}
		.info-row {
			display: grid;
			grid-template-columns: 160px minmax(0, 1fr);
			align-items: baseline;
			column-gap: 10px;
		}
		.info-label {
			font-size: 7.8pt;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 0.05em;
			color: #5b6474;
		}
		.info-value {
			padding: 0;
			border: none;
			background: transparent;
			font-size: 9.6pt;
			font-weight: 500;
			color: #1f2937;
		}
		.signature-statement {
			font-size: 9.6pt;
			margin: 0 0 12px;
			color: #1f2937;
		}
		.signature-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
			gap: 14px;
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

		<?php
		$personal_rows = array(
			array(
				'label' => __( 'Nom', 'cgt' ),
				'value' => $get_value( 'nom', '—' ),
			),
			array(
				'label' => __( 'Prénom', 'cgt' ),
				'value' => $get_value( 'prenom', '—' ),
			),
			array(
				'label' => __( 'Sexe', 'cgt' ),
				'value' => $get_value( 'sexe', '—' ),
			),
			array(
				'label' => __( 'Date de naissance', 'cgt' ),
				'value' => $get_value( 'date_naissance', '—' ),
			),
			array(
				'label' => __( 'Nationalité', 'cgt' ),
				'value' => $get_value( 'nationalite', '—' ),
			),
			array(
				'label' => __( 'Téléphone', 'cgt' ),
				'value' => $get_value( 'tel', '—' ),
			),
			array(
				'label' => __( 'Email', 'cgt' ),
				'value' => $get_value( 'email', '—' ),
			),
			array(
				'label' => __( 'Statut', 'cgt' ),
				'value' => $get_value( 'statut', '—' ),
			),
			array(
				'label' => __( 'Catégorie', 'cgt' ),
				'value' => $get_value( 'categorie', '—' ),
			),
			array(
				'label' => __( 'Adresse postale', 'cgt' ),
				'value' => ! empty( $formatted_address ) ? esc_html( $formatted_address ) : '—',
			),
		);
		?>

		<h2><?php esc_html_e( 'Informations personnelles', 'cgt' ); ?></h2>
		<div class="section">
			<div class="info-rows">
				<?php foreach ( $personal_rows as $row ) : ?>
					<div class="info-row">
						<span class="info-label"><?php echo esc_html( $row['label'] ); ?> :</span>
						<span class="info-value"><?php echo esc_html( $row['value'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<?php
		$professional_rows = array(
			array(
				'label' => __( 'Entreprise', 'cgt' ),
				'value' => $get_value( 'entreprise_nom', '—' ),
			),
			array(
				'label' => __( 'SIRET', 'cgt' ),
				'value' => $get_value( 'entreprise_siret', '—' ),
			),
			array(
				'label' => __( 'Secteur', 'cgt' ),
				'value' => $get_value( 'secteur', '—' ),
			),
			array(
				'label' => __( 'Téléphone entreprise', 'cgt' ),
				'value' => $get_value( 'entreprise_tel', '—' ),
			),
			array(
				'label' => __( 'Email entreprise', 'cgt' ),
				'value' => $get_value( 'entreprise_email', '—' ),
			),
			array(
				'label' => __( 'Union locale', 'cgt' ),
				'value' => $get_value( 'union_locale', '—' ),
			),
			array(
				'label' => __( 'Union départementale', 'cgt' ),
				'value' => $get_value( 'union_departementale', '—' ),
			),
		);

		if ( ! empty( $formatted_company_address ) ) {
			$professional_rows[] = array(
				'label' => __( 'Adresse de l’entreprise', 'cgt' ),
				'value' => esc_html( $formatted_company_address ),
			);
		}
		?>

		<h2><?php esc_html_e( 'Informations professionnelles', 'cgt' ); ?></h2>
		<div class="section">
			<div class="info-rows">
				<?php foreach ( $professional_rows as $row ) : ?>
					<div class="info-row">
						<span class="info-label"><?php echo esc_html( $row['label'] ); ?> :</span>
						<span class="info-value"><?php echo esc_html( $row['value'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<?php
		$full_name_display = trim( sprintf( '%s %s', $get_value( 'nom', '' ), $get_value( 'prenom', '' ) ) );
		if ( '' === $full_name_display ) {
			$full_name_display = '—';
		}

		$signature_city = $get_value( 'ville', '—' );
		$signature_date = ! empty( $data['date_soumission'] ) ? mysql2date( 'd/m/Y', $data['date_soumission'] ) : date_i18n( 'd/m/Y' );
		?>

		<h2><?php esc_html_e( 'Signature', 'cgt' ); ?></h2>
		<div class="section">
			<p class="signature-statement">
				<?php
				printf(
					/* translators: %s: full name */
					esc_html__( 'Je soussigné(e) M./Mlle %s déclare adhérer à la Fédération des Sociétés d’Étude.', 'cgt' ),
					esc_html( $full_name_display )
				);
				?>
			</p>
			<div class="signature-grid">
				<div class="info-row">
					<span class="info-label"><?php esc_html_e( 'Fait à', 'cgt' ); ?> :</span>
					<span class="info-value"><?php echo esc_html( $signature_city ); ?></span>
				</div>
				<div class="info-row">
					<span class="info-label"><?php esc_html_e( 'Le', 'cgt' ); ?> :</span>
					<span class="info-value"><?php echo esc_html( $signature_date ); ?></span>
				</div>
			</div>
		</div>

		<footer>
			<p><?php esc_html_e( 'Ce document est généré automatiquement à partir des données fournies par l’adhérent.', 'cgt' ); ?><br><?php esc_html_e( '© Fédération des Sociétés d’Études – CGT', 'cgt' ); ?></p>
		</footer>
	</div>
</body>
</html>
