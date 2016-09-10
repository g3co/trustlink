<?php
/**
 *
 */

class trustlinkApi {
	/** @var SoapClient $client*/
	protected $client = NULL;
	/** @var array $errors */
	protected $errors = array();

	/**
	 * @param $config array массив конфигурации
	 * должен содержать следующие ключи
	 *	$config['uri'] string адрес soap сервера
	 *	$config['location'] string адрес скрипта для обратных сообщений(может быть любым, требуется для метода)
	 *	$config['login'] string логин для авторизации на сервере
	 *	$config['password'] string пароль
	 */
	public function __construct($config){
		$this->client = new SoapClient(NULL,$config);
	}

	/** Получить список тематик
	 * @return array массив объектов класса stdClass со свойствами
	 *	theme_id int id темы
	 *	name string рускоязычное название тематики
	 */
	public function getThemes(){
		return $this->client->__soapCall('get_themes',array());
	}

	/** Добавляет сайт на сервис
	 * @param $sSiteUrl string url сайта можно с http можно без
	 * @param $iThemeId integer id тематики, получать методом getThemes
	 * @return integer id добавленного сайта, если сайт не добавлен вернётся 0 и будет добавленна ошибка в список errors
	 */
	public function addSite($sSiteUrl,$iThemeId){
		$arrAddSiteConfig = array(
			'url'           => $sSiteUrl,
			'theme_id'	=> $iThemeId,
			'url_with_code'=> '/'
		);

		$result = $this->client->__soapCall('create_site',$arrAddSiteConfig);

		if($result->error)$this->addError($result->message);

		return (int)$result->result;
	}

	/** Получить информацию с сервиса о сайте
	 * @param $iSiteId integer id сайта
	 * @return integer возвращается код состояния, в случае возникновения ошибки будет возвращён 0 и добавлена запись в массив ошибок
	 */
	public function getSite($iSiteId){
		$iSiteId = (int)$iSiteId;

		if(!$iSiteId){
			$this->addError('Передан неверный идентификатор сайта Id: ' . $iSiteId);
			return false;
		}

		$result = $this->client->__soapCall('get_site',array('site_id'=>$iSiteId));

		if(!$result) $this->addError('По сайту с Id: ' . $iSiteId . ' не были полученны данные');

		return (int)$result;
	}

	/** Получить все сайты добавленные на сервис
	 * @return array массив id сайтов
	 */
	public function getAllSites(){
		$result = $this->client->__soapCall('get_sites',array());

		return $result;
	}

	/** Удалить сайт с сервиса
	 * @param $iSiteId integer id сайта на сервисе
	 * @return bool статус удаления сайта
	 */
	public function removeSite($iSiteId){
		$iSiteId = (int)$iSiteId;

		if(!$iSiteId) {
			$this->addError('Передан неверный идентификатор сайта Id: ' . $iSiteId);
			return false;
		}

		$result = $this->client->__soapCall('destroy_site_async',array('site_id'=>$iSiteId));

		if($result->error) {
			$this->addError($result->error_type);
			return false;
		}

		return true;
	}

	/** Добавить ошибку в лог
	 * @param $sError string текстовое описание ошибки
	 */
	protected function addError($sError){
		$this->errors[] = (string)$sError;
	}

	/** Получить список ошибок
	 * @param bool $onlyLast если true возвращает только последнюю ошибку
	 * @return array|string возвращает массив ошибок или последнюю ошибку в виде строки
	 */
	public function getError($onlyLast = false){
		$output = $this->errors;
		if($onlyLast) $output = end($this->errors);
		return $output;
	}

	/** Проверяет, возникали ли ошибки
	 * @return bool true если были ошибки, false если нет
	 */
	public function hasError(){
		if(count($this->errors)) return true;
		return false;
	}
	/** Сбрасывает список ошибок
	 */
	public function resetError(){
		$this->errors = array();
	}
}

