<?php

/**
* class ReformalAPI
* 
* @author zhzhussupovkz@gmail.com
* 
* The MIT License (MIT)
*
* Copyright (c) 2013 Zhussupov Zhassulan zhzhussupovkz@gmail.com
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy of
* this software and associated documentation files (the "Software"), to deal in
* the Software without restriction, including without limitation the rights to
* use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
* the Software, and to permit persons to whom the Software is furnished to do so,
* subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
* FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
* COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
* IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
* CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

class ReformalAPI {

	//main api url
	private $api_url = 'http://reformal.ru/service.php';

	//token
	private $token;

	//API settings
	private $api_settings = array();

	//constructor
	public function __construct() {
		$this->api_settings['api_id'] = 'YOUR API ID';
		$this->api_settings['project_id'] = 'YOUR PROJECT ID';
	}

	/*
	send request
	*/
	private function send_req($xml) {
		$ch = curl_init();
		$options = array(
			CURLOPT_URL => $this->api_url,
			CURLOPT_POSTFIELDS => $xml,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => 0,
			);
		curl_setopt_array($ch, $options);
		$response = curl_exec($ch);
		curl_close($ch);
		$resp_xml = simplexml_load_string($response);
		$json = json_encode($resp_xml);
		$result = json_decode($json, TRUE);
		return $result;
	}

	/*
	Авторизация пользователя
	*/
	private function getToken($email, $pass) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<signIn>
		<email>'.$email.'</email>
		<password>'.$pass.'</password>
		</signIn>';
		$result = $this->send_req($xml);
		$token = $result['session']['token'];
		$this->token = $token;
	}

	/*
	Информация пользователя
	*/
	public function userInfo($login, $mode = 'personal') {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<userInfo>
			<token>'.$this->token.'</token>
			<login>'.$login.'</login>
			<mode>'.$mode.'</mode>
		</userInfo>';
		$result = $this->send_req($xml);
		switch ($mode) {
			case 'personal':
				return $result['user'];
				break;
			case 'comments':
				return $result['userComments']['comments'];
				break;
			case 'ideas';
				return $result['userIdeas']['ideas'];
				break;
			case 'projects';
				return $result['userProjects']['projects'];
				break;
			default:
				return $result['user'];
				break;
		}
	}

	/*
	Кол-во комментариев, идей и проектов пользователя
	*/
	public function userCount($login, $mode) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<userInfo>
			<token>'.$this->token.'</token>
			<login>'.$login.'</login>
			<mode>'.$mode.'</mode>
		</userInfo>';
		$result = $this->send_req($xml);
		switch ($mode) {
			case 'comments':
				return $result['userComments']['count_comments'];
				break;
			case 'ideas';
				return $result['userIdeas']['count_ideas'];
				break;
			case 'projects';
				return $result['userProjects']['count_projects'];
				break;
			default:
				return $result['userProjects']['count_projects'];
				break;
		}
	}

	/*
	Получение информации об идее
	*/
	public function ideaInfo($id, $comments = true) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<ideaInfo>
		<id>'.$id.'</id>
		<token>'.$this->token.'</token>';
		if ($comments == true)
			$xml.= '<with_coms>1</with_coms>';

		$xml.='<api_id>'.$this->api_settings['api_id'].'</api_id>
		<project_id>'.$this->api_settings['projects_id'].'</project_id>
		</ideaInfo> ';

		$result = $this->send_req($xml);
		if ($comments == true)
			return $result['idea'];
		else
			return $result['idea']['info'];
	}

	/*
	Получение комментариев к идее
	*/
	public function ideaComments($id) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<ideaComments>
			<id>'.$id.'</id>
			<token>'.$this->token.'</token>
			<api_id>'.$this->api_settings['api_id'].'</api_id>
			<project_id>'.$this->api_settings['projects_id'].'</project_id>
		</ideaComments> ';

		$result = $this->send_req($xml);
		return $result['comments'];
	}

	/*
	Добавление идеи
	*/
	public function addIdea($project_id, $domain, $title, $story, $params = array()) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<addIdea>
			<token>'.$this->token.'</token>
			<project_id>'.$project_id.'</project_id>
			<api_id>'.$this->api_settings['api_id'].'</api_id>
			<domain>'.$slider.'</domain>
			<title>'.$title.'</title>
		<story>'.$story.'</story> ';

		if (array_key_exists('noreg_name', $params))
			$xml.= '<noreg_name>'.$params['noreg_name'].'</noreg_name>';
		if (array_key_exists('noreg_email', $params))
			$xml.= '<noreg_email>'.$params['noreg_email'].'</noreg_email>';
		if (array_key_exists('subscribe', $params))
			$xml.= '<subscribe>'.$params['subscribe'].'</subscribe>';

		$xml.= '</addIdea>';

		$result = $this->send_req($xml);
		return $result['operationStatus'];
	}

	/*
	Добавление комментария к идее
	*/
	public function addIdeaComment($idea_id, $story, $params = array()) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<addIdeaComment>
			<token>'.$this->token.'</token>
			<idea_id>'.$idea_id.'</idea_id>
			<story>'.$story.'</story> ';

		if (array_key_exists('noreg_name', $params))
			$xml.= '<noreg_name>'.$params['noreg_name'].'</noreg_name>';
		if (array_key_exists('noreg_email', $params))
			$xml.= '<noreg_email>'.$params['noreg_email'].'</noreg_email>';
		if (array_key_exists('parent', $params))
			$xml.= '<parent>'.$params['parent'].'</parent>';

		$xml.= '</addIdeaComment>';

		$result = $this->send_req($xml);
		return $result['operationStatus'];
	}

	/*
	Удаление идеи
	*/
	public function deleteIdea($idea_id) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<addIdea>
			<token>'.$this->token.'</token>
			<idea_id>'.$idea_id.'</idea_id>
			<api_id>'.$this->api_settings['api_id'].'</api_id>
			<project_id>'.$this->api_settings['projects_id'].'</project_id>
		</deleteIdea>';

		$result = $this->send_req($xml);
		return $result['operationStatus'];
	}

	/*
	Отдать голос
	*/
	public function addVote($idea_id) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<vote>
			<token>'.$this->token.'</token>
			<idea_id>'.$idea_id.'</idea_id>
			<api_id>'.$this->api_settings['api_id'].'</api_id>
			<project_id>'.$this->api_settings['projects_id'].'</project_id>
		</vote>';

		$result = $this->send_req($xml);
		return $result['operationStatus'];
	}

	/*
	Забрать голос
	*/
	public function cancelVote($idea_id) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<cancelVote>
			<token>'.$this->token.'</token>
			<idea_id>'.$idea_id.'</idea_id>
			<api_id>'.$this->api_settings['api_id'].'</api_id>
			<project_id>'.$this->api_settings['projects_id'].'</project_id>
		</cancelVote>';

		$result = $this->send_req($xml);
		return $result['operationStatus'];
	}

	/*
	Поиск идеи в проекте
	*/
	public function searchIdea($idea_id, $query) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
			<suggestionSearch>
				<token>'.$this->token.'</token>
				<api_id>'.$this->api_settings['api_id'].'</api_id>
				<project_id>'.$this->api_settings['projects_id'].'</project_id>
				<query>'.$query.'</query>
			</suggestionSearch>';

		$result = $this->send_req($xml);
		return $result;
	}

	/*
	Регистрация нового пользователя
	*/
	public function regUser($login, $email, $password) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
			<regUser>
				<login>'.$login.'</login>
				<email>'.$email.'</email>
				<password>'.$password.'</password>
			</regUser>';
		$result = $this->send_req($xml);
		if (isset($result['token']))
			return $result['token'];
		else
			return $result;
	}
}

?>