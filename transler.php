<?php
/*
Plugin Name: Transler
Plugin URI: https://wordpress.org/plugins/transler/
Description: Осуществляет транслитерацию кириллицы в название файлов и слагах записей.
Author: Nikolay Lukyanov
Author URI: https://vk.com/mx.lukyanov
Version: 1.0.6
License: GPLv2
*/
class Transler 
{
	// Активация плагина
	public function Activation() 
	{
		add_action('shutdown', array( 'Transler', 'Apply' ));
	}

	// Транслитерация строки
	public function Convert( $string )
	{
		// Декодируем строку и переводим нижний регистр
		$string = mb_strtolower(urldecode( $string ));
		
		// Удаляем все символы кроме разрешенных
		$string = preg_replace('/[^а-я0-9]-_/ui', '', $string);

		// Словарь
		$dictionary = array(
			'а' => 'a',  'б' => 'b',  'в' => 'v', 
			'г' => 'g',  'д' => 'd',  'е' => 'e', 
	        'ё' => 'e',  'ж' => 'zh', 'з' => 'z', 
			'и' => 'i',  'й' => 'i',  'к' => 'k', 
			'л' => 'l',  'м' => 'm',  'н' => 'n', 
			'о' => 'o',  'п' => 'p',  'р' => 'r', 
			'с' => 's',  'т' => 't',  'у' => 'u', 
			'ф' => 'f',  'х' => 'kh', 'ц' => 'ts',
			'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 
			'ъ' => 'ie', 'ы' => 'y',  'ь' => '', 
			'э' => 'e',  'ю' => 'iu', 'я' => 'ia',
		);

		return strtr($string, $dictionary);
	}

	/*
	* Транслитерация существующих записей
	*
	* Осуществляет каждый раз при активации плагина.
	*
	*/
	public function Apply()
	{
		$posts = get_posts( array ( 'numberposts' => -1 ) );

		foreach( $posts as $post )
		{
		    $slug = $this->convert( $post->post_title );

		    if( $post->post_name != $slug )
		    wp_update_post(
	            array (
	                'ID'        => $post->ID,
	                'post_name' => $slug
	            )
	        );
		}
	}
}

// Хук выполняется при активации плагина
register_activation_hook(__FILE__, array( 'Transler', 'Activation' ));

// Добавляем фильтр на генерацию слагов
add_filter('sanitize_title', array( 'Transler', 'Convert' ));
// Добавляем фильтр на генерацию имени файла
add_filter('sanitize_file_name', array( 'Transler', 'Convert' ));