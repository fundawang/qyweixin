<?php

/**
 * @file
 * Contains \Drupal\qyweixin\CorpInterface.
 */

namespace Drupal\qyweixin;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining qyweixin Corp Entity.
 *
 */
interface CorpInterface extends ConfigEntityInterface {
    /* Const for user gender */
	const USER_GENDER_MALE=1;
	const USER_GENDER_FEMALE=2;
	
    /* Const for user status */
	const USER_STATUS_ENABLED=1;
	const USER_STATUS_DISABLED=0;
	
    /* Const for user subscribing status */
	const USER_SUBSCRIBE_STATUS_SUBSCRIBED=1;
	const USER_SUBSCRIBE_STATUS_FREEZED=2;
	const USER_SUBSCRIBE_STATUS_UNSUBSCRIBED=4;

	/**
	 * Retrieve access_token to be used in other functions
	 *
	 * @return string
	 *   The access_token return by Tencent qyweixin interface
	 */
	public function getAccessToken();

	/**
	 * Wrapper of QyWeixin's user/create function.
	 *
	 * @param stdClass/UserEntityInterface $user
	 *   The user to push to qyweixin's contact book.
	 *   if $user is a stdClass, then the implementor could assume it complies user object
	 *      specification in qyweixin.
	 *   if $user is a UserEntityInterface, then the implementor should wrap it automatically.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return $this
	 */
	public function userCreate($user);

	/**
	 * Wrapper of QyWeixin's user/update function.
	 *
	 * @param stdClass/UserEntityInterface $user
	 *   The user to push to qyweixin's contact book.
	 *   if $user is a stdClass, then the implementor could assume it complies user object
	 *      specification in qyweixin.
	 *   if $user is a UserEntityInterface, then the implementor should wrap it automatically.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return $this
	 */
	public function userUpdate($user);

	/**
	 * Wrapper of QyWeixin's user/delete function.
	 *
	 * @param stdClass/UserEntityInterface/Array $user
	 *   The user to push to qyweixin's contact book.
	 *   if $user is a stdClass, then the implementor could assume it complies user object
	 *      specification in qyweixin.
	 *   if $user is a UserEntityInterface, then the implementor should wrap it automatically.
	 *   if $user is a array, then massive deletion(user/batchdeleted) might be called, and each
	 *      of the element could be plain uid, stdClass or UserEntityInterface.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return $this
	 */
	public function userDelete($user);

	/**
	 * Wrapper of QyWeixin's user/get function.
	 *
	 * @param stdClass/UserEntityInterface $user
	 *   The user to push to qyweixin's contact book.
	 *   if $user is a stdClass, then the implementor could assume it
	 *      complies user object specification in qyweixin.
	 *   if $user is a UserEntityInterface, then the implementor should wrap it automatically.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return stdClass
	 *   The user object retured by Tencent qyweixin interface.
	 */
	public function userGet($user);

	/**
	 * Wrapper of QyWeixin's user/simplelist function.
	 *
	 * @param int $departmentid
	 *   The id of department you want to fetch.
	 * @param boolean $fetch_child
	 *   1 means you want the members of sub departments should be fetched also.
	 * @param int $status
	 *   Following numbers could be used:
	 *   0: Allo
	 *   1 means you want the members of sub departments should be fetched also.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return array of stdClass
	 *   The user objects retured by Tencent qyweixin interface.
	 */
	public function userSimpleList($department = 1, $fetch_child = FALSE, $status = USER_SUBSCRIBE_STATUS_UNSUBSCRIBED);

	/**
	 * Wrapper of QyWeixin's user/list function.
	 *
	 * @param int $departmentid
	 *   The id of department you want to fetch.
	 * @param boolean $fetch_child
	 *   1 means you want the members of sub departments should be fetched also.
	 * @param int $status
	 *   Following numbers could be used:
	 *   0: Allo
	 *   1 means you want the members of sub departments should be fetched also.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return array of stdClass
	 *   The user objects retured by Tencent qyweixin interface.
	 */
	public function userList($department = 1, $fetch_child = FALSE, $status = USER_SUBSCRIBE_STATUS_UNSUBSCRIBED);
	

	/**
	 * Wrapper of QyWeixin's department/create function.
	 *
	 * @param stdClass $department
	 *   The department object you want to push qyweixin's database.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return $this
	 */
	public function departmentCreate($department);

	/**
	 * Wrapper of QyWeixin's department/update function.
	 *
	 * @param stdClass $department
	 *   The department object you want to push qyweixin's database.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return $this
	 */
	public function departmentUpdate($department);

	/**
	 * Wrapper of QyWeixin's department/delete function.
	 *
	 * @param int $departmentid
	 *   The departmentid to be removed from qyweixin's database.
	 *
	 *   Exception could be thrown if error occurs. The caller should take care of the exception.
	 *
	 * @return $this
	 */
	public function departmentDelete($departmentid);

}
?>
