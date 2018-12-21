<?php

/**
 * Class Gov_Schedules_Shortcodes
 *
 */
class Gov_Schedules_Shortcodes
{

	public function __construct()
	{
		add_shortcode('gs-agenda', array($this, 'gs_agenda_shortcode'));
	}

	public function gs_agenda_shortcode($atts)
	{
		$atts = shortcode_atts(array(
			'event-cats' => ''
		), $atts);

		$event_cats = array_map( 'trim', explode(',', $atts['event-cats'] ) );
		$initial_cat_pick = $event_cats[0];

		ob_start(); ?>

		<div id="agenda" class="gs-agenda-container">
			<div class="agenda-cats row">
				<?php for ($i = 0; $i < count($event_cats); $i++) :
					$tax = get_term_by('slug', $event_cats[$i], 'event-category'); ?>

					<div class="col">
						<h2 class="section-title mb-5 text-center"><a href="#" <?php echo $i === 0 ? 'class="active"' : ''; ?> data-event-cat="<?php echo $tax->slug; ?>"><?php echo $tax->name; ?></a></h2>
					</div>

				<?php endfor; ?>
			</div>
			<div class="daypicker-wrapper">
				<ul class="daypicker">
					<?php
					//setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
					//date_default_timezone_set('America/Sao_Paulo');

					for($i = 3; $i >= 1; $i--) {
						$date = date_create();
						date_sub($date, date_interval_create_from_date_string( $i . ' days')); ?>

						<li>
							<a href="#" data-day="<?php echo date_format($date, 'Y-m-d'); ?>" title="<?php echo strftime('%d/%m/%Y', strtotime( date_format($date, 'Y-m-d') ) ); ?>">
								<span><?php echo date_format($date, 'd'); ?></span>
								<small><?php echo date_i18n('D', strtotime( date_format($date, 'Y-m-d') ) ); ?></small>
							</a>
						</li>

						<?php
					}

					for($i = 0; $i <= 3; $i++) {
						$date = date_create();
						date_add($date, date_interval_create_from_date_string($i . ' days')); ?>

						<li <?php echo $i === 0 ? 'class="selected"' : ''; ?>>
							<a href="#" data-day="<?php echo date_format($date, 'Y-m-d'); ?>" title="<?php echo strftime('%d/%m/%Y', strtotime( date_format($date, 'Y-m-d') ) ); ?>">
								<span><?php echo date_format($date, 'd'); ?></span>
								<small><?php echo date_i18n('D', strtotime( date_format($date, 'Y-m-d') ) ); ?></small>
							</a>
						</li>

						<?php
					} ?>
				</ul>
			</div>
			<div class="monthpicker-wrapper text-center">
				<a href="#" class="monthpicker"><span class="month-name text-uppercase">NOV</span> 2018</a>
				<input type="text" id="datepicker" class="sr-only">
			</div>
			<div class="events row">

				<?php
				$args = array(
					'post_type' => 'event',
					'tax_query' => array(
						array (
							'taxonomy' => 'event-category',
							'field' => 'slug',
							'terms' => $initial_cat_pick,
						)
					),
					'meta_query'     => array(
						'relation' => 'OR',
						array(
							'key'     => 'dados_do_evento_data-de-incio',
							'value'   => date( 'Y-m-d' ),
							'compare' => '=',
							'type'    => 'DATE'
						),
						array(
							'relation' => 'AND',
							array(
								'key'     => 'dados_do_evento_data-de-incio',
								'value'   => date( 'Y-m-d' ),
								'compare' => '<=',
								'type'    => 'DATE'
							),
							array(
								'key'     => 'dados_do_evento_data-de-fim',
								'value'   => date( 'Y-m-d' ),
								'compare' => '>=',
								'type'    => 'DATE'
							),
							array(
								'key'     => 'dados_do_evento_data-de-fim',
								'value'   => '',
								'compare' => '!='
							)
						)
					)
				);

				$query = new WP_Query($args);

				if( $query->have_posts() ):
					while ($query->have_posts()) : $query->the_post();

						$locaction = get_post_meta( get_the_ID(), 'dados_do_evento_location', true );
						$date = get_post_meta( get_the_ID(), 'dados_do_evento_data-de-incio', true );
						$raw_date = explode(' ', $date ); ?>

						<div class="col-md-4 ml">
							<div class="event-item">
								<?php the_title( '<h2>', '</h2>' ); ?>

								<div class="info">
									<span class="time icon icon-clock"><?php echo $raw_date[1]; ?></span>
									<span class="location icon icon-location"><?php echo $locaction; ?></span>
									<a href="#">Adicionar ao meu calendário</a>
								</div>
							</div>
						</div>

					<?php

					endwhile; wp_reset_query();
				else: ?>

					<div class="event-item empty">
						<span class="location">Sem eventos nesta data.</span>
					</div>

				<?php endif;
				?>
			</div>
			<div class="agenda-footer text-center">
				<a href="<?php echo home_url('/agenda/'); ?>" class="btn text-uppercase mt-5">Agenda completa</a>
			</div>
		</div>

		<?php return ob_get_clean();
	}


}
