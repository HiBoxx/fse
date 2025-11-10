<?php
/**
 * Template HTML pour la génération du PDF d'adhésion FSETUD
 * Design complet avec toutes les informations sur une page A4
 *
 * @var array $data
 */

defined( 'ABSPATH' ) || exit;

$get_value = static function( $key, $default = '' ) use ( $data ) {
	return ! empty( $data[ $key ] ) ? esc_html( $data[ $key ] ) : $default;
};

$formatted_address = trim( sprintf( '%s, %s %s', $data['adresse'] ?? '', $data['code_postal'] ?? '', $data['ville'] ?? '' ) );
$formatted_company_address = trim( sprintf( '%s, %s %s', $data['entreprise_adresse'] ?? '', $data['entreprise_code_postal'] ?? '', $data['entreprise_ville'] ?? '' ) );
$formatted_bank_address = trim( sprintf( '%s, %s %s', $data['banque_adresse'] ?? '', $data['banque_code_postal'] ?? '', $data['banque_ville'] ?? '' ) );
$submitted_on = ! empty( $data['date_soumission'] ) ? mysql2date( 'd/m/Y', $data['date_soumission'] ) : date_i18n( 'd/m/Y' );

// Prélèvement date
$prelevement_date = '';
if ( ! empty( $data['prelevement_mois'] ) && ! empty( $data['prelevement_annee'] ) ) {
	$mois_names = array(
		'01' => 'Janvier', '02' => 'Février', '03' => 'Mars', '04' => 'Avril',
		'05' => 'Mai', '06' => 'Juin', '07' => 'Juillet', '08' => 'Août',
		'09' => 'Septembre', '10' => 'Octobre', '11' => 'Novembre', '12' => 'Décembre'
	);
	$mois_name = $mois_names[ $data['prelevement_mois'] ] ?? '';
	$prelevement_date = '05 ' . $mois_name . ' ' . $data['prelevement_annee'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Adhésion FSETUD</title>
	<style>
		@page {
			size: A4;
			margin: 8mm 10mm;
		}
		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
		}
		body {
			font-family: "DejaVu Sans", Arial, sans-serif;
			font-size: 8pt;
			color: #1f2937;
			line-height: 1.3;
		}

		/* Header */
		.header {
			text-align: center;
			padding: 8px 0 10px;
			margin-bottom: 8px;
			border-bottom: 3px solid #e30613;
			background: linear-gradient(135deg, #fff 0%, #f9fafb 100%);
		}
		.header h1 {
			font-size: 16pt;
			font-weight: 700;
			color: #e30613;
			text-transform: uppercase;
			letter-spacing: 1px;
			margin-bottom: 3px;
		}
		.header .subtitle {
			font-size: 7.5pt;
			color: #6b7280;
			margin-bottom: 5px;
		}
		.header .contact {
			font-size: 7pt;
			color: #9ca3af;
		}
		.badge {
			display: inline-block;
			padding: 3px 10px;
			background: #e30613;
			color: white;
			border-radius: 12px;
			font-size: 7pt;
			font-weight: 600;
			margin-top: 5px;
			letter-spacing: 0.5px;
		}

		/* Sections */
		.section {
			margin-bottom: 6px;
			page-break-inside: avoid;
		}
		.section-title {
			font-size: 9pt;
			font-weight: 700;
			color: white;
			background: #e30613;
			padding: 4px 8px;
			margin-bottom: 4px;
			text-transform: uppercase;
			letter-spacing: 0.8px;
		}
		.section-content {
			border: 1px solid #e5e7eb;
			border-radius: 4px;
			padding: 6px 8px;
			background: #f9fafb;
		}

		/* Grid Layout */
		.grid {
			display: table;
			width: 100%;
			border-collapse: collapse;
		}
		.grid-row {
			display: table-row;
		}
		.grid-cell {
			display: table-cell;
			padding: 3px 4px;
			vertical-align: top;
		}
		.grid-cell.label {
			width: 28%;
			font-size: 6.8pt;
			font-weight: 600;
			color: #6b7280;
			text-transform: uppercase;
			letter-spacing: 0.3px;
		}
		.grid-cell.value {
			width: 72%;
			font-size: 7.8pt;
			font-weight: 500;
			color: #111827;
			padding: 3px 6px;
			background: white;
			border: 1px solid #d1d5db;
			border-radius: 3px;
		}

		/* Two columns layout */
		.two-cols {
			display: table;
			width: 100%;
		}
		.col {
			display: table-cell;
			width: 50%;
			padding: 0 3px;
			vertical-align: top;
		}
		.col:first-child {
			padding-left: 0;
		}
		.col:last-child {
			padding-right: 0;
		}

		/* Three columns */
		.three-cols {
			display: table;
			width: 100%;
		}
		.three-cols .col {
			width: 33.33%;
		}

		/* Field inline */
		.field-inline {
			display: table;
			width: 100%;
			margin-bottom: 3px;
		}
		.field-inline .label {
			display: table-cell;
			width: 35%;
			font-size: 6.8pt;
			font-weight: 600;
			color: #6b7280;
			text-transform: uppercase;
			padding: 3px 4px 3px 0;
		}
		.field-inline .value {
			display: table-cell;
			font-size: 7.8pt;
			color: #111827;
			padding: 3px 6px;
			background: white;
			border: 1px solid #d1d5db;
			border-radius: 3px;
		}

		/* Signature */
		.signature-img {
			max-width: 100%;
			max-height: 40px;
			border: 1px solid #d1d5db;
			border-radius: 3px;
			padding: 2px;
			background: white;
		}

		/* Footer */
		.footer {
			margin-top: 8px;
			padding-top: 5px;
			border-top: 1px solid #e5e7eb;
			text-align: center;
			font-size: 6.5pt;
			color: #9ca3af;
		}

		/* Compact spacing */
		.compact {
			margin-bottom: 4px;
		}
	</style>
</head>
<body>
	<!-- Header -->
	<div class="header">
		<h1>ADHÉSION FSETUD</h1>
		<div class="subtitle">Fédération des Sociétés d'Études et du Développement – CGT</div>
		<div class="contact">Case 421 – 263 rue de Paris – 93514 Montreuil Cedex – Tél : 01 55 82 89 41</div>
		<span class="badge">SOUMIS LE <?php echo strtoupper( $submitted_on ); ?></span>
	</div>

	<!-- SECTION 1: Informations Personnelles -->
	<div class="section compact">
		<div class="section-title">1. INFORMATIONS PERSONNELLES</div>
		<div class="section-content">
			<div class="two-cols">
				<div class="col">
					<div class="field-inline">
						<div class="label">Nom</div>
						<div class="value"><?php echo $get_value( 'nom', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Prénom</div>
						<div class="value"><?php echo $get_value( 'prenom', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Sexe</div>
						<div class="value"><?php echo $get_value( 'sexe', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Date naissance</div>
						<div class="value"><?php echo $get_value( 'date_naissance', '—' ); ?></div>
					</div>
				</div>
				<div class="col">
					<div class="field-inline">
						<div class="label">Nationalité</div>
						<div class="value"><?php echo $get_value( 'nationalite', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Téléphone</div>
						<div class="value"><?php echo $get_value( 'tel', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Email</div>
						<div class="value"><?php echo $get_value( 'email', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Adresse</div>
						<div class="value"><?php echo ! empty( $formatted_address ) ? esc_html( $formatted_address ) : '—'; ?></div>
					</div>
				</div>
			</div>
			<div class="two-cols" style="margin-top: 3px;">
				<div class="col">
					<div class="field-inline">
						<div class="label">Statut</div>
						<div class="value"><?php echo $get_value( 'statut', '—' ); ?></div>
					</div>
				</div>
				<div class="col">
					<div class="field-inline">
						<div class="label">Catégorie</div>
						<div class="value"><?php echo $get_value( 'categorie', '—' ); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- SECTION 2: Informations Entreprise -->
	<div class="section compact">
		<div class="section-title">2. INFORMATIONS ENTREPRISE</div>
		<div class="section-content">
			<div class="two-cols">
				<div class="col">
					<div class="field-inline">
						<div class="label">Entreprise</div>
						<div class="value"><?php echo $get_value( 'entreprise_nom', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">SIRET</div>
						<div class="value"><?php echo $get_value( 'entreprise_siret', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Groupe</div>
						<div class="value"><?php echo $get_value( 'appartient_groupe', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Secteur</div>
						<div class="value"><?php echo $get_value( 'secteur', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Code APE/NAF</div>
						<div class="value"><?php echo $get_value( 'code_ape_naf', '—' ); ?></div>
					</div>
				</div>
				<div class="col">
					<div class="field-inline">
						<div class="label">Adresse</div>
						<div class="value"><?php echo ! empty( $formatted_company_address ) ? esc_html( $formatted_company_address ) : '—'; ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Téléphone</div>
						<div class="value"><?php echo $get_value( 'entreprise_tel', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Email</div>
						<div class="value"><?php echo $get_value( 'entreprise_email', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Convention collective</div>
						<div class="value"><?php echo $get_value( 'convention_collective', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Effectif</div>
						<div class="value"><?php echo $get_value( 'effectif', '—' ); ?></div>
					</div>
				</div>
			</div>
			<div class="two-cols" style="margin-top: 3px;">
				<div class="col">
					<div class="field-inline">
						<div class="label">Union locale</div>
						<div class="value"><?php echo $get_value( 'union_locale', '—' ); ?></div>
					</div>
				</div>
				<div class="col">
					<div class="field-inline">
						<div class="label">Union départ.</div>
						<div class="value"><?php echo $get_value( 'union_departementale', '—' ); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- SECTION 3: Cotisation -->
	<div class="section compact">
		<div class="section-title">3. COTISATION ET PRÉLÈVEMENT</div>
		<div class="section-content">
			<div class="three-cols">
				<div class="col">
					<div class="field-inline">
						<div class="label">Cotisation mensuelle</div>
						<div class="value"><?php echo $get_value( 'cotisation_mensuelle', '—' ); ?> €</div>
					</div>
				</div>
				<div class="col">
					<div class="field-inline">
						<div class="label">Date prélèvement</div>
						<div class="value"><?php echo ! empty( $prelevement_date ) ? esc_html( $prelevement_date ) : '—'; ?></div>
					</div>
				</div>
				<div class="col">
					<div class="field-inline">
						<div class="label">Montant prélèvement</div>
						<div class="value"><?php echo $get_value( 'prelevement_montant', '—' ); ?> €</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- SECTION 4: Domiciliation Bancaire -->
	<div class="section compact">
		<div class="section-title">4. DOMICILIATION BANCAIRE</div>
		<div class="section-content">
			<div class="two-cols">
				<div class="col">
					<div class="field-inline">
						<div class="label">Nom de la banque</div>
						<div class="value"><?php echo $get_value( 'banque_nom', '—' ); ?></div>
					</div>
					<div class="field-inline">
						<div class="label">Adresse banque</div>
						<div class="value"><?php echo ! empty( $formatted_bank_address ) ? esc_html( $formatted_bank_address ) : '—'; ?></div>
					</div>
				</div>
				<div class="col">
					<div class="field-inline">
						<div class="label">RIB</div>
						<div class="value"><?php echo $get_value( 'rib', '—' ); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- SECTION 5: Signature -->
	<div class="section">
		<div class="section-title">5. SIGNATURE</div>
		<div class="section-content">
			<?php if ( ! empty( $data['signature'] ) ) : ?>
				<img src="<?php echo esc_url( $data['signature'] ); ?>" class="signature-img" alt="Signature">
			<?php else : ?>
				<div style="padding: 10px; text-align: center; color: #9ca3af; font-size: 7pt; border: 1px dashed #d1d5db; border-radius: 3px; background: white;">
					Signature non fournie
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Footer -->
	<div class="footer">
		<p>Document généré automatiquement le <?php echo date_i18n( 'd/m/Y à H:i' ); ?> | © <?php echo date( 'Y' ); ?> FSETUD-CGT | Tous droits réservés</p>
	</div>
</body>
</html>
