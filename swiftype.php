<?php

// json_last_error polyfill
if (!function_exists('json_last_error')) {
	function json_last_error() {
		global $_polyfill__json_last_error;
		if isset($_polyfill__json_last_error) {
			return $_polyfill__json_last_error;
		} else if (defined('JSON_ERROR_NONE')) {
			return JSON_ERROR_NONE;
		} else {
			return 0;
		}
	}
}

// json_encode polyfill using Services_JSON
if( !function_exists('json_encode') ) {
	require_once 'JSON.php';
    function json_encode($data) {
    	global $_polyfill__json_last_error;
        $json = new Services_JSON();
        $encoded = $json->encode($data);
        if (Services_JSON::isError($decoded)) {
        	$_polyfill__json_last_error = 4;
        }
        return $encoded;
    }
}
 
// json_decode polyfill using Services_JSON
if( !function_exists('json_decode') ) {
	require_once 'JSON.php';
    function json_decode($data) {
    	global $_polyfill__json_last_error;
        $json = new Services_JSON();
        $decoded = $json->decode($data);
        if (Services_JSON::isError($decoded)) {
        	$_polyfill__json_last_error = 4;
        }
        return $decoded;
    }
}

class SwiftypeClient {
	var $username;
	var $password;
	var $api_key;
	var $host;
	var $api_base_path;

	function SwiftypeClient($username = null, $password = null, $api_key = null, $host = 'http://api.swiftype.com', $api_base_path = '/api/v1/') {
		$this->username = $username;
		$this->password = $password;
		$this->api_key = $api_key;
		$this->host = $host;
		$this->api_base_path = $api_base_path;

		if(!function_exists('curl_init')){
			throw new Exception('Swiftype requires the CURL PHP extension.');
		}

		if(!function_exists('json_decode')){
  			throw new Exception('Swiftype requires the JSON PHP extension.');
		}
	}

	function engines() {
		return $this->get($this->engines_path());
	}

	function engine($engine_id) {
		return $this->get($this->engine_path($engine_id));
	}

	function create_engine($engine_id) {
		$engine = array(
			'engine' => array(
				'name' => $engine_id
			)
		);

		return $this->post($this->engines_path(), array(), $engine);
	}

	function destroy_engine($engine_id) {
		return $this->delete($this->engine_path($engine_id));
	}

	function document_types($engine_id) {
		return $this->get($this->document_types_path($engine_id));
	}

	function document_type($engine_id, $document_type_id) {
		return $this->get($this->document_type_path($engine_id, $document_type_id));
	}

	function create_document_type($engine_id, $document_type_id) {
		$document_type = array(
			'document_type' => array(
				'name' => $document_type_id
			)
		);
		return $this->post($this->document_types_path($engine_id), array(), $document_type);
	}

	function destroy_document_type($engine_id, $document_type_id) {
		return $this->delete($this->document_type_path($engine_id, $document_type_id));
	}

	function documents($engine_id, $document_type_id) {
		return $this->get($this->documents_path($engine_id, $document_type_id));
	}

	function document($engine_id, $document_type_id, $document_id) {
		return $this->get($this->document_path($engine_id, $document_type_id, $document_id));
	}

	function create_document($engine_id, $document_type_id, $document = array()) {
		return $this->post($this->documents_path($engine_id, $document_type_id), array(), array('document' => $document));
	}

	function create_or_update_document($engine_id, $document_type_id, $document = array()) {
		return $this->post($this->documents_path($engine_id, $document_type_id).'/create_or_update', array(), array('document' => $document));
	}

	function update_document($engine_id, $document_type_id, $document_id, $fields = array()) {
		return $this->put($this->document_path($engine_id, $document_type_id, $document_id).'/update_fields', array(), array('fields' => $fields));
	}

	function update_documents($engine_id, $document_type_id, $documents = array()) {
		return $this->put($this->documents_path($engine_id, $document_type_id).'/bulk_update', array(), array('documents' => $documents));
	}

	function create_or_update_documents($engine_id, $document_type_id, $documents = array()) {
		return $this->post($this->documents_path($engine_id, $document_type_id).'/bulk_create_or_update', array(), array('documents' => $documents));
	}

	function destroy_document($engine_id, $document_type_id, $document_id) {
		return $this->delete($this->document_path($engine_id, $document_type_id, $document_id));
	}

	function destroy_documents($engine_id, $document_type_id, $document_ids = array()) {
		return $this->post($this->documents_path($engine_id, $document_type_id).'/bulk_destroy', array(), array('documents' => $document_ids));
	}

	function search($engine_id, $document_type_id=null, $query, $options = array()) {
		$query_string = array('q' => $query);
		$full_query = array_merge($query_string, $options);
		return $this->post($this->search_path($engine_id, $document_type_id), array(), $full_query);
	}

	function suggest($engine_id, $document_type_id=null, $query, $options = array()) {
		$query_string = array('q' => $query);
		$full_query = array_merge($query_string, $options);
		return $this->post($this->suggest_path($engine_id, $document_type_id), array(), $full_query);
	}

	function search_path($engine_id, $document_type_id = null) {
		if ($document_type_id === null) {
			return 'engines/'.$engine_id.'/search';
		} else {
			return 'engines/'.$engine_id.'/document_types/'.$document_type_id.'/search';
		}
	}

	function suggest_path($engine_id, $document_type_id = null) {
		if ($document_type_id === null) {
			return 'engines/'.$engine_id.'/suggest';
		} else {
			return 'engines/'.$engine_id.'/document_types/'.$document_type_id.'/suggest';
		}
	}

	function engines_path() {
		return 'engines';
	}

	function engine_path($engine_id) {
		return 'engines/'.$engine_id;
	}

	function document_type_path($engine_id, $document_type_id) {
		return $this->engine_path($engine_id).'/document_types/'.$document_type_id;
	}

	function document_types_path($engine_id) {
		return $this->engine_path($engine_id).'/document_types';
	}

	function document_path($engine_id, $document_type_id, $document_id) {
		return $this->document_type_path($engine_id, $document_type_id).'/documents/'.$document_id;
	}

	function documents_path($engine_id, $document_type_id) {
		return $this->document_type_path($engine_id, $document_type_id).'/documents';
	}

	function get($path, $params = array(), $data = array()) {
		return $this->request('GET', $path, $params, $data);
	}

	function post($path, $params = array(), $data = array()) {
		return $this->request('POST', $path, $params, $data);
	}

	function delete($path, $params = array(), $data = array()) {
		return $this->request('DELETE', $path, $params, $data);
	}

	function put($path, $params = array(), $data = array()) {
		return $this->request('PUT', $path, $params, $data);
	}

	function request($method, $path, $params = array(), $data = array()) {
		//Final URL
		$full_path = $this->host.$this->api_base_path.$path.'.json';

		//Use the api key if we have it.
		if ($this->api_key != null) {
			$params['auth_token'] = $this->api_key;
		}

		//Build the query string
		$query = false;
		if (!empty($params)) {
			$query = '';
			foreach ($params as $key => $value) {
				$query = $key . '=' . urlencode($value) . '&';
			}
			$query = rtrim($query, '&');
		}


		if ($query) {
			$full_path .= '?' . $query;
		}

		$request = curl_init($full_path);

		//Use basic http auth if we have no api key
		//Throw an exception if we have nothing with which to authenticate ourselves
		if ($this->api_key === null && $this->username != null && $this->password != null) {
			curl_setopt($request, CURLOPT_USERPWD, $this->username.':'.$this->password);
		} elseif ($this->api_key === null && $this->username === null && $this->password === null) {
			throw new Exception('Authorization required.');
		}

		//Return the output instead of printing it
		curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($request, CURLOPT_FAILONERROR, true);

		$body = ($data) ? json_encode($data) : '';

		curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-type: application/json'));

		if ($method === 'POST') {
			curl_setopt($request, CURLOPT_POST, true);
			curl_setopt($request, CURLOPT_POSTFIELDS, $body);
		} elseif ($method === 'DELETE') {
			curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'DELETE');
		} elseif ($method === 'PUT') {
			curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($request, CURLOPT_POSTFIELDS, $body);
		}

		$response = curl_exec($request);
		$error = curl_error($request);

		if ($error) {
	  		throw new Exception("Sending message failed. Error: ". $error);
		}

		$http_status = intval(curl_getinfo($request,CURLINFO_HTTP_CODE));
		curl_close($request);

		//Any 2XX HTTP codes mean that the request worked
		if (intval(floor($http_status / 100)) === 2) {
			$final = json_decode($response);
			switch (json_last_error()) {
				case JSON_ERROR_DEPTH:
					$error = 'Maximum stack depth exceeded';
					break;
				case JSON_ERROR_CTRL_CHAR:
					$error = 'Unexpected control character found';
					break;
				case JSON_ERROR_SYNTAX:
					$error = 'Syntax error, malformed JSON';
					break;
				case JSON_ERROR_STATE_MISMATCH:
					$error = 'Underflow or the modes mismatch';
					break;
				case JSON_ERROR_UTF8:
					$error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
					break;
				case JSON_ERROR_NONE:
				default:
					$error = false;
					break;
			}

			if ($error === false) {
				//Request and response are OK
				if ($final) {
					return array(
						'status' => $http_status,
						'body' => $final
					);
				} else {
					return array('status' => $http_status);
				}
			} else {
				throw new Exception('The JSON response could not be parsed: '.$error. '\n'.$response);
			}
		} elseif ($http_status === 401) {
			throw new Exception('Authorization required.');
		} else {
	  		throw new Exception("Couldn't send message, got response code: ". $http_status. " response: ".$response);
		}
	}
}

?>
