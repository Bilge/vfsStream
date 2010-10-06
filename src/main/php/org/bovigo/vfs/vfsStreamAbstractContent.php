<?php
/**
 * Base stream contents container.
 *
 * @package  bovigo_vfs
 * @version  $Id$
 */
/**
 * @ignore
 */
require_once dirname(__FILE__) . '/vfsStreamContent.php';
/**
 * Base stream contents container.
 *
 * @package  bovigo_vfs
 */
abstract class vfsStreamAbstractContent implements vfsStreamContent
{
    /**
     * name of the container
     *
     * @var  string
     */
    protected $name;
    /**
     * type of the container
     *
     * @var  string
     */
    protected $type;
    /**
     * timestamp of last modification
     *
     * @var  int
     */
    protected $lastModified;
    /**
     * permissions for content
     *
     * @var  int
     */
    protected $permissions;
    /**
     * owner of the file
     *
     * @var  int
     */
    protected $user;
    /**
     * owner group of the file
     *
     * @var  int
     */
    protected $group;

    /**
     * constructor
     *
     * @param  string  $name
     * @param  int     $permissions  optional
     */
    public function __construct($name, $permissions = null)
    {
        $this->name         = $name;
        $this->lastModified = time();
        if (null === $permissions) {
            $permissions = $this->getDefaultPermissions() - vfsStream::umask();
        }

        $this->permissions  = $permissions;
        $this->user         = vfsStream::getCurrentUser();
        $this->group        = vfsStream::getCurrentGroup();
    }

    /**
     * returns default permissions for concrete implementation
     *
     * @return  int
     * @since   0.8.0
     */
    protected abstract function getDefaultPermissions();

    /**
     * returns the file name of the content
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * renames the content
     *
     * @param  string  $newName
     */
    public function rename($newName)
    {
        $this->name = $newName;
    }

    /**
     * checks whether the container can be applied to given name
     *
     * @param   string  $name
     * @return  bool
     */
    public function appliesTo($name)
    {
        if ($name === $this->name) {
            return true;
        }

        return (substr($name, 0, strlen($this->name)) === $this->name && strpos($name, '/') !== false);
    }

    /**
     * returns the type of the container
     *
     * @return  int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * alias for lastModified()
     *
     * @param   int               $filemtime
     * @return  vfsStreamContent
     * @see     lastModified()
     */
    public function setFilemtime($filemtime)
    {
        return $this->lastModified($filemtime);
    }

    /**
     * sets the last modification time of the stream content
     *
     * @param   int               $filemtime
     * @return  vfsStreamContent
     */
    public function lastModified($filemtime)
    {
        $this->lastModified = $filemtime;
        return $this;
    }

    /**
     * returns the last modification time of the stream content
     *
     * @return  int
     */
    public function filemtime()
    {
        return $this->lastModified;
    }

    /**
     * adds content to given container
     *
     * @param   vfsStreamContainer  $container
     * @return  vfsStreamContent
     */
    public function at(vfsStreamContainer $container)
    {
        $container->addChild($this);
        return $this;
    }

    /**
     * change file mode to given permissions
     *
     * @param   int               $permissions
     * @return  vfsStreamContent
     */
    public function chmod($permissions)
    {
        $this->permissions = $permissions;
        clearstatcache();
        return $this;
    }

    /**
     * returns permissions
     *
     * @return  int
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * checks whether content is readable
     *
     * @param   int   $user   id of user to check for
     * @param   int   $group  id of group to check for
     * @return  bool
     */
    public function isReadable($user, $group)
    {
        if ($this->user === $user) {
            $check = 0400;
        } elseif ($this->group === $group) {
            $check = 0040;
        } else {
            $check = 0004;
        }

        return (bool) ($this->permissions & $check);
    }

    /**
     * checks whether content is writable
     *
     * @param   int   $user   id of user to check for
     * @param   int   $group  id of group to check for
     * @return  bool
     */
    public function isWritable($user, $group)
    {
        if ($this->user === $user) {
            $check = 0200;
        } elseif ($this->group === $group) {
            $check = 0020;
        } else {
            $check = 0002;
        }

        return (bool) ($this->permissions & $check);
    }

    /**
     * checks whether content is executable
     *
     * @param   int   $user   id of user to check for
     * @param   int   $group  id of group to check for
     * @return  bool
     */
    public function isExecutable($user, $group)
    {
        if ($this->user === $user) {
            $check = 0100;
        } elseif ($this->group === $group) {
            $check = 0010;
        } else {
            $check = 0001;
        }

        return (bool) ($this->permissions & $check);
    }

    /**
     * change owner of file to given user
     *
     * @param   int               $user
     * @return  vfsStreamContent
     */
    public function chown($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * checks whether file is owned by given user
     *
     * @param   int  $user
     * @return  bool
     */
    public function isOwnedByUser($user)
    {
        return $this->user === $user;
    }

    /**
     * returns owner of file
     *
     * @return  int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * change owner group of file to given group
     *
     * @param   int               $group
     * @return  vfsStreamContent
     */
    public function chgrp($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * checks whether file is owned by group
     *
     * @param   int   $group
     * @return  bool
     */
    public function isOwnedByGroup($group)
    {
        return $this->group === $group;
    }

    /**
     * returns owner group of file
     *
     * @return  int
     */
    public function getGroup()
    {
        return $this->group;
    }
}
?>