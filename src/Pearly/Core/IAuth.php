<?php
/**
 * Pearly 1.0
 *
 * @author Jacob Alberty <jacob.alberty@gmail.com>
 */
namespace Pearly\Core;

/**
 * Authorization Interface.
 *
 * This interface is used to handle permissions of views and conrollers.
 */
interface IAuth extends IBase
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
    public function getPerms();
}
