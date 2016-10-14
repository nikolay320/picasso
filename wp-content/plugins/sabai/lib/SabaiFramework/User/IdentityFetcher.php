<?php
abstract class SabaiFramework_User_IdentityFetcher
{
    protected $_idField = 'id';
    protected $_usernameField = 'username';
    protected $_nameField = 'name';
    protected $_emailField = 'email';
    protected $_urlField = 'url';
    protected $_timestampField = 'created';
    private $_identities = array();

    public function clear()
    {
        $this->_identities = array();
    }

    /**
     * Loads user identity objects by user ids
     *
     * @param array $userIds ids of users to load
     */
    protected function _load(array $userIds)
    {
        // Check if requested identities are already oaded
        $user_ids = array_diff($userIds, array_keys($this->_identities));
        // Only load if there are any not loaded yet
        if ($user_ids) {
            $identities = $this->_doFetchByIds($user_ids);
            $this->_identities = $identities + $this->_identities;
            if ($userids_not_found = array_diff($user_ids, array_keys($identities))) {
                foreach ($userids_not_found as $uid) {
                    $this->_identities[$uid] = $this->getAnonymous();
                }
            }
        }
    }

    /**
     * Fetches a user identity object by user id
     *
     * @param string $userId
     * @return SabaiFramework_User_Identity
     */
    public function fetchById($userId)
    {
        $this->_load(array($userId));
        return $this->_identities[$userId];
    }

    /**
     * Fetches user identity objects by user ids
     *
     * @param array $userIds
     * @return array array of SabaiFramework_User_Identity objects indexed by user id
     */
    public function fetchByIds(array $userIds)
    {
        $this->_load($userIds);
        return array_intersect_key($this->_identities, array_combine($userIds, $userIds));
    }

    /**
     * Fetches user identity object by user name
     *
     * @param string $userName
     * @return SabaiFramework_User_Identity
     */
    public function fetchByUsername($userName)
    {
        if (!$identity = $this->_doFetchByUsername($userName)) {
            return $this->getAnonymous();
        }

        $this->_identities[$identity->id] = $identity;

        return $identity;
    }

    /**
     * Fetches user identity object by email address
     *
     * @param string $email
     * @return SabaiFramework_User_Identity
     */
    public function fetchByEmail($email)
    {
        if (!$identity = $this->_doFetchByEmail($email)) {
            return $this->getAnonymous();
        }

        $this->_identities[$identity->id] = $identity;

        return $identity;
    }

    /**
     * Paginate user identity objects
     *
     * @param int $perpage
     * @param string $sort
     * @param string $order
     * @return SabaiFramework_User_IdentityPaginator
     */
    public function paginate($perpage = 20, $sort = 'id', $order = 'ASC', $key = 0)
    {
        return new SabaiFramework_User_IdentityPaginator($this, $perpage, $sort, $order, $key);
    }

     /**
     * Fetches user identity objects
     *
     * @return ArrayObject
     * @param int $limit
     * @param int $offset
     * @param string $sort
     * @param string $order
     */
    public function fetch($limit = 0, $offset = 0, $sort = null, $order = null)
    {
        $order = in_array(@$order, array('ASC', 'DESC')) ? $order : 'ASC';
        switch (@$sort) {
            case 'name':
                $sort = $this->_nameField;
                break;
            case 'username':
                $sort = $this->_usernameField;
                break;
            case 'email':
                $sort = $this->_emailField;
                break;
            case 'url':
                $sort = $this->_urlField;
                break;
            case 'timestamp':
                $sort = $this->_timestampField;
                break;
            default:
                $sort = $this->_idField;
                break;
        }
        $identities = $this->_doFetch(intval($limit), intval($offset), $sort, $order);

        return new ArrayObject($identities);
    }

    /**
     * Searches user identity objects
     *
     * @return ArrayObject
     * @param string $term
     * @param int $limit
     * @param int $offset
     * @param string $sort
     * @param string $order
     */
    public function search($term, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        $identities = $this->_doSearch(
            $term,
            intval($limit),
            intval($offset),
            $this->_getFieldName($sort),
            $order === 'DESC' ? 'DESC' : 'ASC'
        );

        return new ArrayObject($identities);
    }
    
    /**
     * Searches user identity objects by name
     *
     * @return ArrayObject
     * @param string $term
     * @param int $limit
     * @param int $offset
     * @param string $sort
     * @param string $order
     */
    public function searchByName($term, $limit = 0, $offset = 0, $sort = null, $order = null)
    {
        $identities = $this->_doSearchByName(
            $term,
            intval($limit),
            intval($offset),
            $this->_getFieldName($sort),
            $order === 'DESC' ? 'DESC' : 'ASC'
        );

        return new ArrayObject($identities);
    }
    
    protected function _getFieldName($field)
    {
        switch ($field) {
            case 'name':
                return $this->_nameField;
            case 'username':
                return $this->_usernameField;
            case 'email':
                return $this->_emailField;
            case 'url':
                return $this->_urlField;
            case 'timestamp':
                return $this->_timestampField;
            default:
                return $this->_idField;
        }
    }

    /**
     * Fetches user identity objects
     *
     * @return array
     * @param int $limit
     * @param int $offset
     * @param string $sort
     * @param string $order
     */
    abstract protected function _doFetch($limit, $offset, $sort, $order);

    /**
     * Searches user identity objects
     *
     * @return array
     * @param string $term
     * @param int $limit
     * @param int $offset
     * @param string $sort
     * @param string $order
     */
    abstract protected function _doSearch($term, $limit, $offset, $sort, $order);
    
    /**
     * Searches user identity objects by name
     *
     * @return array
     * @param string $term
     * @param int $limit
     * @param int $offset
     * @param string $sort
     * @param string $order
     */
    abstract protected function _doSearchByName($term, $limit, $offset, $sort, $order);

    /**
     * Counts user identities
     *
     * @return int
     */
    abstract public function count();

    /**
     * Fetches user identity objects by user ids
     *
     * @abstract
     * @param array $userIds
     * @return array array of SabaiFramework_User_Identity objects indexed by user id
     */
    abstract protected function _doFetchByIds(array $userIds);

    /**
     * Fetches a user identity object by user name
     *
     * @param string $userName
     * @return mixed SabaiFramework_User_Identity if user exists, false otherwise
     */
    abstract protected function _doFetchByUsername($userName);

    /**
     * Fetches a user identity object by email address
     *
     * @param string $email
     * @return mixed SabaiFramework_User_Identity if user exists, false otherwise
     */
    abstract protected function _doFetchByEmail($email);

    /**
     * Creates an anonymous user identity object
     *
     * @return mixed SabaiFramework_User_AnonymousIdentity
     */
    abstract public function getAnonymous();
}