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

namespace OCA\ContactsInteraction\Listeners;

use OCA\ContactsInteraction\Db\RecentContact;
use OCA\ContactsInteraction\Db\RecentContactMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Contacts\Events\ContactInteractedWithEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\ILogger;

class ContactInteractionListener implements IEventListener {

	/** @var RecentContactMapper */
	private $mapper;

	/** @var ITimeFactory */
	private $timeFactory;

	/** @var ILogger */
	private $logger;

	public function __construct(RecentContactMapper $mapper,
								ITimeFactory $timeFactory,
								ILogger $logger) {
		$this->mapper = $mapper;
		$this->timeFactory = $timeFactory;
		$this->logger = $logger;
	}

	public function handle(Event $event): void {
		if (!($event instanceof ContactInteractedWithEvent)) {
			return;
		}

		if ($event->getUid() === null && $event->getEmail() === null && $event->getFederatedCloudId() === null) {
			$this->logger->warning("Contact interaction event has no user identifier set");
			return;
		}

		// TODO: check if the there is an existing contact, then just copy the card and only
		//       create a new card when this data is new
		$this->mapper->insert(RecentContact::fromEvent($event, $this->timeFactory->getTime()));
	}

}
