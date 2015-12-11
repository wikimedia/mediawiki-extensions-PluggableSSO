<?php

/**
 *  Copyright (c) 2015 Mark A. Hershberger
 *
 *  This file is part of the PluggableSSO MediaWiki extension
 *
 *  PlugggableSSO is free software: you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  PluggableSSO is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *  General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with PluggableSSO.  If not, see
 *  <http://www.gnu.org/licenses/>.
 */


namespace PluggableSSO;

class Hooks {

	static public function onAuthPluginSetup( $wgAuth ) {
		if ( !class_exists( 'PluggableAuth' ) ) {
			die( '<b>Error:</b> This extension requires the PluggableAuth ' .
				'extension to be loaded first' );
		}

		if ( array_key_exists( 'PluggableAuth_Class', $GLOBALS ) ) {
			die( '<b>Error:</b> A value for $PluggableAuth_Class has ' .
				'already been set.' );
		}

		$GLOBALS['PluggableAuth_Class'] = 'PluggableSSO';
		$GLOBALS['PluggableAuth_Timeout'] = 0;
		$GLOBALS['PluggableAuth_AutoLogin'] = true;

		if ( !isset( $_SERVER['REMOTE_USER'] ) ) {
			throw new MWException( __CLASS__ . " requires that the webserver" .
				"sets REMOTE_USER." );
		}
		$username = $_SERVER['REMOTE_USER'];
		$domain = null;
		if ( isset( $GLOBALS['wgAuthRemoteuserDomain'] ) ) {
			$domain = $GLOBALS['wgAuthRemoteuserDomain'];

			list( $name, $userDomain ) = explode( '@', $username );
			if ( $userDomain !== $domain ) {
				throw new \MWException( "User doesn't have right domain" );
			}
			$username = $name;
		}

		$id = \User::idFromName( "$username" );

		$session_variable = wfWikiID() . "_userid";
		if (
			isset( $_SESSION[$session_variable] ) &&
			$id != $_SESSION[$session_variable]
		) {
			wfDebugLog( __CLASS__, "Username didn't match session" );
			throw new \MWException( "Username doesn't match session" );
		}
		$_SESSION[$session_variable] = $id;
		\Hooks::run( 'PluggableSSOAuth', array( &$GLOBALS['wgAuth'] ) );
	}
}