<?php

/*
 * PHP-Auth
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 */

/*
 * WARNING:
 *
 * Do *not* use these files from the `tests` directory as the foundation
 * for the usage of this library in your own code. Instead, please follow
 * the `README.md` file in the root directory of this project.
 */

// enable error reporting
\error_reporting(\E_ALL);
\ini_set('display_errors', 'stdout');

// enable assertions
\ini_set('assert.active', 1);
@\ini_set('zend.assertions', 1);
\ini_set('assert.exception', 1);

// DIRECTORY_SEPARATOR adds a slash to the end of the path
define('ROOT', dirname(__FILE__) . DIRECTORY_SEPARATOR);
// set a constant that holds the project's "application" folder, like "/var/www/application".
define('APP', ROOT . 'application' . DIRECTORY_SEPARATOR);

\header('Content-type: text/html; charset=utf-8');

require __DIR__.'/vendor/autoload.php';

$dataSource = new \Rini\Core\Db\PdoDataSource('mysql'); // see "Available drivers for database systems" below
$dataSource->setHostname('localhost');
$dataSource->setPort(3306);
$dataSource->setDatabaseName('mini');
$dataSource->setCharset('utf8mb4');
$dataSource->setUsername('root');
$dataSource->setPassword('');

$db = \Rini\Core\Db\PdoDatabase::fromDataSource($dataSource);

// $db = new \PDO('mysql:dbname=php_auth;host=127.0.0.1;charset=utf8mb4', 'root', 'monkey');
// or
// $db = new \PDO('pgsql:dbname=php_auth;host=127.0.0.1;port=5432', 'postgres', 'monkey');
// or
// $db = new \PDO('sqlite:../Databases/php_auth.sqlite');

$auth = new \Rini\Core\Auth\Auth($db);

$result = \processRequestData($auth);

\showGeneralForm();
\showDebugData($auth, $result);

if ($auth->check()) {
	\showAuthenticatedUserForm($auth);
}
else {
	\showGuestUserForm();
}

function processRequestData(\Rini\Core\Auth\Auth $auth) {
	if (isset($_POST)) {
		if (isset($_POST['action'])) {
			if ($_POST['action'] === 'login') {
				if ($_POST['remember'] == 1) {
					// keep logged in for one year
					$rememberDuration = (int) (60 * 60 * 24 * 365.25);
				}
				else {
					// do not keep logged in after session ends
					$rememberDuration = null;
				}

				try {
					if (isset($_POST['email'])) {
						$auth->login($_POST['email'], $_POST['password'], $rememberDuration);
					}
					elseif (isset($_POST['username'])) {
						$auth->loginWithUsername($_POST['username'], $_POST['password'], $rememberDuration);
					}
					else {
						return 'either email address or username required';
					}

					return 'ok';
				}
				catch (\Rini\Core\Auth\InvalidEmailException $e) {
					return 'wrong email address';
				}
				catch (\Rini\Core\Auth\UnknownUsernameException $e) {
					return 'unknown username';
				}
				catch (\Rini\Core\Auth\AmbiguousUsernameException $e) {
					return 'ambiguous username';
				}
				catch (\Rini\Core\Auth\InvalidPasswordException $e) {
					return 'wrong password';
				}
				catch (\Rini\Core\Auth\EmailNotVerifiedException $e) {
					return 'email address not verified';
				}
				catch (\Rini\Core\Auth\TooManyRequestsException $e) {
					return 'too many requests';
				}
			}
			else if ($_POST['action'] === 'register') {
				try {
					if ($_POST['require_verification'] == 1) {
						$callback = function ($selector, $token) {
							echo '<pre>';
							echo 'Email confirmation';
							echo "\n";
							echo '  >  Selector';
							echo "\t\t\t\t";
							echo \htmlspecialchars($selector);
							echo "\n";
							echo '  >  Token';
							echo "\t\t\t\t";
							echo \htmlspecialchars($token);
							echo '</pre>';
						};
					}
					else {
						$callback = null;
					}

					if (!isset($_POST['require_unique_username'])) {
						$_POST['require_unique_username'] = '0';
					}

					if ($_POST['require_unique_username'] == 0) {
						return $auth->register($_POST['email'], $_POST['password'], $_POST['username'], $callback);
					}
					else {
						return $auth->registerWithUniqueUsername($_POST['email'], $_POST['password'], $_POST['username'], $callback);
					}
				}
				catch (\Rini\Core\Auth\InvalidEmailException $e) {
					return 'invalid email address';
				}
				catch (\Rini\Core\Auth\InvalidPasswordException $e) {
					return 'invalid password';
				}
				catch (\Rini\Core\Auth\UserAlreadyExistsException $e) {
					return 'email address already exists';
				}
				catch (\Rini\Core\Auth\DuplicateUsernameException $e) {
					return 'username already exists';
				}
				catch (\Rini\Core\Auth\TooManyRequestsException $e) {
					return 'too many requests';
				}
			}
			else if ($_POST['action'] === 'confirmEmail') {
				try {
					if (isset($_POST['login']) && $_POST['login'] > 0) {
						if ($_POST['login'] == 2) {
							// keep logged in for one year
							$rememberDuration = (int) (60 * 60 * 24 * 365.25);
						}
						else {
							// do not keep logged in after session ends
							$rememberDuration = null;
						}

						return $auth->confirmEmailAndSignIn($_POST['selector'], $_POST['token'], $rememberDuration);
					}
					else {
						return $auth->confirmEmail($_POST['selector'], $_POST['token']);
					}
				}
				catch (\Rini\Core\Auth\InvalidSelectorTokenPairException $e) {
					return 'invalid token';
				}
				catch (\Rini\Core\Auth\TokenExpiredException $e) {
					return 'token expired';
				}
				catch (\Rini\Core\Auth\UserAlreadyExistsException $e) {
					return 'email address already exists';
				}
				catch (\Rini\Core\Auth\TooManyRequestsException $e) {
					return 'too many requests';
				}
			}
			else if ($_POST['action'] === 'resendConfirmationForEmail') {
				try {
					$auth->resendConfirmationForEmail($_POST['email'], function ($selector, $token) {
						echo '<pre>';
						echo 'Email confirmation';
						echo "\n";
						echo '  >  Selector';
						echo "\t\t\t\t";
						echo \htmlspecialchars($selector);
						echo "\n";
						echo '  >  Token';
						echo "\t\t\t\t";
						echo \htmlspecialchars($token);
						echo '</pre>';
					});

					return 'ok';
				}
				catch (\Rini\Core\Auth\ConfirmationRequestNotFound $e) {
					return 'no request found';
				}
				catch (\Rini\Core\Auth\TooManyRequestsException $e) {
					return 'too many requests';
				}
			}
			else if ($_POST['action'] === 'resendConfirmationForUserId') {
				try {
					$auth->resendConfirmationForUserId($_POST['userId'], function ($selector, $token) {
						echo '<pre>';
						echo 'Email confirmation';
						echo "\n";
						echo '  >  Selector';
						echo "\t\t\t\t";
						echo \htmlspecialchars($selector);
						echo "\n";
						echo '  >  Token';
						echo "\t\t\t\t";
						echo \htmlspecialchars($token);
						echo '</pre>';
					});

					return 'ok';
				}
				catch (\Rini\Core\Auth\ConfirmationRequestNotFound $e) {
					return 'no request found';
				}
				catch (\Rini\Core\Auth\TooManyRequestsException $e) {
					return 'too many requests';
				}
			}
			else if ($_POST['action'] === 'forgotPassword') {
				try {
					$auth->forgotPassword($_POST['email'], function ($selector, $token) {
						echo '<pre>';
						echo 'Password reset';
						echo "\n";
						echo '  >  Selector';
						echo "\t\t\t\t";
						echo \htmlspecialchars($selector);
						echo "\n";
						echo '  >  Token';
						echo "\t\t\t\t";
						echo \htmlspecialchars($token);
						echo '</pre>';
					});

					return 'ok';
				}
				catch (\Rini\Core\Auth\InvalidEmailException $e) {
					return 'invalid email address';
				}
				catch (\Rini\Core\Auth\EmailNotVerifiedException $e) {
					return 'email address not verified';
				}
				catch (\Rini\Core\Auth\ResetDisabledException $e) {
					return 'password reset is disabled';
				}
				catch (\Rini\Core\Auth\TooManyRequestsException $e) {
					return 'too many requests';
				}
			}
			else if ($_POST['action'] === 'resetPassword') {
				try {
					if (isset($_POST['login']) && $_POST['login'] > 0) {
						if ($_POST['login'] == 2) {
							// keep logged in for one year
							$rememberDuration = (int) (60 * 60 * 24 * 365.25);
						}
						else {
							// do not keep logged in after session ends
							$rememberDuration = null;
						}

						return $auth->resetPasswordAndSignIn($_POST['selector'], $_POST['token'], $_POST['password'], $rememberDuration);
					}
					else {
						return $auth->resetPassword($_POST['selector'], $_POST['token'], $_POST['password']);
					}
				}
				catch (\Rini\Core\Auth\InvalidSelectorTokenPairException $e) {
					return 'invalid token';
				}
				catch (\Rini\Core\Auth\TokenExpiredException $e) {
					return 'token expired';
				}
				catch (\Rini\Core\Auth\ResetDisabledException $e) {
					return 'password reset is disabled';
				}
				catch (\Rini\Core\Auth\InvalidPasswordException $e) {
					return 'invalid password';
				}
				catch (\Rini\Core\Auth\TooManyRequestsException $e) {
					return 'too many requests';
				}
			}
			else if ($_POST['action'] === 'canResetPassword') {
				try {
					$auth->canResetPasswordOrThrow($_POST['selector'], $_POST['token']);

					return 'yes';
				}
				catch (\Rini\Core\Auth\InvalidSelectorTokenPairException $e) {
					return 'invalid token';
				}
				catch (\Rini\Core\Auth\TokenExpiredException $e) {
					return 'token expired';
				}
				catch (\Rini\Core\Auth\ResetDisabledException $e) {
					return 'password reset is disabled';
				}
				catch (\Rini\Core\Auth\TooManyRequestsException $e) {
					return 'too many requests';
				}
			}
			else if ($_POST['action'] === 'reconfirmPassword') {
				try {
					return $auth->reconfirmPassword($_POST['password']) ? 'correct' : 'wrong';
				}
				catch (\Rini\Core\Auth\NotLoggedInException $e) {
					return 'not logged in';
				}
				catch (\Rini\Core\Auth\TooManyRequestsException $e) {
					return 'too many requests';
				}
			}
			else if ($_POST['action'] === 'changePassword') {
				try {
					$auth->changePassword($_POST['oldPassword'], $_POST['newPassword']);

					return 'ok';
				}
				catch (\Rini\Core\Auth\NotLoggedInException $e) {
					return 'not logged in';
				}
				catch (\Rini\Core\Auth\InvalidPasswordException $e) {
					return 'invalid password(s)';
				}
				catch (\Rini\Core\Auth\TooManyRequestsException $e) {
					return 'too many requests';
				}
			}
			else if ($_POST['action'] === 'changePasswordWithoutOldPassword') {
				try {
					$auth->changePasswordWithoutOldPassword($_POST['newPassword']);

					return 'ok';
				}
				catch (\Rini\Core\Auth\NotLoggedInException $e) {
					return 'not logged in';
				}
				catch (\Rini\Core\Auth\InvalidPasswordException $e) {
					return 'invalid password';
				}
			}
			else if ($_POST['action'] === 'changeEmail') {
				try {
					$auth->changeEmail($_POST['newEmail'], function ($selector, $token) {
						echo '<pre>';
						echo 'Email confirmation';
						echo "\n";
						echo '  >  Selector';
						echo "\t\t\t\t";
						echo \htmlspecialchars($selector);
						echo "\n";
						echo '  >  Token';
						echo "\t\t\t\t";
						echo \htmlspecialchars($token);
						echo '</pre>';
					});

					return 'ok';
				}
				catch (\Rini\Core\Auth\InvalidEmailException $e) {
					return 'invalid email address';
				}
				catch (\Rini\Core\Auth\UserAlreadyExistsException $e) {
					return 'email address already exists';
				}
				catch (\Rini\Core\Auth\EmailNotVerifiedException $e) {
					return 'account not verified';
				}
				catch (\Rini\Core\Auth\NotLoggedInException $e) {
					return 'not logged in';
				}
				catch (\Rini\Core\Auth\TooManyRequestsException $e) {
					return 'too many requests';
				}
			}
			else if ($_POST['action'] === 'setPasswordResetEnabled') {
				try {
					$auth->setPasswordResetEnabled($_POST['enabled'] == 1);

					return 'ok';
				}
				catch (\Rini\Core\Auth\NotLoggedInException $e) {
					return 'not logged in';
				}
			}
			else if ($_POST['action'] === 'logOut') {
				$auth->logOut();

				return 'ok';
			}
			else if ($_POST['action'] === 'logOutEverywhereElse') {
				try {
					$auth->logOutEverywhereElse();
				}
				catch (\Rini\Core\Auth\NotLoggedInException $e) {
					return 'not logged in';
				}

				return 'ok';
			}
			else if ($_POST['action'] === 'logOutEverywhere') {
				try {
					$auth->logOutEverywhere();
				}
				catch (\Rini\Core\Auth\NotLoggedInException $e) {
					return 'not logged in';
				}

				return 'ok';
			}
			else if ($_POST['action'] === 'destroySession') {
				$auth->destroySession();

				return 'ok';
			}
			else if ($_POST['action'] === 'admin.createUser') {
				try {
					if (!isset($_POST['require_unique_username'])) {
						$_POST['require_unique_username'] = '0';
					}

					if ($_POST['require_unique_username'] == 0) {
						return $auth->admin()->createUser($_POST['email'], $_POST['password'], $_POST['username']);
					}
					else {
						return $auth->admin()->createUserWithUniqueUsername($_POST['email'], $_POST['password'], $_POST['username']);
					}
				}
				catch (\Rini\Core\Auth\InvalidEmailException $e) {
					return 'invalid email address';
				}
				catch (\Rini\Core\Auth\InvalidPasswordException $e) {
					return 'invalid password';
				}
				catch (\Rini\Core\Auth\UserAlreadyExistsException $e) {
					return 'email address already exists';
				}
				catch (\Rini\Core\Auth\DuplicateUsernameException $e) {
					return 'username already exists';
				}
			}
			else if ($_POST['action'] === 'admin.deleteUser') {
				if (isset($_POST['id'])) {
					try {
						$auth->admin()->deleteUserById($_POST['id']);
					}
					catch (\Rini\Core\Auth\UnknownIdException $e) {
						return 'unknown ID';
					}
				}
				elseif (isset($_POST['email'])) {
					try {
						$auth->admin()->deleteUserByEmail($_POST['email']);
					}
					catch (\Rini\Core\Auth\InvalidEmailException $e) {
						return 'unknown email address';
					}
				}
				elseif (isset($_POST['username'])) {
					try {
						$auth->admin()->deleteUserByUsername($_POST['username']);
					}
					catch (\Rini\Core\Auth\UnknownUsernameException $e) {
						return 'unknown username';
					}
					catch (\Rini\Core\Auth\AmbiguousUsernameException $e) {
						return 'ambiguous username';
					}
				}
				else {
					return 'either ID, email address or username required';
				}

				return 'ok';
			}
			else if ($_POST['action'] === 'admin.addRole') {
				if (isset($_POST['role'])) {
					if (isset($_POST['id'])) {
						try {
							$auth->admin()->addRoleForUserById($_POST['id'], $_POST['role']);
						}
						catch (\Rini\Core\Auth\UnknownIdException $e) {
							return 'unknown ID';
						}
					}
					elseif (isset($_POST['email'])) {
						try {
							$auth->admin()->addRoleForUserByEmail($_POST['email'], $_POST['role']);
						}
						catch (\Rini\Core\Auth\InvalidEmailException $e) {
							return 'unknown email address';
						}
					}
					elseif (isset($_POST['username'])) {
						try {
							$auth->admin()->addRoleForUserByUsername($_POST['username'], $_POST['role']);
						}
						catch (\Rini\Core\Auth\UnknownUsernameException $e) {
							return 'unknown username';
						}
						catch (\Rini\Core\Auth\AmbiguousUsernameException $e) {
							return 'ambiguous username';
						}
					}
					else {
						return 'either ID, email address or username required';
					}
				}
				else {
					return 'role required';
				}

				return 'ok';
			}
			else if ($_POST['action'] === 'admin.removeRole') {
				if (isset($_POST['role'])) {
					if (isset($_POST['id'])) {
						try {
							$auth->admin()->removeRoleForUserById($_POST['id'], $_POST['role']);
						}
						catch (\Rini\Core\Auth\UnknownIdException $e) {
							return 'unknown ID';
						}
					}
					elseif (isset($_POST['email'])) {
						try {
							$auth->admin()->removeRoleForUserByEmail($_POST['email'], $_POST['role']);
						}
						catch (\Rini\Core\Auth\InvalidEmailException $e) {
							return 'unknown email address';
						}
					}
					elseif (isset($_POST['username'])) {
						try {
							$auth->admin()->removeRoleForUserByUsername($_POST['username'], $_POST['role']);
						}
						catch (\Rini\Core\Auth\UnknownUsernameException $e) {
							return 'unknown username';
						}
						catch (\Rini\Core\Auth\AmbiguousUsernameException $e) {
							return 'ambiguous username';
						}
					}
					else {
						return 'either ID, email address or username required';
					}
				}
				else {
					return 'role required';
				}

				return 'ok';
			}
			else if ($_POST['action'] === 'admin.hasRole') {
				if (isset($_POST['id'])) {
					if (isset($_POST['role'])) {
						try {
							return $auth->admin()->doesUserHaveRole($_POST['id'], $_POST['role']) ? 'yes' : 'no';
						}
						catch (\Rini\Core\Auth\UnknownIdException $e) {
							return 'unknown ID';
						}
					}
					else {
						return 'role required';
					}
				}
				else {
					return 'ID required';
				}
			}
			else if ($_POST['action'] === 'admin.getRoles') {
				if (isset($_POST['id'])) {
					try {
						return $auth->admin()->getRolesForUserById($_POST['id']);
					}
					catch (\Rini\Core\Auth\UnknownIdException $e) {
						return 'unknown ID';
					}
				}
				else {
					return 'ID required';
				}
			}
			else if ($_POST['action'] === 'admin.logInAsUserById') {
				if (isset($_POST['id'])) {
					try {
						$auth->admin()->logInAsUserById($_POST['id']);

						return 'ok';
					}
					catch (\Rini\Core\Auth\UnknownIdException $e) {
						return 'unknown ID';
					}
					catch (\Rini\Core\Auth\EmailNotVerifiedException $e) {
						return 'email address not verified';
					}
				}
				else {
					return 'ID required';
				}
			}
			else if ($_POST['action'] === 'admin.logInAsUserByEmail') {
				if (isset($_POST['email'])) {
					try {
						$auth->admin()->logInAsUserByEmail($_POST['email']);

						return 'ok';
					}
					catch (\Rini\Core\Auth\InvalidEmailException $e) {
						return 'unknown email address';
					}
					catch (\Rini\Core\Auth\EmailNotVerifiedException $e) {
						return 'email address not verified';
					}
				}
				else {
					return 'Email address required';
				}
			}
			else if ($_POST['action'] === 'admin.logInAsUserByUsername') {
				if (isset($_POST['username'])) {
					try {
						$auth->admin()->logInAsUserByUsername($_POST['username']);

						return 'ok';
					}
					catch (\Rini\Core\Auth\UnknownUsernameException $e) {
						return 'unknown username';
					}
					catch (\Rini\Core\Auth\AmbiguousUsernameException $e) {
						return 'ambiguous username';
					}
					catch (\Rini\Core\Auth\EmailNotVerifiedException $e) {
						return 'email address not verified';
					}
				}
				else {
					return 'Username required';
				}
			}
			else if ($_POST['action'] === 'admin.changePasswordForUser') {
				if (isset($_POST['newPassword'])) {
					if (isset($_POST['id'])) {
						try {
							$auth->admin()->changePasswordForUserById($_POST['id'], $_POST['newPassword']);
						}
						catch (\Rini\Core\Auth\UnknownIdException $e) {
							return 'unknown ID';
						}
						catch (\Rini\Core\Auth\InvalidPasswordException $e) {
							return 'invalid password';
						}
					}
					elseif (isset($_POST['username'])) {
						try {
							$auth->admin()->changePasswordForUserByUsername($_POST['username'], $_POST['newPassword']);
						}
						catch (\Rini\Core\Auth\UnknownUsernameException $e) {
							return 'unknown username';
						}
						catch (\Rini\Core\Auth\AmbiguousUsernameException $e) {
							return 'ambiguous username';
						}
						catch (\Rini\Core\Auth\InvalidPasswordException $e) {
							return 'invalid password';
						}
					}
					else {
						return 'either ID or username required';
					}
				}
				else {
					return 'new password required';
				}

				return 'ok';
			}
			else {
				throw new Exception('Unexpected action: ' . $_POST['action']);
			}
		}
	}

	return null;
}

function showDebugData(\Rini\Core\Auth\Auth $auth, $result) {
	echo '<pre>';

	echo 'Last operation' . "\t\t\t\t";
	\var_dump($result);
	echo 'Session ID' . "\t\t\t\t";
	\var_dump(\session_id());
	echo "\n";

	echo '$auth->isLoggedIn()' . "\t\t\t";
	\var_dump($auth->isLoggedIn());
	echo '$auth->check()' . "\t\t\t\t";
	\var_dump($auth->check());
	echo "\n";

	echo '$auth->getUserId()' . "\t\t\t";
	\var_dump($auth->getUserId());
	echo '$auth->id()' . "\t\t\t\t";
	\var_dump($auth->id());
	echo "\n";

	echo '$auth->getEmail()' . "\t\t\t";
	\var_dump($auth->getEmail());
	echo '$auth->getUsername()' . "\t\t\t";
	\var_dump($auth->getUsername());

	echo '$auth->getStatus()' . "\t\t\t";
	echo \convertStatusToText($auth);
	echo ' / ';
	\var_dump($auth->getStatus());

	echo "\n";

	echo 'Roles (super moderator)' . "\t\t\t";
	\var_dump($auth->hasRole(\Rini\Core\Auth\Role::SUPER_MODERATOR));

	echo 'Roles (developer *or* manager)' . "\t\t";
	\var_dump($auth->hasAnyRole(\Rini\Core\Auth\Role::DEVELOPER, \Rini\Core\Auth\Role::MANAGER));

	echo 'Roles (developer *and* manager)' . "\t\t";
	\var_dump($auth->hasAllRoles(\Rini\Core\Auth\Role::DEVELOPER, \Rini\Core\Auth\Role::MANAGER));

	echo 'Roles' . "\t\t\t\t\t";
	echo \json_encode($auth->getRoles()) . "\n";

	echo "\n";

	echo '$auth->isRemembered()' . "\t\t\t";
	\var_dump($auth->isRemembered());
	echo '$auth->getIpAddress()' . "\t\t\t";
	\var_dump($auth->getIpAddress());
	echo "\n";

	echo 'Session name' . "\t\t\t\t";
	\var_dump(\session_name());
	echo 'Auth::createRememberCookieName()' . "\t";
	\var_dump(\Rini\Core\Auth\Auth::createRememberCookieName());
	echo "\n";

	echo 'Auth::createCookieName(\'session\')' . "\t";
	\var_dump(\Rini\Core\Auth\Auth::createCookieName('session'));
	echo 'Auth::createRandomString()' . "\t\t";
	\var_dump(\Rini\Core\Auth\Auth::createRandomString());
	echo 'Auth::createUuid()' . "\t\t\t";
	\var_dump(\Rini\Core\Auth\Auth::createUuid());

	echo '</pre>';
}

function convertStatusToText(\Rini\Core\Auth\Auth $auth) {
	if ($auth->isLoggedIn() === true) {
		if ($auth->getStatus() === \Rini\Core\Auth\Status::NORMAL && $auth->isNormal()) {
			return 'normal';
		}
		elseif ($auth->getStatus() === \Rini\Core\Auth\Status::ARCHIVED && $auth->isArchived()) {
			return 'archived';
		}
		elseif ($auth->getStatus() === \Rini\Core\Auth\Status::BANNED && $auth->isBanned()) {
			return 'banned';
		}
		elseif ($auth->getStatus() === \Rini\Core\Auth\Status::LOCKED && $auth->isLocked()) {
			return 'locked';
		}
		elseif ($auth->getStatus() === \Rini\Core\Auth\Status::PENDING_REVIEW && $auth->isPendingReview()) {
			return 'pending review';
		}
		elseif ($auth->getStatus() === \Rini\Core\Auth\Status::SUSPENDED && $auth->isSuspended()) {
			return 'suspended';
		}
	}
	elseif ($auth->isLoggedIn() === false) {
		if ($auth->getStatus() === null) {
			return 'none';
		}
	}

	throw new Exception('Invalid status `' . $auth->getStatus() . '`');
}

function showGeneralForm() {
	echo '<form action="" method="get" accept-charset="utf-8">';
	echo '<button type="submit">Refresh</button>';
	echo '</form>';
}

function showAuthenticatedUserForm(\Rini\Core\Auth\Auth $auth) {
	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="reconfirmPassword" />';
	echo '<input type="text" name="password" placeholder="Password" /> ';
	echo '<button type="submit">Reconfirm password</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="changePassword" />';
	echo '<input type="text" name="oldPassword" placeholder="Old password" /> ';
	echo '<input type="text" name="newPassword" placeholder="New password" /> ';
	echo '<button type="submit">Change password</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="changePasswordWithoutOldPassword" />';
	echo '<input type="text" name="newPassword" placeholder="New password" /> ';
	echo '<button type="submit">Change password without old password</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="changeEmail" />';
	echo '<input type="text" name="newEmail" placeholder="New email address" /> ';
	echo '<button type="submit">Change email address</button>';
	echo '</form>';

	\showConfirmEmailForm();

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="setPasswordResetEnabled" />';
	echo '<select name="enabled" size="1">';
	echo '<option value="0"' . ($auth->isPasswordResetEnabled() ? '' : ' selected="selected"') . '>Disabled</option>';
	echo '<option value="1"' . ($auth->isPasswordResetEnabled() ? ' selected="selected"' : '') . '>Enabled</option>';
	echo '</select> ';
	echo '<button type="submit">Control password resets</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="logOut" />';
	echo '<button type="submit">Log out</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="logOutEverywhereElse" />';
	echo '<button type="submit">Log out everywhere else</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="logOutEverywhere" />';
	echo '<button type="submit">Log out everywhere</button>';
	echo '</form>';

	\showDestroySessionForm();
}

function showGuestUserForm() {
	echo '<h1>Public</h1>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="login" />';
	echo '<input type="text" name="email" placeholder="Email address" /> ';
	echo '<input type="text" name="password" placeholder="Password" /> ';
	echo '<select name="remember" size="1">';
	echo '<option value="0">Remember (keep logged in)? — No</option>';
	echo '<option value="1">Remember (keep logged in)? — Yes</option>';
	echo '</select> ';
	echo '<button type="submit">Log in with email address</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="login" />';
	echo '<input type="text" name="username" placeholder="Username" /> ';
	echo '<input type="text" name="password" placeholder="Password" /> ';
	echo '<select name="remember" size="1">';
	echo '<option value="0">Remember (keep logged in)? — No</option>';
	echo '<option value="1">Remember (keep logged in)? — Yes</option>';
	echo '</select> ';
	echo '<button type="submit">Log in with username</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="register" />';
	echo '<input type="text" name="email" placeholder="Email address" /> ';
	echo '<input type="text" name="password" placeholder="Password" /> ';
	echo '<input type="text" name="username" placeholder="Username (optional)" /> ';
	echo '<select name="require_verification" size="1">';
	echo '<option value="0">Require email confirmation? — No</option>';
	echo '<option value="1">Require email confirmation? — Yes</option>';
	echo '</select> ';
	echo '<select name="require_unique_username" size="1">';
	echo '<option value="0">Username — Any</option>';
	echo '<option value="1">Username — Unique</option>';
	echo '</select> ';
	echo '<button type="submit">Register</button>';
	echo '</form>';

	\showConfirmEmailForm();

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="forgotPassword" />';
	echo '<input type="text" name="email" placeholder="Email address" /> ';
	echo '<button type="submit">Forgot password</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="resetPassword" />';
	echo '<input type="text" name="selector" placeholder="Selector" /> ';
	echo '<input type="text" name="token" placeholder="Token" /> ';
	echo '<input type="text" name="password" placeholder="New password" /> ';
	echo '<select name="login" size="1">';
	echo '<option value="0">Sign in automatically? — No</option>';
	echo '<option value="1">Sign in automatically? — Yes</option>';
	echo '<option value="2">Sign in automatically? — Yes (and remember)</option>';
	echo '</select> ';
	echo '<button type="submit">Reset password</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="canResetPassword" />';
	echo '<input type="text" name="selector" placeholder="Selector" /> ';
	echo '<input type="text" name="token" placeholder="Token" /> ';
	echo '<button type="submit">Can reset password?</button>';
	echo '</form>';

	\showDestroySessionForm();

	echo '<h1>Administration</h1>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.createUser" />';
	echo '<input type="text" name="email" placeholder="Email address" /> ';
	echo '<input type="text" name="password" placeholder="Password" /> ';
	echo '<input type="text" name="username" placeholder="Username (optional)" /> ';
	echo '<select name="require_unique_username" size="1">';
	echo '<option value="0">Username — Any</option>';
	echo '<option value="1">Username — Unique</option>';
	echo '</select> ';
	echo '<button type="submit">Create user</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.deleteUser" />';
	echo '<input type="text" name="id" placeholder="ID" /> ';
	echo '<button type="submit">Delete user by ID</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.deleteUser" />';
	echo '<input type="text" name="email" placeholder="Email address" /> ';
	echo '<button type="submit">Delete user by email</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.deleteUser" />';
	echo '<input type="text" name="username" placeholder="Username" /> ';
	echo '<button type="submit">Delete user by username</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.addRole" />';
	echo '<input type="text" name="id" placeholder="ID" /> ';
	echo '<select name="role">' . \createRolesOptions() . '</select>';
	echo '<button type="submit">Add role for user by ID</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.addRole" />';
	echo '<input type="text" name="email" placeholder="Email address" /> ';
	echo '<select name="role">' . \createRolesOptions() . '</select>';
	echo '<button type="submit">Add role for user by email</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.addRole" />';
	echo '<input type="text" name="username" placeholder="Username" /> ';
	echo '<select name="role">' . \createRolesOptions() . '</select>';
	echo '<button type="submit">Add role for user by username</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.removeRole" />';
	echo '<input type="text" name="id" placeholder="ID" /> ';
	echo '<select name="role">' . \createRolesOptions() . '</select>';
	echo '<button type="submit">Remove role for user by ID</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.removeRole" />';
	echo '<input type="text" name="email" placeholder="Email address" /> ';
	echo '<select name="role">' . \createRolesOptions() . '</select>';
	echo '<button type="submit">Remove role for user by email</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.removeRole" />';
	echo '<input type="text" name="username" placeholder="Username" /> ';
	echo '<select name="role">' . \createRolesOptions() . '</select>';
	echo '<button type="submit">Remove role for user by username</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.hasRole" />';
	echo '<input type="text" name="id" placeholder="ID" /> ';
	echo '<select name="role">' . \createRolesOptions() . '</select>';
	echo '<button type="submit">Does user have role?</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.getRoles" />';
	echo '<input type="text" name="id" placeholder="ID" /> ';
	echo '<button type="submit">Get user\'s roles</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.logInAsUserById" />';
	echo '<input type="text" name="id" placeholder="ID" /> ';
	echo '<button type="submit">Log in as user by ID</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.logInAsUserByEmail" />';
	echo '<input type="text" name="email" placeholder="Email address" /> ';
	echo '<button type="submit">Log in as user by email address</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.logInAsUserByUsername" />';
	echo '<input type="text" name="username" placeholder="Username" /> ';
	echo '<button type="submit">Log in as user by username</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.changePasswordForUser" />';
	echo '<input type="text" name="id" placeholder="ID" /> ';
	echo '<input type="text" name="newPassword" placeholder="New password" /> ';
	echo '<button type="submit">Change password for user by ID</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="admin.changePasswordForUser" />';
	echo '<input type="text" name="username" placeholder="Username" /> ';
	echo '<input type="text" name="newPassword" placeholder="New password" /> ';
	echo '<button type="submit">Change password for user by username</button>';
	echo '</form>';
}

function showConfirmEmailForm() {
	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="confirmEmail" />';
	echo '<input type="text" name="selector" placeholder="Selector" /> ';
	echo '<input type="text" name="token" placeholder="Token" /> ';
	echo '<select name="login" size="1">';
	echo '<option value="0">Sign in automatically? — No</option>';
	echo '<option value="1">Sign in automatically? — Yes</option>';
	echo '<option value="2">Sign in automatically? — Yes (and remember)</option>';
	echo '</select> ';
	echo '<button type="submit">Confirm email</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="resendConfirmationForEmail" />';
	echo '<input type="text" name="email" placeholder="Email address" /> ';
	echo '<button type="submit">Re-send confirmation</button>';
	echo '</form>';

	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="resendConfirmationForUserId" />';
	echo '<input type="text" name="userId" placeholder="User ID" /> ';
	echo '<button type="submit">Re-send confirmation</button>';
	echo '</form>';
}

function showDestroySessionForm() {
	echo '<form action="" method="post" accept-charset="utf-8">';
	echo '<input type="hidden" name="action" value="destroySession" />';
	echo '<button type="submit">Destroy session</button>';
	echo '</form>';
}

function createRolesOptions() {
	$out = '';

	foreach (\Rini\Core\Auth\Role::getMap() as $roleValue => $roleName) {
		$out .= '<option value="' . $roleValue . '">' . $roleName . '</option>';
	}

	return $out;
}
