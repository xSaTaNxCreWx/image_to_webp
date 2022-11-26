<?php

/**
 * Создание WebP картинок с сохранением на диск
 */
class Image_to_webp {
	/**
	 * Тип используемой библиотеки
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Основной url сайта
	 *
	 * @var string
	 */
	private $base_url;

	/**
	 * Путь до папки с файлами
	 *
	 * @var string
	 */
	private $base_dir;

	/**
	 * Путь, куда будем сохранять WebP
	 *
	 * @var string
	 */
	private $webp_dir;

	/**
	 * Название папки для WebP
	 *
	 * @var string
	 */
	private $webp_dir_name = '/webp';

	/**
	 * Разрешенный расширения файлов
	 *
	 * @var string[]
	 */
	private $extensions = array( 'jpg', 'jpeg', 'png' );

	/**
	 * URL картинки
	 *
	 * @var array
	 */
	private $image_urls = null;

	public function __construct( $type = 'gd', $test = false ) {

		$this->type = $type;

		if ( $test ) {
			$test_info = array();

			if ( phpversion() >= '5.4.0' ) {
				$gd_info = gd_info();
				$test_info = array(
					'php_version'  => phpversion(),
					'gd_version'   => $gd_info['GD Version'],
					'webp_support' => ( key_exists( 'WebP Support', $gd_info ) && $gd_info['WebP Support'] === true )
				);

				if ( ! $test_info['webp_support'] ) {
					$info = 'Создание WebP невозможно. Проверьте настройки сервера. php >= 5.4.0, gd >= 2.1.0, webp_support = true';
				} else {
					$info = 'WebP должны генерироваться без проблем, если gd_version >= 2.2.5, если меньше, то WebP будут генерироваться, но фон у прозрачных png файлов будет черный. ';
				}
			} else {
				$info = 'Минимальная версия php 5.4.0';
			}
			$test_info['info'] = $info;

			$this->vd( $test_info );
		} else {
			$base_url       = ( ( ! empty( $_SERVER['HTTPS'] ) ) ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'];
			$this->base_url = $this->checking_slash( $base_url );

			$this->base_dir = $this->checking_slash( $_SERVER['DOCUMENT_ROOT'] );

			$this->webp_dir = $this->base_dir . $this->checking_slash( $this->webp_dir_name );
		}
	}

	/**
	 * Проверка чтоб в конце строки был '/'
	 *
	 * @param $string
	 *
	 * @return mixed|string
	 */
	private function checking_slash( $string ) {
		return ( substr( $string, - 1 ) === '/' ? $string : $string . '/' );
	}

	/**
	 * Помощь в дебаге
	 *
	 * @param mixed $code
	 *
	 * @return void
	 */
	public function vd( $code = '') {
		echo '<pre style="padding: 15px;border: 1px solid black; background-color: #e8e8e8;font-family: monospace;line-height: 1.4;">';
		if ( $code === '' ) {
			$code = $this;
		}
		var_dump( $code );
		echo '</pre>';
	}

	/**
	 * Добавляем картинку для обработки по URL
	 *
	 * @param $image_url
	 *
	 * @return void
	 */
	public function add_image( $image_url ) {
		if ( is_array( $image_url ) ) {
			$this->image_urls = ( is_null( $this->image_urls ) ) ? $image_url : array_merge( $this->image_urls, $image_url );
		}

		if ( is_string( $image_url ) ) {
			$this->image_urls = ( is_null( $this->image_urls ) ) ? array( $image_url ) : array_merge( $this->image_urls, array( $image_url ) );
		}
	}

	/**
	 * Устанавливаем разрешенные расширения файлов.
	 * По умолчанию: jpg, jpeg, png
	 *
	 * @param array $extension - массив с расширениями
	 *
	 * @return void
	 */
	public function set_extensions( $extension = array() ) {
		if ( ! empty( $extension ) ) {
			$this->extensions = $extension;
		}
	}

	/**
	 * Устанавливаем папку с файлами по умолчанию
	 *
	 * @param $path - путь до папки
	 *
	 * @return void
	 */
	public function set_base_dir( $path ) {
		$this->base_dir = $this->checking_slash( $path );
	}

	/**
	 * Устанавливаем папку для сохранения файлов WebP
	 *
	 * @param $path - путь до папки
	 *
	 * @return void
	 */
	public function set_webp_dir( $path ) {
		$this->webp_dir      = $this->checking_slash( $path );
		$get_name            = explode( '/', $path );
		$this->webp_dir_name = end( $get_name );
	}

	/**
	 * Устанавливаем основной url сайта.
	 * По умолчанию, url собирается из глобальной переменной $_SERVER.
	 *
	 * @param $url - url сайта
	 *
	 * @return void
	 */
	public function set_base_url( $url ) {
		$this->base_url = $url;
	}

	/**
	 * Формируем информацию WebP файла
	 *
	 * @param $image_name
	 *
	 * @return object
	 */
	private function webp_image_name( $image_name ) {
		return (object) array(
			'extension' => 'webp',
			'filename'  => $image_name,
			'basename'  => $image_name . '.webp',
			'dirname'   => ''
		);
	}

	/**
	 * Создаём папки по пути
	 *
	 * @param $path
	 *
	 * @return void
	 */
	private function create_dir( $path ) {
		if ( ! file_exists( $path ) ) {
			mkdir( $path, 0777, true );
		}
	}

	/**
	 * Создание WebP из файла
	 *
	 * @return array
	 * @throws ImagickException
	 */
	public function convert( $path = null ) {
		if ( $path !== null ) {
			$this->image_urls = array(
				$path
			);
		}

		if ( ! empty( $this->image_urls ) && strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false ) {
			// Перебираем входящие url картинок
			foreach ( $this->image_urls as $key => $image ) {
				// Получаем информацию о файле
				$image_info = pathinfo( $image );

				// Проверяем что файл имеет нужно расширение
				if ( key_exists( 'extension', $image_info ) && in_array( strtolower( $image_info['extension'] ), $this->extensions ) ) {
					// Получаем промежуточный путь от основной папки, до папки текущего файла
					$sub_folders = str_replace( array( $this->base_url, $image_info['basename'] ), '', $image );

					// Формируем объект с основной информацией для файла WebP
					$webp_image_info          = $this->webp_image_name( $image_info['filename'] );
					$webp_image_info->dirname = $this->webp_dir . $sub_folders;

					// Получаем основные пути для исходного файла и WebP файла
					$image_path      = $this->base_dir . $sub_folders . $image_info['basename'];
					$wepb_image_path = $webp_image_info->dirname . $webp_image_info->basename;

					// Проверяем что исходный файл существует
					if ( file_exists( $image_path ) ) {
						// Проверяем что WebP ещё не создан
						if ( ! file_exists( $wepb_image_path ) ) {
							// Создаём промежуточные папки
							$this->create_dir( $webp_image_info->dirname );
							// Создаём WebP
							$this->create_webp( $image_info['extension'], $image_path, $wepb_image_path );
						}

						// Перезаписываем входной массив, для ответа
						$this->image_urls[ $key ] = str_replace( $this->webp_dir, $this->base_url . ( $this->base_url === '' ? '/' : '' ) . $this->webp_dir_name, $wepb_image_path );
					}
				}
			}
		}

		// Возвращаем массив
		return ($path !== null) ? $this->image_urls[0] : $this->image_urls;
	}

	/**
	 * Создаём WebP
	 *
	 * @param $extension - расширение исходного файла
	 * @param $image_path - путь до исходного файла
	 * @param $path_to_webp - путь до WebP файла
	 *
	 * @return void
	 * @throws ImagickException
	 */
	private function create_webp( $extension, $image_path, $path_to_webp ) {
		switch ( $this->type ) {
			case 'ImageMagick':
			case 'im':
				$this->create_webp_im( $image_path, $path_to_webp );
				break;
			default:
				$this->create_webp_gd( $extension, $image_path, $path_to_webp );
		}
	}

	/**
	 * Создаём WebP библиотекой GD
	 *
	 * @param $extension - расширение исходного файла
	 * @param $image_path - путь до исходного файла
	 * @param $path_to_webp - путь до WebP файла
	 *
	 * @return void
	 */
	private function create_webp_gd( $extension, $image_path, $path_to_webp ) {
		if ( strtolower( $extension ) === 'png' ) {
			$im = imagecreatefrompng( $image_path );
			imagepalettetotruecolor( $im );
			imagealphablending( $im, false );
			imagesavealpha( $im, true );
		} else {
			$im = imagecreatefromjpeg( $image_path );
		}

		imagewebp( $im, $path_to_webp, 100 );
		imagedestroy( $im );

		if ( filesize( $path_to_webp ) % 2 == 1 ) {
			file_put_contents( $path_to_webp, "\0", FILE_APPEND );
		}
	}

	/**
	 * Создаём WebP библиотекой ImageMagick
	 *
	 * @param $image_path - путь до исходного файла
	 * @param $path_to_webp - путь до WebP файла
	 *
	 * @return void
	 * @throws ImagickException
	 */
	private function create_webp_im( $image_path, $path_to_webp ) {
		$im = new Imagick();
		$im->pingImage($image_path);
		$im->readImage($image_path);
		$im->setImageFormat('webp');
		$im->setOption('webp:method', '6');

		$im->writeImage($path_to_webp);
	}

}
