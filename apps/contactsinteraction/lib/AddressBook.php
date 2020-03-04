<?php

declare(strict_types=1);

/**
 * @copyright 2020 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2020 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\ContactsInteraction;

use Exception;
use OCP\Constants;
use OCP\IL10N;
use Sabre\CardDAV\IAddressBook;
use Sabre\DAV\IProperties;
use Sabre\DAV\PropPatch;

class AddressBook implements IAddressBook, IProperties {

	/** @var Store */
	private $store;

	/** @var IL10N */
	private $l10n;

	public function __construct(Store $store,
								IL10N $l10n) {
		$this->store = $store;
		$this->l10n = $l10n;
	}

	public function getKey() {
		return 'recent';
	}

	public function getUri(): string {
		return 'recent';
	}

	public function getDisplayName(): string {
		return $this->l10n->t('Recent contacts');
	}

	/**
	 * @param string $pattern
	 * @param array $searchProperties
	 * @param array $options
	 */
	public function search($pattern, $searchProperties, $options): array {
		// TODO: Implement search() method.
		return $this->store->search($pattern, $searchProperties);
	}

	public function createOrUpdate($properties): void {
		throw new Exception("This addressbook is immutable");
	}

	public function getPermissions(): int {
		return Constants::PERMISSION_READ;
	}

	public function delete($id): void {
		throw new Exception("This addressbook is immutable");
	}

	/**
	 * @inheritDoc
	 */
	function createFile($name, $data = null) {
		// TODO: Implement createFile() method.
	}

	/**
	 * @inheritDoc
	 */
	function createDirectory($name) {
		// TODO: Implement createDirectory() method.
	}

	/**
	 * @inheritDoc
	 */
	function getChild($name) {
		// TODO: Implement getChild() method.
	}

	/**
	 * @inheritDoc
	 */
	function getChildren() {
		// TODO: Implement getChildren() method.
	}

	/**
	 * @inheritDoc
	 */
	function childExists($name) {
		// TODO: Implement childExists() method.
	}

	/**
	 * @inheritDoc
	 */
	function getName() {
		// TODO: Implement getName() method.
	}

	/**
	 * @inheritDoc
	 */
	function setName($name) {
		// TODO: Implement setName() method.
	}

	/**
	 * @inheritDoc
	 */
	function getLastModified() {
		// TODO: Implement getLastModified() method.
	}

	/**
	 * @inheritDoc
	 */
	function propPatch(PropPatch $propPatch) {
		// TODO: Implement propPatch() method.
	}

	/**
	 * @inheritDoc
	 */
	function getProperties($properties) {
		// TODO: Implement getProperties() method.
	}
}
