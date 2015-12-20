<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Type,
	W2M\Import\Data;

class WpObjectImporter implements ObjectImporterInterface {

	/**
	 * @var TranslationConnectorInterface
	 */
	private $translation_connector;

	/**
	 * @var Data\IdMapperInterface
	 */
	private $id_mapper;

	/**
	 * Todo: specify this
	 */
	private $ancestor_resolver;

	/**
	 * @param TranslationConnectorInterface $translation_connector
	 * @param Data\IdMapperInterface $id_mapper
	 * @param $ancestor_resolver (Not specified yet)
	 */
	public function __construct(
		TranslationConnectorInterface $translation_connector,
		Data\IdMapperInterface $id_mapper,
		$ancestor_resolver = NULL
	) {

		$this->translation_connector = $translation_connector;
		$this->id_mapper             = $id_mapper;
		$this->ancestor_resolver     = $ancestor_resolver;
	}

	/**
	 * @param Type\ImportTermInterface $term
	 * @return bool|\WP_Error
	 */
	public function import_term( Type\ImportTermInterface $term ) {

		// TODO: Implement import_term() method.

		// 1. Insert Term via wp_insert_term()
		// 2. set the new term_id(!) via $term->id( $new_term_id );
		// 3. connect translations $this->translation_connector->link_term( $new_term, $term );

		/**
		 * Todo: resolve ancestor relation
		 * Here we don't know and should not depend on whether the parent was already
		 * imported or not.
		 *
		 * $ansestor_resolver->resolve_term( $new_term, $term );
		 */
	}

	/**
	 * @param Type\ImportPostInterface $post
	 * @return bool|\WP_Error
	 */
	public function import_post( Type\ImportPostInterface $post ) {

		// 1. Insert Term via wp_insert_term()
		// 2. set the new term_id(!) via $term->id( $new_term_id );
		// 3. connect translations $this->translation_connector->link_term( $new_post, $post );

		/**
		 * Todo: resolve ancestor relation
		 * Here we don't know and should not depend on whether the parent was already
		 * imported or not.
		 *
		 * $ansestor_resolver->resolve_term( $new_post, $post );
		 */
	}
}