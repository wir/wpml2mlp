<?php # -*- coding: utf-8 -*-

namespace W2M\Cli;

use
	W2M\Controller,
	W2M\Import,
	W2M\System,
	WP_CLI,
	WP_CLI_Command,
	WP_Error,
	Monolog;

/**
 * Manages migration from WPML to MultilingualPress
 *
 * @package W2M\Cli
 */
class WpCliW2MCommand extends \WP_CLI_Command {

	/**
	 * Imports a single extended WXR file to a blog. Use the --url parameter to specify the home URL
	 * of the site you want to import the language to.
	 *
	 * Example: If you exported all spanish content (es_ES) to ~/my-site-es_ES.xml and want to import it
	 * to my-site.es use `wp w2m import ~/my-site-es_ES.xml --url=my-site.es
	 *
	 * ## Options
	 *
	 * <FILE>
	 * : Path to the WXR file
	 *
	 * @synopsis <FILE> --url=<url> [--no_confirm] [--verbose]
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function import( Array $args, Array $assoc_args ) {

		if ( ! isset( $args[ 0 ] ) ) {
			$this->handle_error( new WP_Error( 'parameter', 'Missing parameter <FILE>' ) );
			exit;
		}

		$import_file = realpath( $args[ 0 ] );
		if ( ! is_file( $import_file ) || ! is_readable( $import_file ) ) {
			$this->handle_error( new WP_Error( 'parameter', 'Import file does not exist or is not readable.' ) );
			exit;
		}

		$env = new System\ImportEnvironment;
		if ( ! $env->is_multisite() ) {
			$this->handle_error( new WP_Error( 'environment', 'This is not a multisite setup' ) );
			exit;
		}
		if ( ! $env->mlp_is_active() ) {
			$this->handle_error( new WP_Error( 'environment', 'MultilingualPress is not active' ) );
			exit;
		}

		$blog_id = get_current_blog_id();
		$locale = $env->mlp_blog_language( $blog_id );

		if ( ! isset( $assoc_args[ 'no_confirm' ] ) ) {
			$ays = readline( "Start import to blog {$blog_id}[{$locale}]? [yes]" );
			if ( 'yes' !== strtolower( $ays ) ) {
				WP_CLI::line( 'Aborting' );
				exit;
			}
		}

		$log_dir = WP_CONTENT_DIR.'/log';
		if ( !is_dir( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
		}

		//Todo: use DI-Container ASAP

		$logger         = new Monolog\Logger( 'w2m-import' );
		$log_setup      = new System\LoggerSetup( $logger, $log_dir, 'w2m-import.log' );
		$log_controller = new Controller\TmpLogController( $logger );

		$log_setup->setup_handler();
		$log_controller->register_log_recorder();
		$log_controller->register_wp_cli_recorder();
		if ( isset( $assoc_args[ 'verbose' ] ) ) {
			$log_controller->register_wp_cli_handler();
		}

		$import_id_mapper  = new Import\Data\ImportListeningTypeIdMapper;
		$ancestor_mapper   = new Import\Data\ImportListeningMTAncestorList;
		$mapper_controller = new Controller\DataIdObserverProvider(
			$import_id_mapper,
			$ancestor_mapper
		);
		$mapper_controller->register_id_observer();

		/**
		 * Users
		 */
		$user_iterator = new Import\Iterator\UserIterator(
			new Import\Iterator\SimpleXmlItemWrapper(
				new Import\Iterator\XmlNodeIterator(
					$import_file,
					'wp:author'
				)
			),
			new Import\Service\WpUserParser
		);
		$user_processor = new Import\Service\UserProcessor(
			$user_iterator,
			new Import\Service\WpUserImporter( $import_id_mapper )
		);

		/**
		 * Terms
		 */
		$term_iterator = new Import\Iterator\TermIterator(
			new Import\Iterator\SimpleXmlItemWrapper(
				new Import\Iterator\XmlNodeIterator(
					$import_file,
					'wp:category'
				)
			),
			new Import\Service\WpTermParser
		);
		$term_processor = new Import\Service\TermProcessor(
			$term_iterator,
			new Import\Service\WpTermImporter( $import_id_mapper )
		);

		/**
		 * Posts
		 */
		$post_iterator = new Import\Iterator\PostIterator(
			new Import\Iterator\SimpleXmlItemWrapper(
				new Import\Iterator\XmlNodeIterator(
					$import_file,
					'item'
				)
			),
			new Import\Service\WpPostParser
		);
		$post_processor = new Import\Service\PostProcessor(
			$post_iterator,
			new Import\Service\WpPostImporter( $import_id_mapper )
		);

		/**
		 * Comments
		 */
		$comment_iterator = new Import\Iterator\CommentIterator(
			new Import\Iterator\SimpleXmlItemWrapper(
				new Import\Iterator\XmlNodeIterator(
					$import_file,
					'wp:comment'
				)
			),
			new Import\Service\WpCommentParser
		);
		$comment_processor = new Import\Service\CommentProcessor(
			$comment_iterator,
			new Import\Service\WpCommentImporter( $import_id_mapper )
		);

		$importer = new Import\Module\ElementImporter(
			[
				$user_processor,
				$term_processor,
				$post_processor,
				$comment_processor
			]
		);
		$importer->process_elements();

	}

	/**
	 * Todo: Will be used later
	 */
	private function die_on_missing_dependency() {

		$msg = 'A $GLOBAL variable is not in a state it supposed to be.'; //surprise
		exit(
		$this->handle_error(
			new WP_Error(
				1,
				$msg
			)
		)
		);
	}

	/**
	 * @param WP_Error $error
	 *
	 * @return int
	 */
	private function handle_error( WP_Error $error ) {

		foreach ( $error->get_error_messages() as $msg ) {
			WP_CLI::error( $msg, FALSE );
		}

		return 1;
	}

	/**
	 * Todo: Will be used later
	 *
	 * @param WP_Error $error
	 *
	 * @return int
	 */
	private function handle_warning( WP_Error $error ) {

		foreach ( $error->get_error_messages() as $msg ) {
			WP_CLI::warning( $msg );
		}

		return 1;
	}

	/**
	 * Todo: Will be used later
	 *
	 * @param $msg
	 *
	 * @return int
	 */
	private function handle_success( $msg ) {

		WP_CLI::success( $msg );

		return 0;
	}
}