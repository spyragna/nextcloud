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

namespace OCA\ContactsInteraction\Db;

use OCP\AppFramework\Db\Entity;
use OCP\Contacts\Events\ContactInteractedWithEvent;

/**
 * @method void setUid(string $uid)
 * @method string|null getUid()
 * @method void setEmail(string $email)
 * @method string|null getEmail()
 * @method void setFederatedCloudId(string $federatedCloudId)
 * @method string getFederatedCloudId()
 */
class RecentContact extends Entity {

	protected $uid;

	protected $email;

	protected $federatedCloudId;

	protected $createdAt;

	public function __construct() {
		$this->addType('uid', 'string');
		$this->addType('email', 'string');
		$this->addType('federatedCloudId', 'string');
		$this->addType('createdAt', 'int');
	}

	public static function fromEvent(ContactInteractedWithEvent $event): self {
		$contact = new self();
		if ($event->getUid() !== null) {
			$contact->setUid($event->getUid());
		}
		if ($event->getEmail() !== null) {
			$contact->setEmail($event->getEmail());
		}
		if ($event->getFederatedCloudId() !== null) {
			$contact->setFederatedCloudId($event->getFederatedCloudId());
		}
		return $contact;
	}

}
