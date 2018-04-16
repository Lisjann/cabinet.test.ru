<?php

namespace Top10\CabinetBundle\Classes;

class JsonImportException extends \Exception
{
	const ERROR_JSON_NOT_FOUND = 101;
    const ERROR_JSON_BLOCKED_SAP5 = 102;
    const ERROR_JSON_BLOCKED_SAP11 = 103;
    const ERROR_JSON_NOT_VALID = 104;
    const ERROR_JSON_NOT_ARRAY = 105;

    public function __construct($code, $message = '')
    {
        if(is_array($message)) {
            $message = implode('; ', $message);
        }
        $message = $this->getMessageByCode($code)."; ".$message;
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return $this->message;
    }

    public function getMessageByCode($code)
    {
        $msg = 'unknown import error';

        switch($code) {
            case self::ERROR_JSON_NOT_FOUND:
                $msg = 'Ошибка файл JSON не найден';
                break;
			case self::ERROR_JSON_BLOCKED_SAP5:
				$msg = '3.JSON Блокирован sap5';
				break;
			case self::ERROR_JSON_BLOCKED_SAP11:
				$msg = '3.JSON Блокирован sap11';
				break;
			case self::ERROR_JSON_NOT_VALID:
				$msg = 'JSON файл не валидный';
				break;
			case self::ERROR_JSON_NOT_ARRAY:
				$msg = 'JSON файл не является массивом';
				break;
           
        }

        return $msg;
    }
}