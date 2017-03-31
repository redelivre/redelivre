<?php

/*
 Plugin Name: Delibera User Fields Extender
 Plugin URI: http://www.ethymos.com.br
 Description: O Plugin "Delibera User Fields Extender" extende os campos de dados dos usuários para suportar campos 
 Version: 0.0.1
 Author: Laboratório de Cultura Digital
 Author URI: http://laboratoriodeculturadigital.redelivre.org.br

 THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

// PHP 5.3 and later:
namespace Delibera;

class UserFieldsExtender
{
	function __construct()
	{
		add_action( 'wp_login', array($this, 'wp_login'), 10, 2);
		add_action( 'admin_init', array($this, 'admin_init'));
	}
	
	function admin_init()
	{
		add_filter( 'manage_users_columns', array($this, 'user_table' ));
		add_filter( 'manage_users_custom_column', array($this, 'user_table_row'), 10, 3 );
		add_action( 'pre_get_users', array($this, 'pre_get_users'));
	}
	
	function user_table( $column )
	{
		$column['cpf'] = 'Cpf';
		return $column;
	}
	
	
	function user_table_row( $val, $column_name, $user_id )
	{
		switch ($column_name)
		{
			case 'cpf' :
				$cpf = get_the_author_meta( 'cpf', $user_id);
				if(!empty($cpf))
				{
					return $cpf;
				}
				return '';
				break;
			default:
		}
		return $val;
	}
	
	/**
	 *
	 * @param WP_User_Query $wp_user_query
	 * @return WP_User_Query
	 */
	function pre_get_users($wp_user_query)
	{
		if (array_key_exists('s', $_REQUEST))
		{
			$s = str_replace('-', '', str_replace('.', '', $_REQUEST['s']));
			if(strlen($s) == 11 && is_numeric($s))
			{
				$wp_user_query->set('meta_key', 'cpf');
				$wp_user_query->set('meta_value', $s);
				$wp_user_query->set('meta_compare', 'LIKE');
				$wp_user_query->set('search', false);
			}
		}
	
		return $wp_user_query;
	}
	
	/**
	 * update provider infos to user metas
	 * @param string $user_login
	 * @param \WP_User $user
	 */
	function wp_login($user_login, $user)
	{
		$user_id = $user->ID;
		
		$wpopauthUserinfo = get_the_author_meta( '_wpopauth-userinfo', $user_id );
		if(
				is_array($wpopauthUserinfo) &&
				array_key_exists('user', $wpopauthUserinfo) &&
				is_array($wpopauthUserinfo['user']) &&
				array_key_exists('cpf', $wpopauthUserinfo['user']))
		{
			update_user_meta($user_id, 'cpf', $wpopauthUserinfo['user']['cpf']) ;
		}
	}
	
}

$UserFieldsExtender = new \Delibera\UserFieldsExtender();
