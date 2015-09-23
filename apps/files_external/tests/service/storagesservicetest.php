<?php
/**
 * @author Vincent Petry <pvince81@owncloud.com>
 * @author Robin McCorkell <rmccorkell@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */
namespace OCA\Files_external\Tests\Service;

use \OC\Files\Filesystem;

use \OCA\Files_external\NotFoundException;
use \OCA\Files_external\Lib\StorageConfig;
use \OCA\Files_External\Lib\BackendService;

abstract class StoragesServiceTest extends \Test\TestCase {

	/**
	 * @var StoragesService
	 */
	protected $service;

	/** @var BackendService */
	protected $backendService;

	/**
	 * Data directory
	 *
	 * @var string
	 */
	protected $dataDir;

	/**
	 * Hook calls
	 *
	 * @var array
	 */
	protected static $hookCalls;

	public function setUp() {
		self::$hookCalls = array();
		$config = \OC::$server->getConfig();
		$this->dataDir = $config->getSystemValue(
			'datadirectory',
			\OC::$SERVERROOT . '/data/'
		);
		\OC_Mount_Config::$skipTest = true;

		// prepare BackendService mock
		$this->backendService =
			$this->getMockBuilder('\OCA\Files_External\Service\BackendService')
			->disableOriginalConstructor()
			->getMock();

		$authMechanisms = [
			'identifier:\Auth\Mechanism' => $this->getAuthMechMock('null', '\Auth\Mechanism'),
			'identifier:\Other\Auth\Mechanism' => $this->getAuthMechMock('null', '\Other\Auth\Mechanism'),
			'identifier:\OCA\Files_External\Lib\Auth\NullMechanism' => $this->getAuthMechMock(),
		];
		$this->backendService->method('getAuthMechanism')
			->will($this->returnCallback(function($class) use ($authMechanisms) {
				if (isset($authMechanisms[$class])) {
					return $authMechanisms[$class];
				}
				return null;
			}));
		$this->backendService->method('getAuthMechanismsByScheme')
			->will($this->returnCallback(function($schemes) use ($authMechanisms) {
				return array_filter($authMechanisms, function ($authMech) use ($schemes) {
					return in_array($authMech->getScheme(), $schemes, true);
				});
			}));
		$this->backendService->method('getAuthMechanisms')
			->will($this->returnValue($authMechanisms));

		$sftpBackend = $this->getBackendMock('\OCA\Files_External\Lib\Backend\SFTP', '\OC\Files\Storage\SFTP');
		$backends = [
			'identifier:\OCA\Files_External\Lib\Backend\SMB' => $this->getBackendMock('\OCA\Files_External\Lib\Backend\SMB', '\OC\Files\Storage\SMB'),
			'identifier:\OCA\Files_External\Lib\Backend\SFTP' => $sftpBackend,
			'identifier:sftp_alias' => $sftpBackend,
		];
		$backends['identifier:\OCA\Files_External\Lib\Backend\SFTP']->method('getLegacyAuthMechanism')
			->willReturn($authMechanisms['identifier:\Other\Auth\Mechanism']);
		$this->backendService->method('getBackend')
			->will($this->returnCallback(function($backendClass) use ($backends) {
				if (isset($backends[$backendClass])) {
					return $backends[$backendClass];
				}
				return null;
			}));
		$this->backendService->method('getBackends')
			->will($this->returnValue($backends));

		\OCP\Util::connectHook(
			Filesystem::CLASSNAME,
			Filesystem::signal_create_mount,
			get_class($this), 'createHookCallback');
		\OCP\Util::connectHook(
			Filesystem::CLASSNAME,
			Filesystem::signal_delete_mount,
			get_class($this), 'deleteHookCallback');

		$containerMock = $this->getMock('\OCP\AppFramework\IAppContainer');
		$containerMock->method('query')
			->will($this->returnCallback(function($name) {
				if ($name === 'OCA\Files_External\Service\BackendService') {
					return $this->backendService;
				}
			}));

		\OC_Mount_Config::$app = $this->getMockBuilder('\OCA\Files_External\Appinfo\Application')
			->disableOriginalConstructor()
			->getMock();
		\OC_Mount_Config::$app->method('getContainer')
			->willReturn($containerMock);
	}

	public function tearDown() {
		\OC_Mount_Config::$skipTest = false;
		self::$hookCalls = array();
	}

	protected function getBackendMock($class = '\OCA\Files_External\Lib\Backend\SMB', $storageClass = '\OC\Files\Storage\SMB') {
		$backend = $this->getMockBuilder('\OCA\Files_External\Lib\Backend\Backend')
			->disableOriginalConstructor()
			->getMock();
		$backend->method('getStorageClass')
			->willReturn($storageClass);
		$backend->method('getIdentifier')
			->willReturn('identifier:'.$class);
		return $backend;
	}

	protected function getAuthMechMock($scheme = 'null', $class = '\OCA\Files_External\Lib\Auth\NullMechanism') {
		$authMech = $this->getMockBuilder('\OCA\Files_External\Lib\Auth\AuthMechanism')
			->disableOriginalConstructor()
			->getMock();
		$authMech->method('getScheme')
			->willReturn($scheme);
		$authMech->method('getIdentifier')
			->willReturn('identifier:'.$class);

		return $authMech;
	}

	/**
	 * Creates a StorageConfig instance based on array data
	 *
	 * @param array data
	 *
	 * @return StorageConfig storage config instance
	 */
	protected function makeStorageConfig($data) {
		$storage = new StorageConfig();
		if (isset($data['id'])) {
			$storage->setId($data['id']);
		}
		$storage->setMountPoint($data['mountPoint']);
		if (!isset($data['backend'])) {
			// data providers are run before $this->backendService is initialised
			// so $data['backend'] can be specified directly
			$data['backend'] = $this->backendService->getBackend($data['backendIdentifier']);
		}
		if (!isset($data['backend'])) {
			throw new \Exception('oops, no backend');
		}
		if (!isset($data['authMechanism'])) {
			$data['authMechanism'] = $this->backendService->getAuthMechanism($data['authMechanismIdentifier']);
		}
		if (!isset($data['authMechanism'])) {
			throw new \Exception('oops, no auth mechanism');
		}
		$storage->setBackend($data['backend']);
		$storage->setAuthMechanism($data['authMechanism']);
		$storage->setBackendOptions($data['backendOptions']);
		if (isset($data['applicableUsers'])) {
			$storage->setApplicableUsers($data['applicableUsers']);
		}
		if (isset($data['applicableGroups'])) {
			$storage->setApplicableGroups($data['applicableGroups']);
		}
		if (isset($data['priority'])) {
			$storage->setPriority($data['priority']);
		}
		if (isset($data['mountOptions'])) {
			$storage->setMountOptions($data['mountOptions']);
		}
		return $storage;
	}


	/**
	 * @expectedException \OCA\Files_external\NotFoundException
	 */
	public function testNonExistingStorage() {
		$backend = $this->backendService->getBackend('identifier:\OCA\Files_External\Lib\Backend\SMB');
		$authMechanism = $this->backendService->getAuthMechanism('identifier:\Auth\Mechanism');
		$storage = new StorageConfig(255);
		$storage->setMountPoint('mountpoint');
		$storage->setBackend($backend);
		$storage->setAuthMechanism($authMechanism);
		$this->service->updateStorage($storage);
	}

	public function testDeleteStorage() {
		$backend = $this->backendService->getBackend('identifier:\OCA\Files_External\Lib\Backend\SMB');
		$authMechanism = $this->backendService->getAuthMechanism('identifier:\Auth\Mechanism');
		$storage = new StorageConfig(255);
		$storage->setMountPoint('mountpoint');
		$storage->setBackend($backend);
		$storage->setAuthMechanism($authMechanism);
		$storage->setBackendOptions(['password' => 'testPassword']);

		$newStorage = $this->service->addStorage($storage);
		$this->assertEquals(1, $newStorage->getId());

		$newStorage = $this->service->removeStorage(1);

		$caught = false;
		try {
			$this->service->getStorage(1);
		} catch (NotFoundException $e) {
			$caught = true;
		}

		$this->assertTrue($caught);
	}

	/**
	 * @expectedException \OCA\Files_external\NotFoundException
	 */
	public function testDeleteUnexistingStorage() {
		$this->service->removeStorage(255);
	}

	public function testCreateStorage() {
		$mountPoint = 'mount';
		$backendIdentifier = 'identifier:\OCA\Files_External\Lib\Backend\SMB';
		$authMechanismIdentifier = 'identifier:\Auth\Mechanism';
		$backendOptions = ['param' => 'foo', 'param2' => 'bar'];
		$mountOptions = ['option' => 'foobar'];
		$applicableUsers = ['user1', 'user2'];
		$applicableGroups = ['group'];
		$priority = 123;

		$backend = $this->backendService->getBackend($backendIdentifier);
		$authMechanism = $this->backendService->getAuthMechanism($authMechanismIdentifier);

		$storage = $this->service->createStorage(
			$mountPoint,
			$backendIdentifier,
			$authMechanismIdentifier,
			$backendOptions,
			$mountOptions,
			$applicableUsers,
			$applicableGroups,
			$priority
		);

		$this->assertEquals('/'.$mountPoint, $storage->getMountPoint());
		$this->assertEquals($backend, $storage->getBackend());
		$this->assertEquals($authMechanism, $storage->getAuthMechanism());
		$this->assertEquals($backendOptions, $storage->getBackendOptions());
		$this->assertEquals($mountOptions, $storage->getMountOptions());
		$this->assertEquals($applicableUsers, $storage->getApplicableUsers());
		$this->assertEquals($applicableGroups, $storage->getApplicableGroups());
		$this->assertEquals($priority, $storage->getPriority());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCreateStorageInvalidClass() {
		$this->service->createStorage(
			'mount',
			'identifier:\OC\Not\A\Backend',
			'identifier:\Auth\Mechanism',
			[]
		);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 */
	public function testCreateStorageInvalidAuthMechanismClass() {
		$this->service->createStorage(
			'mount',
			'identifier:\OCA\Files_External\Lib\Backend\SMB',
			'identifier:\Not\An\Auth\Mechanism',
			[]
		);
	}

	public function testGetStoragesBackendNotVisible() {
		$backend = $this->backendService->getBackend('identifier:\OCA\Files_External\Lib\Backend\SMB');
		$backend->expects($this->once())
			->method('isVisibleFor')
			->with($this->service->getVisibilityType())
			->willReturn(false);
		$authMechanism = $this->backendService->getAuthMechanism('identifier:\Auth\Mechanism');
		$authMechanism->method('isVisibleFor')
			->with($this->service->getVisibilityType())
			->willReturn(true);

		$storage = new StorageConfig(255);
		$storage->setMountPoint('mountpoint');
		$storage->setBackend($backend);
		$storage->setAuthMechanism($authMechanism);
		$storage->setBackendOptions(['password' => 'testPassword']);

		$newStorage = $this->service->addStorage($storage);

		$this->assertCount(1, $this->service->getAllStorages());
		$this->assertEmpty($this->service->getStorages());
	}

	public function testGetStoragesAuthMechanismNotVisible() {
		$backend = $this->backendService->getBackend('identifier:\OCA\Files_External\Lib\Backend\SMB');
		$backend->method('isVisibleFor')
			->with($this->service->getVisibilityType())
			->willReturn(true);
		$authMechanism = $this->backendService->getAuthMechanism('identifier:\Auth\Mechanism');
		$authMechanism->expects($this->once())
			->method('isVisibleFor')
			->with($this->service->getVisibilityType())
			->willReturn(false);

		$storage = new StorageConfig(255);
		$storage->setMountPoint('mountpoint');
		$storage->setBackend($backend);
		$storage->setAuthMechanism($authMechanism);
		$storage->setBackendOptions(['password' => 'testPassword']);

		$newStorage = $this->service->addStorage($storage);

		$this->assertCount(1, $this->service->getAllStorages());
		$this->assertEmpty($this->service->getStorages());
	}

	public static function createHookCallback($params) {
		self::$hookCalls[] = array(
			'signal' => Filesystem::signal_create_mount,
			'params' => $params
		);
	}

	public static function deleteHookCallback($params) {
		self::$hookCalls[] = array(
			'signal' => Filesystem::signal_delete_mount,
			'params' => $params
		);
	}

	/**
	 * Asserts hook call
	 *
	 * @param array $callData hook call data to check
	 * @param string $signal signal name
	 * @param string $mountPath mount path
	 * @param string $mountType mount type
	 * @param string $applicable applicable users
	 */
	protected function assertHookCall($callData, $signal, $mountPath, $mountType, $applicable) {
		$this->assertEquals($signal, $callData['signal']);
		$params = $callData['params'];
		$this->assertEquals(
			$mountPath,
			$params[Filesystem::signal_param_path]
		);
		$this->assertEquals(
			$mountType,
			$params[Filesystem::signal_param_mount_type]
		);
		$this->assertEquals(
			$applicable,
			$params[Filesystem::signal_param_users]
		);
	}
}
