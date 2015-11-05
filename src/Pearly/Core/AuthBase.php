<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Core;

/**
 * Authorization abstract class.
 *
 * This class extends base and has the child class implement getPerms();
 */
abstract class AuthBase extends Base implements IAuth
{
    /**
     * Get permissions function.
     *
     * This function returns the permissions that the current user has at the present time.
     * The format of the array is 'name' = (bool) where name is the name of a controller,
     * view or one of the defaults and the bool value gives a simple way of specifying
     * whether access is allowed. The default names can be 'default', 'controllerdefault'
     * or 'viewdefault' to specify permission when no specific permission is given.
     * As this function is called for every request it must remain lightweight.
     *
     * @return array an array containing the permissions in the specified format.
     */
    abstract public function getPerms();

    /**
     * Get data function.
     *
     * This function returns data about the currently authorized user.
     * It is up to the authentication module to decide what is or is not supported.
     * If the data does not exist this function MUST return null.
     *
     * @param string $key Key to search for.
     *
     * @return mixed null if no data found or the matching data.
     */
    abstract public function getData($key);
}
