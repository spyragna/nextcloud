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
use OCA\ContactsInteraction\AppInfo\Application;
use OCA\DAV\CardDAV\Integration\ExternalAddressBook;
use OCP\IL10N;
use Sabre\DAV\PropPatch;
use Sabre\DAVACL\ACLTrait;
use Sabre\DAVACL\IACL;

class AddressBook extends ExternalAddressBook implements IACL {

	use ACLTrait;

	/** @var Store */
	private $store;

	/** @var IL10N */
	private $l10n;

	public function __construct(Store $store,
								IL10N $l10n) {
		parent::__construct(Application::APP_ID, 'recent');

		$this->store = $store;
		$this->l10n = $l10n;
	}

	/**
	 * @inheritDoc
	 */
	public function delete(): void {
		throw new Exception("This addressbook is immutable");
	}

	/**
	 * @inheritDoc
	 */
	function createFile($name, $data = null) {
		throw new Exception("This addressbook is immutable");
	}

	/**
	 * @inheritDoc
	 */
	public function getChild($name) {
		// TODO: Implement getChild() method.
	}

	/**
	 * @inheritDoc
	 */
	public function getChildren(): array {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function childExists($name) {
		// TODO: Implement childExists() method.
	}

	/**
	 * @inheritDoc
	 */
	public function getLastModified() {
		// TODO: Implement getLastModified() method.
	}

	/**
	 * @inheritDoc
	 */
	public function propPatch(PropPatch $propPatch) {
		throw new Exception("This addressbook is immutable");
	}

	/**
	 * @inheritDoc
	 */
	public function getProperties($properties) {
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getACL() {
		return [
			[
				'privilege' => '{DAV:}read',
				'principal' => $this->getOwner(),
				'protected' => true,
			],
			[
				'privilege' => '{DAV:}read',
				'principal' => $this->getOwner() . '/calendar-proxy-read',
				'protected' => true,
			],
		];
	}

}
