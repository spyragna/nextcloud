<?php
/**
 * @copyright 2017 Georg Ehrke <oc.list@georgehrke.com>
 *
 * @author Georg Ehrke <oc.list@georgehrke.com>
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
 *
 */
namespace OCA\DAV\Migration;

use Doctrine\DBAL\Types\Type;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version1005Date20180530124431 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param \Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 * @since 13.0.0
	 */
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$types = ['resources', 'rooms'];
		foreach($types as $type) {
			if (!$schema->hasTable('calendar_' . $type . '_cache')) {
				$table = $schema->createTable('calendar_' . $type . '_cache');

				$table->addColumn('id', Type::BIGINT, [
					'autoincrement' => true,
					'notnull' => true,
					'length' => 11,
					'unsigned' => true,
				]);
				$table->addColumn('backend_id', Type::STRING, [
					'notnull' => false,
					'length' => 64,
				]);
				$table->addColumn('resource_id', Type::STRING, [
					'notnull' => false,
					'length' => 64,
				]);
				$table->addColumn('email', Type::STRING, [
					'notnull' => false,
					'length' => 255,
				]);
				$table->addColumn('displayname', Type::STRING, [
					'notnull' => false,
					'length' => 255,
				]);
				$table->addColumn('group_restrictions', Type::STRING, [
					'notnull' => false,
					'length' => 4000,
				]);

				$table->setPrimaryKey(['id'], 'calendar_' . $type . '_cache_id_idx');
				$table->addIndex(['backend_id', 'resource_id'], 'calendar_' . $type . '_cache_backendresource_idx');
				$table->addIndex(['email'], 'calendar_' . $type . '_cache_email_idx');
				$table->addIndex(['displayname'], 'calendar_' . $type . '_cache_displayname_idx');
			}
		}

		return $schema;
	}
}
