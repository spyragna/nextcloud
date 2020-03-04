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

use OCA\ContactsInteraction\Db\RecentContact;
use Sabre\CardDAV\ICard;
use Sabre\DAV\Exception\NotImplemented;
use Sabre\DAVACL\ACLTrait;
use Sabre\DAVACL\IACL;
use function md5;

class Card implements ICard, IACL {

	use ACLTrait;

	/** @var RecentContact */
	private $contact;

	/** @var string */
	private $principal;

	/** @var array */
	private $acls;

	public function __construct(RecentContact $contact, string $principal, array $acls) {
		$this->contact = $contact;
		$this->principal = $principal;
		$this->acls = $acls;
	}

	/**
	 * @inheritDoc
	 */
	function getOwner(): ?string {
		$this->principal;
	}

	/**
	 * @inheritDoc
	 */
	function getACL(): array {
		return $this->acls;
	}

	/**
	 * @inheritDoc
	 */
	function setAcls(array $acls): void {
		throw new NotImplemented();
	}

	/**
	 * @inheritDoc
	 */
	function put($data): ?string {
		throw new NotImplemented();
	}

	/**
	 * @inheritDoc
	 */
	function get() {
		return 'BEGIN:VCARD
VERSION:4.0
N:Gump;Forrest;;Mr.;
FN:Forrest Gump
ORG:Bubba Gump Shrimp Co.
TITLE:Shrimp Man
PHOTO;MEDIATYPE=image/gif:http://www.example.com/dir_photos/my_photo.gif
TEL;TYPE=work,voice;VALUE=uri:tel:+1-111-555-1212
TEL;TYPE=home,voice;VALUE=uri:tel:+1-404-555-1212
ADR;TYPE=WORK;PREF=1;LABEL="100 Waters Edge\nBaytown\, LA 30314\nUnited States of America":;;100 Waters Edge;Baytown;LA;30314;United States of America
ADR;TYPE=HOME;LABEL="42 Plantation St.\nBaytown\, LA 30314\nUnited States of America":;;42 Plantation St.;Baytown;LA;30314;United States of America
EMAIL:forrestgump@example.com
REV:20080424T195243Z
x-qq:21588891
END:VCARD';
	}

	/**
	 * @inheritDoc
	 */
	function getContentType(): ?string {
		return 'text/vcard; charset=utf-8';
	}

	/**
	 * @inheritDoc
	 */
	function getETag(): ?string {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	function getSize(): int {
		throw new NotImplemented();
	}

	/**
	 * @inheritDoc
	 */
	function delete(): void {
		throw new NotImplemented();
	}

	/**
	 * @inheritDoc
	 */
	function getName(): string {
		return md5((string) $this->contact->getId());
	}

	/**
	 * @inheritDoc
	 */
	function setName($name): void {
		throw new NotImplemented();
	}

	/**
	 * @inheritDoc
	 */
	function getLastModified(): ?int {
		throw new NotImplemented();
	}
}
