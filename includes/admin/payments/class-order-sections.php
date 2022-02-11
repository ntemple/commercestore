<?php
/**
 * Order Sections Class.
 *
 * @package     CS
 * @subpackage  Admin
 * @copyright   Copyright (c) 2018, CommerceStore, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace CS\Admin;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Class for creating a vertically tabbed UI for reports.
 *
 * @since 3.0
 */
class Order_Sections extends Sections {

	/**
	 * Output the contents
	 *
	 * @since 3.0
	 */
	public function display() {
		$use_js = ! empty( $this->use_js )
			? ' use-js'
			: '';
		$role   = $this->use_js ? 'tablist' : 'menu';
		?>

		<div class="cs-sections-wrap cs-order-sections-wrapper">
			<div class="cs-vertical-sections meta-box <?php echo $use_js; ?>">
				<ul class="section-nav" role="<?php echo esc_attr( $role ); ?>">
					<?php echo $this->get_all_section_links(); ?>
				</ul>

				<div class="section-wrap">
					<?php echo $this->get_all_section_contents(); ?>
				</div>
				<br class="clear">
			</div>
		</div>

		<?php
	}
}
