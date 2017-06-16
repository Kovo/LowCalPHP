<?php
declare(strict_types=1);
namespace LowCal\Module;
use LowCal\Helper\Codes;
use LowCal\Module\Db\Couchbase;
use LowCal\Module\Db\Mysqli;
use LowCal\Module\Db\Server;

/** 
 * Class Db
 * @package LowCal\Module
 */
class Db extends Module
{
	/**
	 * @var array
	 */
	protected $_servers = array();

	/**
	 * @var null|string
	 */
	protected $_active_server_id = null;

	/**
	 * @param string $identifier
	 * @param int $type
	 * @param string $user
	 * @param string $password
	 * @param string $name
	 * @param string $host
	 * @param int $port
	 * @param bool $assign_active
	 * @return Db
	 * @throws \Exception
	 */
	public function addServer(string $identifier, int $type, string $user = '', string $password = '', string $name = '', string $host = 'localhost', int $port = 3306, bool $auto_connect = false, bool $assign_active = true): Db
	{
		if(empty($identifier))
		{
			throw new \Exception('Identifier cannot be empty', Codes::DB_IDENTIFIER_MISSING);
		}

		$this->_servers[$identifier] = new Server($this->_Base);

		$this->_servers[$identifier]->setIdentifier($identifier);
		$this->_servers[$identifier]->setType($type);
		$this->_servers[$identifier]->setUser($user);
		$this->_servers[$identifier]->setPassword($password);
		$this->_servers[$identifier]->setName($name);
		$this->_servers[$identifier]->setHost($host);
		$this->_servers[$identifier]->setPort($port);

		$this->_servers[$identifier]->init();

		if($assign_active)
		{
			$this->_active_server_id = $identifier;
		}

		if($auto_connect)
		{
			$this->_servers[$identifier]->connect();
		}

		return $this;
	}

	/**
	 * @param string $identifier
	 * @return Db
	 * @throws \Exception
	 */
	public function removeServer(string $identifier = ''): Db
	{
		if(empty($identifier) && !empty($this->_servers))
		{
			/**
			 * @var $server Server
			 */
			foreach($this->_servers as $id => $server)
			{
				$server->disconnect();
				$this->_servers[$id] = null;
				unset($this->_servers[$id]);
			}
		}
		elseif(isset($this->_servers[$identifier]))
		{
			$this->_servers[$identifier]->disconnect();
			$this->_servers[$identifier] = null;
			unset($this->_servers[$identifier]);
		}
		else
		{
			throw new \Exception('Identifier not found in server registry.', Codes::DB_IDENTIFIER_MISSING);
		}

		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getActiveServerIdentifier(): ?string
	{
		return $this->_active_server_id;
	}

	/**
	 * @param string $identifier
	 * @return Db
	 * @throws \Exception
	 */
	public function setActiveServerIdentifier(string $identifier): Db
	{
		if(empty($identifier))
		{
			throw new \Exception('Identifier cannot be empty', Codes::DB_IDENTIFIER_MISSING);
		}

		$this->_active_server_id = $identifier;

		return $this;
	}

	/**
	 * @param string $identifier
	 * @return Server
	 * @throws \Exception
	 */
	public function server(string $identifier = ''): Server
	{
		if(empty($identifier))
		{
			return $this->_servers[$this->_active_server_id];
		}
		elseif(isset($this->_servers[$identifier]))
		{
			return $this->_servers[$identifier];
		}
		else
		{
			throw new \Exception('Server with provided Identifier not found.', Codes::DB_IDENTIFIER_MISSING);
		}
	}

	/**
	 * @param string $identifier
	 * @return Couchbase|Mysqli|null
	 */
	public function interact(string $identifier = '')
	{
		return $this->server($identifier)->getInteractionObject();
	}

	public function select()
	{

	}

	public function insert()
	{

	}

	public function update()
	{

	}

	public function delete()
	{

	}

	public function query()
	{

	}
}